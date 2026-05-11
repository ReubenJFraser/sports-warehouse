"""Stage 1 local hero-candidate preprocessing.

This prototype analyses a small, explicit set of product images and writes
JSON only. It does not write to MySQL, alter PHP logic, or replace Hero
Manager scoring. The output is advisory evidence for future review.

Usage:
    python tools-dev/image-analysis/score_hero_candidates_stage1.py
    python tools-dev/image-analysis/score_hero_candidates_stage1.py --input images/brands/stax/women/airlyte/active_zip_Jacket

Default output:
    tools-dev/image-analysis/out/hero_candidates_stage1.json

Dependencies:
    pip install opencv-python mediapipe numpy tqdm pillow

Principle:
    Automation suggests. Manual Hero Manager selections win.
"""

from __future__ import annotations

import argparse
import json
import math
from pathlib import Path
from typing import Any

import cv2
import mediapipe as mp
import numpy as np
from tqdm import tqdm


PROJECT_ROOT = Path(__file__).resolve().parents[2]
DEFAULT_OUTPUT = Path("tools-dev/image-analysis/out/hero_candidates_stage1.json")

DEFAULT_INPUTS = [
    "images/brands/stax/women/airlyte/active_zip_Jacket",
    "images/brands/stax/women/airlyte/full-length_tights",
    "images/brands/stax/women/second_left_seamless_campaign/bralette",
    "images/brands/asics/unisex/running_shoes/kayano_26.png",
    "images/brands/nike/other/600ml_waterbottle.png",
]

IMAGE_EXTS = {".png", ".jpg", ".jpeg", ".webp"}

PRODUCT_RULES = {
    "sports_bra": {
        "keywords": ["sports_bra", "bralette", "crop", "strappy_crop", "adira_crop", "flex_crop", "v_front-crop"],
        "roi_type": "upper_body_garment",
        "face_weight": 0.45,
        "expected_pose": True,
    },
    "upper_body": {
        "keywords": ["jacket", "hoodie", "long_sleeve", "longsleeve", "tank", "top", "tee"],
        "roi_type": "upper_body_model",
        "face_weight": 0.75,
        "expected_pose": True,
    },
    "lower_body": {
        "keywords": ["leggings", "tights", "shorts", "skort", "skirt", "biker"],
        "roi_type": "lower_body_garment",
        "face_weight": 0.1,
        "expected_pose": True,
    },
    "full_body": {
        "keywords": ["playsuit", "set", "tracksuit", "outfit"],
        "roi_type": "full_body_model",
        "face_weight": 0.45,
        "expected_pose": True,
    },
    "object": {
        "keywords": ["shoe", "shoes", "sneaker", "trainer", "bottle", "ball", "helmet", "bag", "backpack"],
        "roi_type": "object",
        "face_weight": 0.0,
        "expected_pose": False,
    },
}


SCORE_SCOPE = "diagnostic_within_category_not_global_rank"


CATEGORY_INTERPRETATIONS = {
    "upper_body": {
        "label": "upper_body_model",
        "face_required": True,
        "pose_required": "expected",
        "primary_visual_target": "upper_body_model",
    },
    "sports_bra": {
        "label": "sports_bra_partial_crop",
        "face_required": False,
        "pose_required": "helpful_not_required",
        "primary_visual_target": "upper_body_garment",
    },
    "lower_body": {
        "label": "lower_body_garment",
        "face_required": False,
        "pose_required": "expected_for_garment_roi",
        "primary_visual_target": "hips_to_knees_or_ankles",
    },
    "full_body": {
        "label": "full_body_model",
        "face_required": False,
        "pose_required": "expected",
        "primary_visual_target": "full_model_or_outfit",
    },
    "object": {
        "label": "object_product",
        "face_required": False,
        "pose_required": "not_required",
        "primary_visual_target": "alpha_mask_object",
    },
    "unknown": {
        "label": "unknown_product_type",
        "face_required": False,
        "pose_required": "optional_diagnostic",
        "primary_visual_target": "alpha_or_full_image_fallback",
    },
}


def clamp(value: float, lo: float, hi: float) -> float:
    return max(lo, min(hi, value))


