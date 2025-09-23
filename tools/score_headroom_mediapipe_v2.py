import os, cv2, argparse, pathlib
import numpy as np
import pandas as pd
from tqdm import tqdm
import mediapipe as mp

def clamp(v, lo, hi): return max(lo, min(hi, v))

def composite_rgba_on_bg(img_rgba, bg=(224,224,224)):
    """img_rgba: HxWx4 BGRA/BGRA; returns HxWx3 BGR composited on bg color."""
    if img_rgba.shape[2] == 3:
        return img_rgba
    if img_rgba.shape[2] == 4:
        b,g,r,a = cv2.split(img_rgba)
        alpha = a.astype(np.float32) / 255.0
        alpha3 = cv2.merge([alpha, alpha, alpha])
        bgimg = np.full(img_rgba.shape[:2] + (3,), bg, dtype=np.uint8)
        rgb = cv2.merge([b,g,r]).astype(np.float32)
        out = (alpha3 * rgb + (1.0 - alpha3) * bgimg.astype(np.float32)).astype(np.uint8)
        return out
    return img_rgba[..., :3]

def pad_canvas(img_bgr, pad_pct=(0.12, 0.08, 0.06, 0.06), bg=(224,224,224)):
    """Pad top,bottom,left,right by percentages of original height/width."""
    h, w = img_bgr.shape[:2]
    pad_top, pad_bottom, pad_left, pad_right = pad_pct
    pt = int(round(h * pad_top)); pb = int(round(h * pad_bottom))
    pl = int(round(w * pad_left)); pr = int(round(w * pad_right))
    H, W = h + pt + pb, w + pl + pr
    canvas = np.full((H, W, 3), bg, dtype=np.uint8)
    canvas[pt:pt+h, pl:pl+w, :] = img_bgr
    return canvas, (pt, pl)

def alpha_top_margin_pct(img_rgba, thresh=16):
    """Return percentage from image top to first opaque row; None if no alpha."""
    if img_rgba is None or img_rgba.shape[2] < 4:
        return None
    a = img_rgba[..., 3]
    rows = np.where((a > thresh).any(axis=1))[0]
    if rows.size == 0:
        return None
    top_row = int(rows.min())
    return top_row / img_rgba.shape[0] * 100.0

def detect_face_top_y(img_bgr, mp_fd_short, mp_fd_full, min_size=320, conf_short=0.3, conf_full=0.3):
    """Try short-range then full-range; return (face_count, top_y, center_y) in ORIGINAL (unpadded) coords."""
    # we pass in already-padded image; caller will unpad using offsets
    h, w = img_bgr.shape[:2]
    scale = min_size / min(h, w) if min(h, w) > 0 and min(h, w) < min_size else 1.0
    im_in = cv2.resize(img_bgr, (int(w*scale), int(h*scale))) if scale != 1.0 else img_bgr
    ih, iw = im_in.shape[:2]
    rgb = cv2.cvtColor(im_in, cv2.COLOR_BGR2RGB)

    # short-range first
    mp_fd_short.min_detection_confidence = conf_short
    res = mp_fd_short.process(rgb)
    if not res.detections:
        # full-range fallback
        mp_fd_full.min_detection_confidence = conf_full
        res = mp_fd_full.process(rgb)

    if not res.detections:
        return 0, None, None

    face_count = len(res.detections)
    top_y = 1e9
    center_y = None
    for d in res.detections:
        bb = d.location_data.relative_bounding_box
        y = float(bb.ymin) * ih
        c = (float(bb.ymin) + float(bb.height)/2.0) * ih
        if y < top_y:
            top_y = y
            center_y = c

    # unscale back to padded coords
    if scale != 1.0:
        top_y /= scale
        center_y /= scale
    return face_count, top_y, center_y

