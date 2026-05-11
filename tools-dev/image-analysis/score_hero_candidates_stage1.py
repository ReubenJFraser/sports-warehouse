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
import re
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

import cv2
import mediapipe as mp
import numpy as np
from tqdm import tqdm


PROJECT_ROOT = Path(__file__).resolve().parents[2]
DEFAULT_OUTPUT = Path("tools-dev/image-analysis/out/hero_candidates_stage1.json")

DEFAULT_INPUTS = [
    "images/brands/stax/women/airlyte/active_zip_Jacket/02.png",
    "images/brands/stax/women/airlyte/wrap-longsleeve/01.png",
    "images/brands/stax/women/airlyte/ruched_tank/01.png",
    "images/brands/adidas/kids/marvel-spider_man/t-shirt/01.png",
    "images/brands/adidas/men/poly_linear_full_zip_hoodie_and_pants/01.png",
    "images/brands/nike/women/training/full-zip_top Tracksuit/01.png",
    "images/brands/stax/women/second_left_seamless_campaign/bralette/01.png",
    "images/brands/stax/women/second_left_seamless_campaign/bralette/03.png",
    "images/brands/stax/women/nandex/strappy_crop/01.png",
    "images/brands/stax/women/nandex/v_front-crop/03.png",
    "images/brands/stax/women/nandex/adira_crop/01.png",
    "images/brands/aybl/adapt_seamless/sports_bra/01.png",
    "images/brands/asos/4504-curve/sports_bra/high_support/zip-front-adjustable-straps/01.png",
    "images/brands/underarmour/women/wordmark_strappy_sports_bra/01.png",
    "images/brands/stax/women/airlyte/full-length_tights/01.png",
    "images/brands/stax/women/airlyte/full-length_tights/02.png",
    "images/brands/nike/women/zenvy/leggings-high_waisted-full_length/01.png",
    "images/brands/nike/women/zenvy/leggings-high-waisted-flared/01.png",
    "images/brands/adidas/women/3-stripes/flared_leggings/01.png",
    "images/brands/nike/women/training/pro_mesh-3inch_shorts/01.png",
    "images/brands/stax/women/second_left_seamless_campaign/biker_shorts/01.png",
    "images/brands/stax/women/nandex/venus_skirt/02.png",
    "images/brands/designer/kate_galliano/bodysuit/bubblegum/01.png",
    "images/brands/stax/women/airlyte/backless_playsuit/01.png",
    "images/brands/adidas/kids/marvel-spider_man/tracksuit/01.png",
    "images/brands/adidas/men/campus_sneakers/01.png",
    "images/brands/asics/unisex/running_shoes/kayano_26.png",
    "images/brands/reebok/unisex/training_shoes/nano_x3/pure_white.png",
    "images/brands/nike/other/600ml_waterbottle.png",
    "images/brands/adidas/other/UEFA_Euro16-Top_Glider_Ball.png",
    "images/brands/other/protec-skate_helmet.png",
    "images/brands/adidas/kids/marvel-spider_man/backpack/01.png",
    "images/brands/other/sting-armaplus-boxing_gloves-T3.png",
]

IMAGE_EXTS = {".png", ".jpg", ".jpeg", ".webp"}