def box_to_dict(x: float, y: float, w: float, h: float) -> dict[str, float]:
    return {
        "x": round(float(x), 2),
        "y": round(float(y), 2),
        "width": round(float(w), 2),
        "height": round(float(h), 2),
    }


def bbox_area(box: dict[str, float] | None) -> float:
    if not box:
        return 0.0
    return max(0.0, box["width"]) * max(0.0, box["height"])


def rel_path(path: Path) -> str:
    try:
        return path.resolve().relative_to(PROJECT_ROOT).as_posix()
    except ValueError:
        return path.as_posix()


def collect_image_paths(inputs: list[str], max_images_per_input: int) -> list[Path]:
    paths: list[Path] = []
    seen: set[Path] = set()

    for item in inputs:
        candidate = (PROJECT_ROOT / item).resolve()
        found: list[Path] = []

        if candidate.is_file() and candidate.suffix.lower() in IMAGE_EXTS:
            found = [candidate]
        elif candidate.is_dir():
            found = sorted(p for p in candidate.iterdir() if p.is_file() and p.suffix.lower() in IMAGE_EXTS)

        for path in found[:max_images_per_input]:
            if path not in seen:
                paths.append(path)
                seen.add(path)

    return paths


def read_image(path: Path) -> np.ndarray | None:
    data = np.fromfile(str(path), dtype=np.uint8)
    if data.size == 0:
        return None
    return cv2.imdecode(data, cv2.IMREAD_UNCHANGED)


def composite_rgba_on_bg(img: np.ndarray, bg: tuple[int, int, int] = (224, 224, 224)) -> np.ndarray:
    if img.ndim == 2:
        return cv2.cvtColor(img, cv2.COLOR_GRAY2BGR)
    if img.shape[2] == 3:
        return img
    b, g, r, a = cv2.split(img)
    alpha = a.astype(np.float32) / 255.0
    alpha3 = cv2.merge([alpha, alpha, alpha])
    bg_img = np.full(img.shape[:2] + (3,), bg, dtype=np.uint8)
    rgb = cv2.merge([b, g, r]).astype(np.float32)
    return (alpha3 * rgb + (1.0 - alpha3) * bg_img.astype(np.float32)).astype(np.uint8)


def alpha_geometry(img: np.ndarray, threshold: int = 16) -> tuple[dict[str, Any], list[str]]:
    warnings: list[str] = []
    h, w = img.shape[:2]

    if img.ndim < 3 or img.shape[2] < 4:
        warnings.append("missing_alpha_channel")
        full = box_to_dict(0, 0, w, h)
        return {
            "has_alpha": False,
            "alpha_bbox": full,
            "alpha_occupancy": None,
            "padding_pct": {"top": 0.0, "bottom": 0.0, "left": 0.0, "right": 0.0},
            "object_fill_ratio": 1.0,
            "crop_safety_estimate": "unknown",
        }, warnings

    alpha = img[:, :, 3]
    mask = alpha > threshold
    occupied = int(mask.sum())
    if occupied == 0:
        warnings.append("alpha_channel_empty")
        full = box_to_dict(0, 0, w, h)
        return {
            "has_alpha": True,
            "alpha_bbox": full,
            "alpha_occupancy": 0.0,
            "padding_pct": {"top": 0.0, "bottom": 0.0, "left": 0.0, "right": 0.0},
            "object_fill_ratio": 1.0,
            "crop_safety_estimate": "unknown",
        }, warnings

    ys, xs = np.where(mask)
    left, right = int(xs.min()), int(xs.max())
    top, bottom = int(ys.min()), int(ys.max())
    bw, bh = right - left + 1, bottom - top + 1
    padding = {
        "top": round(top / h * 100.0, 2),
        "bottom": round((h - bottom - 1) / h * 100.0, 2),
        "left": round(left / w * 100.0, 2),
        "right": round((w - right - 1) / w * 100.0, 2),
    }
    min_pad = min(padding.values())

    return {
        "has_alpha": True,
        "alpha_bbox": box_to_dict(left, top, bw, bh),
        "alpha_occupancy": round(occupied / float(w * h), 4),
        "padding_pct": padding,
        "object_fill_ratio": round((bw * bh) / float(w * h), 4),
        "crop_safety_estimate": "safe" if min_pad >= 1.5 else "edge_risk",
    }, warnings