def main():
    ap = argparse.ArgumentParser(description="Score headroom & crop-safety using MediaPipe + alpha fallback")
    ap.add_argument("--images-root", required=True, help="Root folder containing images")
    ap.add_argument("--exts", default=".jpg,.jpeg,.png,.webp", help="Comma-separated extensions")
    ap.add_argument("--out-csv", default="headroom_mediapipe.csv", help="Output CSV path")
    ap.add_argument("--min-size", type=int, default=384, help="Detector min input short side (upsample if smaller)")
    ap.add_argument("--pad-top", type=float, default=0.12, help="Top pad as fraction of height")
    ap.add_argument("--pad-bottom", type=float, default=0.08, help="Bottom pad fraction")
    ap.add_argument("--pad-left", type=float, default=0.06, help="Left pad fraction")
    ap.add_argument("--pad-right", type=float, default=0.06, help="Right pad fraction")
    ap.add_argument("--alpha-unsafe-thresh", type=float, default=1.5, help="If alpha top margin < this %, mark unsafe")
    ap.add_argument("--face-unsafe-thresh", type=float, default=6.0, help="If face top margin < this %, mark unsafe")
    args = ap.parse_args()

    root = pathlib.Path(args.images_root)
    exts = {e.strip().lower() for e in args.exts.split(",") if e.strip()}
    paths = [p for p in root.rglob("*") if p.suffix.lower() in exts]

    print(f"[headroom] root: {root}")
    print(f"[headroom] found {len(paths)} image(s)")

    # Two MediaPipe detectors: short-range (0) and full-range (1)
    mp_fd = mp.solutions.face_detection
    fd_short = mp_fd.FaceDetection(model_selection=0, min_detection_confidence=0.3)
    fd_full  = mp_fd.FaceDetection(model_selection=1, min_detection_confidence=0.3)

    rows = []
    for p in tqdm(paths, desc="Scoring headroom v2"):
        # robust disk read (Windows)
        data = np.fromfile(str(p), dtype=np.uint8)
        if data.size == 0:
            rows.append({"image_path": str(p), "image_basename": p.name, "error": "read_failed"})
            continue

        # keep alpha if present
        img_rgba = cv2.imdecode(data, cv2.IMREAD_UNCHANGED)
        if img_rgba is None:
            rows.append({"image_path": str(p), "image_basename": p.name, "error": "decode_failed"})
            continue

        # compute alpha-top fallback (if RGBA)
        alpha_top_pct = None
        if img_rgba.ndim == 3 and img_rgba.shape[2] >= 4:
            alpha_top_pct = alpha_top_margin_pct(img_rgba)

        # BGR composite on neutral gray
        img_bgr = composite_rgba_on_bg(img_rgba)

        h, w = img_bgr.shape[:2]

        # pad canvas for better detection (adds room above head)
        pad_pct = (args.pad_top, args.pad_bottom, args.pad_left, args.pad_right)
        padded, (pt, pl) = pad_canvas(img_bgr, pad_pct=pad_pct)

        # detect on padded
        face_count, top_y_pad, center_y_pad = detect_face_top_y(
            padded, fd_short, fd_full, min_size=args.min_size, conf_short=0.3, conf_full=0.3
        )

        face_top_pct = headroom_pct = focus_y_pct = None
        used_reason = None
        crop_safe = None

        if face_count > 0 and top_y_pad is not None:
            # convert top_y back to ORIGINAL (unpadded) coords
            top_y_unpadded = top_y_pad - pt
            top_y_unpadded = max(0.0, top_y_unpadded)
            face_top_pct = clamp(top_y_unpadded / h * 100.0, 0.0, 100.0)
            headroom_pct = face_top_pct
            # “center minus a bit” heuristic for framing
            if center_y_pad is not None:
                center_y_unpadded = clamp(center_y_pad - pt, 0.0, float(h))
                focus_y_pct = clamp(center_y_unpadded / h * 100.0 - 6.0, 8.0, 35.0)
            crop_safe = 1 if face_top_pct >= args.face_unsafe_thresh else 0
            used_reason = "face"
        else:
            # Face not found → use alpha-edge headroom if available
            if alpha_top_pct is not None:
                crop_safe = 0 if alpha_top_pct < args.alpha_unsafe_thresh else 1
                headroom_pct = alpha_top_pct
                used_reason = "alpha"
            else:
                # No face, no alpha: default safe so we keep 'cover' for non-human products
                crop_safe = 1
                used_reason = "none"

        rows.append({
            "image_path": str(p),
            "image_basename": p.name,
            "width": w, "height": h, "ratio": round(w/h,4) if h else None,
            "face_count": face_count if face_count is not None else None,
            "face_top_pct": round(face_top_pct,2) if face_top_pct is not None else None,
            "headroom_pct": round(headroom_pct,2) if headroom_pct is not None else None,
            "focus_y_pct": round(focus_y_pct,1) if focus_y_pct is not None else None,
            "alpha_top_pct": round(alpha_top_pct,2) if alpha_top_pct is not None else None,
            "final_crop_safe": int(crop_safe) if crop_safe is not None else None,
            "decision_reason": used_reason
        })

    df = pd.DataFrame(rows)
    df.to_csv(args.out_csv, index=False, encoding="utf-8")
    print(f"[headroom] wrote {args.out_csv} ({len(df)} rows)")

if __name__ == "__main__":
    main()