PRODUCT_RULES = {
    "sports_bra": {
        "keywords": [
            "sports bra",
            "bralette",
            "strappy crop",
            "adira crop",
            "flex crop",
            "v front crop",
            "bandeau",
            "racerback",
            "underwire",
        ],
        "roi_type": "upper_body_garment",
        "face_weight": 0.45,
        "expected_pose": True,
    },
    "object": {
        "keywords": ["shoe", "shoes", "sneaker", "sneakers", "trainer", "trainers", "boots", "bottle", "water bottle", "waterbottle", "ball", "helmet", "bag", "gym bag", "backpack", "gloves", "boxing gloves"],
        "roi_type": "object",
        "face_weight": 0.0,
        "expected_pose": False,
    },
    "full_body": {
        "keywords": ["playsuit", "bodysuit", "one piece", "tracksuit", "matching set"],
        "roi_type": "full_body_model",
        "face_weight": 0.45,
        "expected_pose": True,
    },
    "upper_body": {
        "keywords": ["jacket", "hoodie", "long sleeve", "longsleeve", "tank", "tank top", "top", "tee", "t shirt", "crop top"],
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
}

COMPACT_TOKEN_SYNONYMS = {
    "longsleeve": "long sleeve",
    "waterbottle": "water bottle",
    "sportsbra": "sports bra",
    "croptop": "crop top",
    "tshirt": "t shirt",
    "tee": "t shirt",
    "fullzip": "full zip",
    "highwaisted": "high waisted",
    "onepiece": "one piece",
    "gymbag": "gym bag",
}


DIAGNOSTIC_VOCABULARY = {
    "core_categories": [
        "sports bra", "bralette", "crop top", "tank top", "t shirt", "long sleeve", "longsleeve", "jacket", "hoodie",
        "tracksuit", "track pants", "leggings", "shorts", "skirt", "bodysuit", "one piece",
        "playsuit", "shoes", "sneakers", "trainers", "soccer boots", "backpack", "gym bag",
        "helmet", "ball", "water bottle", "waterbottle", "boxing gloves",
    ],
    "silhouette_shape": [
        "flared", "cross over", "longline", "cropped", "full length", "mini", "backless",
        "one shoulder", "asymmetrical", "strapless", "halter", "scoop neck", "square neck",
        "v neck", "bandeau", "racerback", "straight back", "cross over back",
        "multi cross over back straps", "centre spine", "cutout", "ruched", "foldover",
        "high waisted", "strappy crop", "v front crop", "adira crop", "flex crop",
    ],
    "garment_construction": [
        "seamless", "scrunch", "underwire", "elastic underbust band", "rib waistband",
        "foldover ribbed", "waistband", "pocket", "mesh", "brushed fleece",
        "elastic waistband ankle cuff",
    ],
    "sports_bra_support": [
        "low support", "light support", "medium support", "high support", "underwire",
        "one shoulder", "halter", "racerback", "cross over back", "longline", "bandeau",
    ],
    "surface_material_pattern": ["leopard print", "stonewash", "rib", "lyte", "poly", "towelling"],
    "object_controls": [
        "accessories", "backpack", "helmet", "ball", "water bottle", "waterbottle", "boxing gloves",
        "shoes", "sneakers", "boots", "trainers",
    ],
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


def normalize_label(value: str) -> str:
    text = value.lower()
    text = re.sub(r"[_\-\s:;\\/]+", " ", text)
    text = re.sub(r"[^a-z0-9]+", " ", text)
    text = re.sub(r"\s+", " ", text).strip()
    tokens = text.split()
    expanded: list[str] = []
    for token in tokens:
        expanded.append(token)
        synonym = COMPACT_TOKEN_SYNONYMS.get(token)
        if synonym:
            expanded.extend(synonym.split())
    return re.sub(r"\s+", " ", " ".join(expanded)).strip()


def normalized_tokens(value: str) -> list[str]:
    return normalize_label(value).split()


def has_normalized_phrase(haystack: str, phrase: str) -> bool:
    normalized_phrase = normalize_label(phrase)
    if not normalized_phrase:
        return False
    return f" {normalized_phrase} " in f" {haystack} "


def matched_diagnostic_vocabulary(image_path: str) -> dict[str, list[str]]:
    normalized = normalize_label(image_path)
    matches: dict[str, list[str]] = {}
    for group, terms in DIAGNOSTIC_VOCABULARY.items():
        group_matches = [term for term in terms if has_normalized_phrase(normalized, term)]
        if group_matches:
            matches[group] = group_matches
    return matches


def product_type_matches(image_path: str) -> dict[str, list[str]]:
    haystack = normalize_label(image_path)
    matches: dict[str, list[str]] = {}
    for product_type, rule in PRODUCT_RULES.items():
        terms = [keyword for keyword in rule["keywords"] if has_normalized_phrase(haystack, keyword)]
        if terms:
            matches[product_type] = terms
    return matches


def infer_product_type_with_diagnostics(image_path: str) -> tuple[dict[str, Any], dict[str, Any]]:
    matches = product_type_matches(image_path)
    selected_type = None
    selected_terms: list[str] = []

    for product_type, rule in PRODUCT_RULES.items():
        if product_type in matches:
            selected_type = product_type
            selected_terms = matches[product_type]
            selected_rule = {"product_type": product_type, **rule}
            break
    else:
        selected_rule = {
            "product_type": "unknown",
            "roi_type": "object_or_unknown",
            "face_weight": 0.2,
            "expected_pose": False,
        }

    competing = {
        product_type: terms
        for product_type, terms in matches.items()
        if product_type != selected_rule["product_type"]
    }
    competing_terms = [
        {"product_type": product_type, "terms": terms}
        for product_type, terms in competing.items()
    ]

    if selected_rule["product_type"] == "unknown":
        confidence = "low"
        reason = "No product-type keyword matched after normalized token comparison."
    elif not competing:
        confidence = "high"
        reason = f"Classified as {selected_rule['product_type']} from clear normalized term match: {', '.join(selected_terms)}."
    elif selected_rule["product_type"] == "sports_bra" and set(competing).issubset({"upper_body"}):
        confidence = "medium"
        reason = "Sports bra/crop terms overlap with upper-body language; sports_bra rule wins by priority."
    elif selected_rule["product_type"] == "object":
        confidence = "medium"
        reason = "Object/product term wins by priority, but competing product-type language is present."
    else:
        confidence = "low" if len(competing) > 1 else "medium"
        reason = "Selected category has competing product-type signals in the normalized path."

    diagnostics = {
        "normalized_tokens": normalized_tokens(image_path),
        "matched_terms": [term for terms in matches.values() for term in terms],
        "matched_product_type_terms": selected_terms,
        "competing_product_type_terms": competing_terms,
        "classification_confidence": confidence,
        "classification_reason": reason,
    }

    return selected_rule, diagnostics


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
    rule, _diagnostics = infer_product_type_with_diagnostics(image_path)
    return rule


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

    rule, classification_diagnostics = infer_product_type_with_diagnostics(image_path)
    diagnostic_terms = matched_diagnostic_vocabulary(image_path)
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
        "classification_diagnostics": classification_diagnostics,
        "diagnostic_vocabulary": diagnostic_terms,
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


def build_run_summary(records: list[dict[str, Any]]) -> dict[str, Any]:
    product_type_counts = Counter(r["product_type"] for r in records)
    roi_specificity_counts = Counter(r["roi_diagnostics"]["roi_specificity"] for r in records)
    warning_counts = Counter(w for r in records for w in r["warnings"])
    category_specific_warning_counts = Counter(
        w for r in records for w in r["category_specific_warnings"]
    )

    vocabulary_counts: dict[str, dict[str, int]] = {}
    vocabulary_counter: dict[str, Counter] = defaultdict(Counter)
    for record in records:
        for group, terms in record.get("diagnostic_vocabulary", {}).items():
            vocabulary_counter[group].update(terms)

    for group, counter in vocabulary_counter.items():
        vocabulary_counts[group] = dict(sorted(counter.items()))

    confidence_counts = Counter(
        r["classification_diagnostics"]["classification_confidence"]
        for r in records
    )
    competing_signal_records = [
        {
            "image_path": r["image_path"],
            "product_type": r["product_type"],
            "competing_product_type_terms": r["classification_diagnostics"]["competing_product_type_terms"],
        }
        for r in records
        if r["classification_diagnostics"]["competing_product_type_terms"]
    ]
    low_confidence_records = [
        {
            "image_path": r["image_path"],
            "product_type": r["product_type"],
            "classification_reason": r["classification_diagnostics"]["classification_reason"],
        }
        for r in records
        if r["classification_diagnostics"]["classification_confidence"] == "low"
    ]

    return {
        "image_count": len(records),
        "product_type_counts": dict(sorted(product_type_counts.items())),
        "roi_specificity_counts": dict(sorted(roi_specificity_counts.items())),
        "warning_counts": dict(sorted(warning_counts.items())),
        "category_specific_warning_counts": dict(sorted(category_specific_warning_counts.items())),
        "diagnostic_vocabulary_counts": vocabulary_counts,
        "classification_confidence_counts": dict(sorted(confidence_counts.items())),
        "competing_category_signal_records": competing_signal_records,
        "low_confidence_records": low_confidence_records,
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
    run_summary = build_run_summary(records)

    payload = {
        "schema": "active_layers.hero_candidates_stage2b.v1",
        "advisory_only": True,
        "manual_override_policy": "Automation suggests. Manual Hero Manager selections win.",
        "score_scope": SCORE_SCOPE,
        "classification_note": "Product and diagnostic vocabulary are matched against normalized word tokens. Underscores, hyphens, spaces, colons, semicolons, slashes, and repeated whitespace are treated as ordinary separators.",
        "project_root": "project-relative paths only",
        "inputs": inputs,
        "image_count": len(records),
        "run_summary": run_summary,
        "records": records,
    }

    output_path.write_text(json.dumps(payload, indent=2), encoding="utf-8")
    print(f"[stage1] analysed {len(records)} image(s)")
    print(f"[stage1] wrote {rel_path(output_path)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