def pad_canvas(img_bgr: np.ndarray, pad_pct: tuple[float, float, float, float] = (0.12, 0.08, 0.06, 0.06)) -> tuple[np.ndarray, tuple[int, int]]:
    h, w = img_bgr.shape[:2]
    pt = int(round(h * pad_pct[0]))
    pb = int(round(h * pad_pct[1]))
    pl = int(round(w * pad_pct[2]))
    pr = int(round(w * pad_pct[3]))
    canvas = np.full((h + pt + pb, w + pl + pr, 3), (224, 224, 224), dtype=np.uint8)
    canvas[pt : pt + h, pl : pl + w, :] = img_bgr
    return canvas, (pt, pl)


def detect_face(img_bgr: np.ndarray, fd_short: Any, fd_full: Any, min_size: int = 384) -> dict[str, Any]:
    h, w = img_bgr.shape[:2]
    padded, (pad_top, pad_left) = pad_canvas(img_bgr)
    ph, pw = padded.shape[:2]
    scale = min_size / min(ph, pw) if min(ph, pw) > 0 and min(ph, pw) < min_size else 1.0
    work = cv2.resize(padded, (int(pw * scale), int(ph * scale))) if scale != 1.0 else padded
    rgb = cv2.cvtColor(work, cv2.COLOR_BGR2RGB)

    result = fd_short.process(rgb)
    if not result.detections:
        result = fd_full.process(rgb)

    if not result.detections:
        return {
            "face_detected": False,
            "face_count": 0,
            "face_bbox": None,
            "face_visibility_score": 0.0,
            "headroom_pct": None,
            "focus_y_pct": None,
        }

    ih, iw = work.shape[:2]
    boxes = []
    for detection in result.detections:
        rel = detection.location_data.relative_bounding_box
        x = (float(rel.xmin) * iw) / scale - pad_left
        y = (float(rel.ymin) * ih) / scale - pad_top
        bw = (float(rel.width) * iw) / scale
        bh = (float(rel.height) * ih) / scale
        x = clamp(x, 0.0, float(w))
        y = clamp(y, 0.0, float(h))
        bw = clamp(bw, 0.0, float(w) - x)
        bh = clamp(bh, 0.0, float(h) - y)
        confidence = detection.score[0] if detection.score else 0.0
        boxes.append((x, y, bw, bh, confidence))

    x, y, bw, bh, confidence = min(boxes, key=lambda b: b[1])
    center_y = y + bh / 2.0
    headroom_pct = clamp(y / h * 100.0, 0.0, 100.0)
    focus_y_pct = clamp(center_y / h * 100.0 - 6.0, 8.0, 35.0)

    return {
        "face_detected": True,
        "face_count": len(boxes),
        "face_bbox": box_to_dict(x, y, bw, bh),
        "face_visibility_score": round(float(confidence) * 100.0, 2),
        "headroom_pct": round(headroom_pct, 2),
        "focus_y_pct": round(focus_y_pct, 1),
    }


POSE_POINTS = {
    "left_shoulder": 11,
    "right_shoulder": 12,
    "left_hip": 23,
    "right_hip": 24,
    "left_knee": 25,
    "right_knee": 26,
    "left_ankle": 27,
    "right_ankle": 28,
}


def detect_pose(img_bgr: np.ndarray, pose_detector: Any) -> tuple[dict[str, Any], list[str]]:
    warnings: list[str] = []
    h, w = img_bgr.shape[:2]
    rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
    result = pose_detector.process(rgb)

    empty = {
        "pose_detected": False,
        "pose_confidence": 0.0,
        "landmarks": {name: None for name in POSE_POINTS},
    }

    if not result.pose_landmarks:
        warnings.append("pose_not_detected")
        return empty, warnings

    landmarks: dict[str, dict[str, float] | None] = {}
    confidences: list[float] = []
    for name, idx in POSE_POINTS.items():
        lm = result.pose_landmarks.landmark[idx]
        visibility = float(getattr(lm, "visibility", 0.0))
        if visibility < 0.25:
            landmarks[name] = None
            continue
        landmarks[name] = {
            "x": round(lm.x * w, 2),
            "y": round(lm.y * h, 2),
            "visibility": round(visibility, 3),
        }
        confidences.append(visibility)

    if not confidences:
        warnings.append("pose_landmarks_low_confidence")
        return empty, warnings

    return {
        "pose_detected": True,
        "pose_confidence": round(sum(confidences) / len(confidences), 3),
        "landmarks": landmarks,
    }, warnings


def infer_product_type(image_path: str) -> dict[str, Any]:
    haystack = image_path.lower().replace("\\", "/")
    for product_type, rule in PRODUCT_RULES.items():
        if any(keyword in haystack for keyword in rule["keywords"]):
            return {"product_type": product_type, **rule}
    return {
        "product_type": "unknown",
        "roi_type": "object_or_unknown",
        "face_weight": 0.2,
        "expected_pose": False,
    }


def average_landmark(landmarks: dict[str, Any], names: list[str]) -> tuple[float, float] | None:
    pts = [landmarks.get(name) for name in names if landmarks.get(name)]
    if not pts:
        return None
    return (sum(p["x"] for p in pts) / len(pts), sum(p["y"] for p in pts) / len(pts))


def estimate_rois(image: dict[str, Any], alpha: dict[str, Any], face: dict[str, Any], pose: dict[str, Any], rule: dict[str, Any]) -> dict[str, Any]:
    w, h = image["width"], image["height"]
    object_roi = alpha["alpha_bbox"] or box_to_dict(0, 0, w, h)
    full_body_roi = object_roi
    landmarks = pose.get("landmarks", {})

    shoulders = average_landmark(landmarks, ["left_shoulder", "right_shoulder"])
    hips = average_landmark(landmarks, ["left_hip", "right_hip"])
    knees = average_landmark(landmarks, ["left_knee", "right_knee"])
    ankles = average_landmark(landmarks, ["left_ankle", "right_ankle"])

    upper_body_roi = None
    lower_body_roi = None

    if shoulders and hips:
        top = face["face_bbox"]["y"] if face.get("face_bbox") else max(0.0, shoulders[1] - h * 0.18)
        bottom = min(h, hips[1] + h * 0.16)
        upper_body_roi = box_to_dict(0, top, w, max(1.0, bottom - top))

    if hips:
        lower_bottom = ankles[1] if ankles else (knees[1] if knees else object_roi["y"] + object_roi["height"])
        top = max(0.0, hips[1] - h * 0.08)
        bottom = min(float(h), lower_bottom + h * 0.04)
        lower_body_roi = box_to_dict(0, top, w, max(1.0, bottom - top))

    if upper_body_roi is None and face.get("face_bbox"):
        face_box = face["face_bbox"]
        top = max(0.0, face_box["y"] - h * 0.04)
        bottom = min(float(h), face_box["y"] + face_box["height"] + h * 0.42)
        upper_body_roi = box_to_dict(0, top, w, max(1.0, bottom - top))

    inferred = rule["roi_type"]
    selected_source = {
        "upper_body_model": "pose_band" if upper_body_roi else ("face_anchor" if face.get("face_bbox") else "alpha_bbox"),
        "upper_body_garment": "pose_band" if upper_body_roi else ("face_anchor" if face.get("face_bbox") else "alpha_bbox"),
        "lower_body_garment": "pose_band" if lower_body_roi else "alpha_bbox",
        "full_body_model": "alpha_bbox",
        "object": "object_bbox",
        "object_or_unknown": "alpha_bbox",
    }.get(inferred, "alpha_bbox")

    selected = {
        "upper_body_model": upper_body_roi or object_roi,
        "upper_body_garment": upper_body_roi or object_roi,
        "lower_body_garment": lower_body_roi or object_roi,
        "full_body_model": full_body_roi,
        "object": object_roi,
        "object_or_unknown": object_roi,
    }.get(inferred, object_roi)

    return {
        "full_body_roi": full_body_roi,
        "upper_body_roi": upper_body_roi,
        "lower_body_roi": lower_body_roi,
        "object_roi": object_roi,
        "selected_roi": selected,
        "selected_roi_source": selected_source,
    }


def build_roi_diagnostics(rule: dict[str, Any], rois: dict[str, Any], alpha: dict[str, Any], pose: dict[str, Any], face: dict[str, Any]) -> dict[str, Any]:
    product_type = rule["product_type"]
    roi_source = rois.get("selected_roi_source") or "full_image_fallback"
    alpha_box = alpha.get("alpha_bbox") or {}
    image_like_full = (
        alpha_box.get("x", 0) == 0
        and alpha_box.get("y", 0) == 0
        and alpha.get("object_fill_ratio") == 1.0
    )

    roi_fallback_used = False
    roi_is_garment_specific = False
    roi_is_body_region_specific = False
    roi_confidence = "medium"
    roi_specificity = "full_image_fallback"

    if roi_source == "object_bbox":
        roi_confidence = "high" if alpha.get("has_alpha") else "medium"
        roi_is_garment_specific = False
        roi_is_body_region_specific = False
        roi_specificity = "object_bbox"
    elif roi_source == "pose_band":
        roi_confidence = "medium"
        roi_is_garment_specific = False
        roi_is_body_region_specific = True
        roi_specificity = "body_region_band"
    elif roi_source == "face_anchor":
        roi_confidence = "low"
        roi_fallback_used = True
        roi_is_garment_specific = False
        roi_is_body_region_specific = True
        roi_specificity = "face_anchored_band"
    elif roi_source == "alpha_bbox":
        roi_fallback_used = product_type in {"lower_body", "upper_body", "sports_bra", "unknown"}
        roi_confidence = "medium" if alpha.get("has_alpha") and not image_like_full else "low"
        roi_is_garment_specific = False
        roi_is_body_region_specific = False
        roi_specificity = "alpha_object_bbox"
    else:
        roi_source = "full_image_fallback"
        roi_fallback_used = True
        roi_confidence = "low"
        roi_is_garment_specific = False
        roi_is_body_region_specific = False
        roi_specificity = "full_image_fallback"

    if product_type == "lower_body" and not rois.get("lower_body_roi"):
        roi_fallback_used = True
        roi_confidence = "low"
        roi_is_garment_specific = False
        roi_is_body_region_specific = False
        roi_specificity = "alpha_object_bbox"

    if product_type == "sports_bra" and roi_source == "alpha_bbox" and alpha.get("has_alpha"):
        roi_confidence = "medium"

    return {
        "roi_source": roi_source,
        "roi_specificity": roi_specificity,
        "roi_fallback_used": roi_fallback_used,
        "roi_confidence": roi_confidence,
        "roi_is_garment_specific": roi_is_garment_specific,
        "roi_is_body_region_specific": roi_is_body_region_specific,
    }


def sharpness_score(img_bgr: np.ndarray) -> float:
    gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)
    variance = float(cv2.Laplacian(gray, cv2.CV_64F).var())
    return round(clamp((variance / 600.0) * 100.0, 0.0, 100.0), 2)


def score_record(image: dict[str, Any], alpha: dict[str, Any], face: dict[str, Any], pose: dict[str, Any], rois: dict[str, Any], rule: dict[str, Any], img_bgr: np.ndarray) -> tuple[dict[str, float], list[str], bool]:
    warnings: list[str] = []
    needs_manual_review = False
    w, h = image["width"], image["height"]
    selected = rois["selected_roi"]
    roi_area = bbox_area(selected)
    image_area = float(w * h)
    roi_fill_score = clamp((roi_area / image_area) * 125.0, 0.0, 100.0)

    alpha_safety = alpha.get("crop_safety_estimate")
    crop_safety_score = 88.0 if alpha_safety == "safe" else 45.0 if alpha_safety == "edge_risk" else 60.0
    if face.get("headroom_pct") is not None and rule["face_weight"] > 0.3:
        crop_safety_score = 90.0 if face["headroom_pct"] >= 6.0 else 40.0

    ratio = image["aspect_ratio"]
    orientation_fit_score = 92.0 if 0.68 <= ratio <= 0.86 else 78.0 if ratio < 1.05 else 55.0

    selected_center_x = selected["x"] + selected["width"] / 2.0
    selected_center_y = selected["y"] + selected["height"] / 2.0
    x_offset = abs(selected_center_x - w / 2.0) / max(1.0, w / 2.0)
    y_offset = abs(selected_center_y - h / 2.0) / max(1.0, h / 2.0)
    composition_score = clamp(100.0 - (x_offset * 55.0 + y_offset * 35.0), 0.0, 100.0)

    image_quality_score = sharpness_score(img_bgr)
    face_score = 0.0
    if rule["face_weight"] > 0:
        face_score = face["face_visibility_score"] if face["face_detected"] else 20.0
        face_score *= rule["face_weight"]

    irrelevant_region_penalty = 0.0
    if rule["product_type"] == "lower_body" and face["face_detected"]:
        irrelevant_region_penalty = 8.0

    missing_expected_signal_penalty = 0.0
    if rule["expected_pose"] and not pose["pose_detected"] and rule["product_type"] != "sports_bra":
        missing_expected_signal_penalty = 12.0
        warnings.append("expected_pose_missing")
        needs_manual_review = True

    if rule["product_type"] == "unknown":
        warnings.append("unknown_product_type")
        needs_manual_review = True

    if rule["product_type"] == "lower_body" and rois["lower_body_roi"] is None:
        warnings.append("lower_body_roi_uncertain")
        needs_manual_review = True

    final = (
        roi_fill_score * 0.24
        + crop_safety_score * 0.18
        + orientation_fit_score * 0.16
        + composition_score * 0.14
        + image_quality_score * 0.14
        + face_score * 0.14
        - irrelevant_region_penalty
        - missing_expected_signal_penalty
    )

    return {
        "roi_fill_score": round(roi_fill_score, 2),
        "crop_safety_score": round(crop_safety_score, 2),
        "orientation_fit_score": round(orientation_fit_score, 2),
        "composition_score": round(composition_score, 2),
        "image_quality_score": round(image_quality_score, 2),
        "face_score_if_relevant": round(face_score, 2),
        "irrelevant_region_penalty": round(irrelevant_region_penalty, 2),
        "missing_expected_signal_penalty": round(missing_expected_signal_penalty, 2),
        "final_advisory_score": round(clamp(final, 0.0, 100.0), 2),
    }, warnings, needs_manual_review


def build_category_flags_and_warnings(rule: dict[str, Any], face: dict[str, Any], pose: dict[str, Any], rois: dict[str, Any]) -> tuple[dict[str, bool], list[str], bool]:
    product_type = rule["product_type"]
    category_warnings: list[str] = []
    extra_manual_review = False

    flags = {
        "lower_body_roi_fallback_to_alpha_bbox": False,
        "sports_bra_partial_body_crop": False,
        "face_missing_but_not_required": False,
        "pose_missing_but_not_required": False,
        "pose_missing_needs_review": False,
    }

    if product_type in {"sports_bra", "lower_body", "full_body", "object", "unknown"} and not face["face_detected"]:
        flags["face_missing_but_not_required"] = True
        category_warnings.append("face_missing_but_not_required")

    if product_type == "sports_bra":
        flags["sports_bra_partial_body_crop"] = True
        category_warnings.append("sports_bra_partial_body_crop")
        if not pose["pose_detected"]:
            flags["pose_missing_but_not_required"] = True
            category_warnings.append("pose_missing_but_crop_valid")

    if product_type == "lower_body" and rois.get("lower_body_roi") is None:
        flags["lower_body_roi_fallback_to_alpha_bbox"] = True
        flags["pose_missing_needs_review"] = True
        category_warnings.extend([
            "lower_body_pose_missing_roi_not_garment_specific",
            "lower_body_roi_fallback_to_alpha_bbox",
        ])
        extra_manual_review = True

    if product_type == "object":
        flags["pose_missing_but_not_required"] = True
        category_warnings.append("object_pose_not_required")

    if product_type == "unknown" and not pose["pose_detected"]:
        flags["pose_missing_but_not_required"] = True
        category_warnings.append("pose_missing_but_not_required")

    return flags, sorted(set(category_warnings)), extra_manual_review


def orientation_for_ratio(ratio: float) -> str:
    if ratio > 1.05:
        return "landscape"
    if ratio < 0.95:
        return "portrait"
    return "square"


def analyse_image(path: Path, fd_short: Any, fd_full: Any, pose_detector: Any) -> dict[str, Any]:
    warnings: list[str] = []
    img = read_image(path)
    image_path = rel_path(path)

    if img is None:
        return {
            "item_id": None,
            "image_path": image_path,
            "product_type": "unknown",
            "inferred_roi_type": None,
            "image": None,
            "alpha": None,
            "face": None,
            "pose": None,
            "rois": None,
            "scores": None,
            "warnings": ["decode_failed"],
            "needs_manual_review": True,
        }

    img_bgr = composite_rgba_on_bg(img)
    h, w = img_bgr.shape[:2]
    ratio = round(w / h, 4) if h else 0.0
    image = {
        "width": w,
        "height": h,
        "aspect_ratio": ratio,
        "orientation": orientation_for_ratio(ratio),
    }

    alpha, alpha_warnings = alpha_geometry(img)
    warnings.extend(alpha_warnings)

    rule = infer_product_type(image_path)
    face = detect_face(img_bgr, fd_short, fd_full)
    if rule["expected_pose"] or rule["product_type"] == "unknown":
        pose, pose_warnings = detect_pose(img_bgr, pose_detector)
        warnings.extend(pose_warnings)
    else:
        pose = {
            "pose_detected": False,
            "pose_confidence": 0.0,
            "landmarks": {name: None for name in POSE_POINTS},
            "pose_requirement": "not_required_for_product_type",
        }

    rois = estimate_rois(image, alpha, face, pose, rule)
    scores, score_warnings, manual = score_record(image, alpha, face, pose, rois, rule, img_bgr)
    warnings.extend(score_warnings)
    roi_diagnostics = build_roi_diagnostics(rule, rois, alpha, pose, face)
    category_flags, category_warnings, category_manual = build_category_flags_and_warnings(rule, face, pose, rois)
    manual = manual or category_manual
    category_interpretation = CATEGORY_INTERPRETATIONS.get(
        rule["product_type"],
        CATEGORY_INTERPRETATIONS["unknown"],
    )

    return {
        "item_id": None,
        "image_path": image_path,
        "product_type": rule["product_type"],
        "inferred_roi_type": rule["roi_type"],
        "score_scope": SCORE_SCOPE,
        "category_interpretation": category_interpretation,
        "image": image,
        "alpha": alpha,
        "face": face,
        "pose": pose,
        "rois": rois,
        "roi_diagnostics": roi_diagnostics,
        "scores": scores,
        "warnings": sorted(set(warnings)),
        "category_specific_warnings": category_warnings,
        **category_flags,
        "needs_manual_review": manual,
    }


def main() -> int:
    parser = argparse.ArgumentParser(description="JSON-only Stage 1 hero candidate preprocessing.")
    parser.add_argument("--input", action="append", dest="inputs", help="Project-relative image folder or image path. May be repeated.")
    parser.add_argument("--output", default=str(DEFAULT_OUTPUT), help="Project-relative JSON output path.")
    parser.add_argument("--max-images-per-input", type=int, default=4, help="Limit images per input folder.")
    args = parser.parse_args()

    inputs = args.inputs or DEFAULT_INPUTS
    paths = collect_image_paths(inputs, args.max_images_per_input)
    output_path = (PROJECT_ROOT / args.output).resolve()
    output_path.parent.mkdir(parents=True, exist_ok=True)

    mp_fd = mp.solutions.face_detection
    fd_short = mp_fd.FaceDetection(model_selection=0, min_detection_confidence=0.3)
    fd_full = mp_fd.FaceDetection(model_selection=1, min_detection_confidence=0.3)
    pose_detector = mp.solutions.pose.Pose(static_image_mode=True, model_complexity=1, min_detection_confidence=0.35)

    records = [analyse_image(path, fd_short, fd_full, pose_detector) for path in tqdm(paths, desc="Analysing hero candidates")]
    payload = {
        "schema": "active_layers.hero_candidates_stage1.v1b",
        "advisory_only": True,
        "manual_override_policy": "Automation suggests. Manual Hero Manager selections win.",
        "score_scope": SCORE_SCOPE,
        "project_root": "project-relative paths only",
        "inputs": inputs,
        "image_count": len(records),
        "records": records,
    }

    output_path.write_text(json.dumps(payload, indent=2), encoding="utf-8")
    print(f"[stage1] analysed {len(records)} image(s)")
    print(f"[stage1] wrote {rel_path(output_path)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
