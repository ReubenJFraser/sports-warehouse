import os, sys, cv2, argparse, pathlib
import numpy as np
import pandas as pd
from tqdm import tqdm
import mediapipe as mp

def clamp(v, lo, hi): return max(lo, min(hi, v))

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--images-root", required=True)
    ap.add_argument("--exts", default=".jpg,.jpeg,.png,.webp")
    ap.add_argument("--out-csv", default="headroom_mediapipe.csv")
    ap.add_argument("--min-size", type=int, default=320)
    args = ap.parse_args()

    root = pathlib.Path(args.images_root)
    exts = {e.strip().lower() for e in args.exts.split(",") if e.strip()}
    paths = [p for p in root.rglob("*") if p.suffix.lower() in exts]

    mp_fd = mp.solutions.face_detection
    detector = mp_fd.FaceDetection(model_selection=1, min_detection_confidence=0.5)

    rows = []
    for p in tqdm(paths, desc="Scoring headroom (MediaPipe)"):
        im = cv2.imdecode(np.fromfile(str(p), dtype=np.uint8), cv2.IMREAD_COLOR)
        if im is None:
            rows.append({"image_path": str(p), "error": "decode_failed"})
            continue
        h, w = im.shape[:2]
        scale = args.min_size / min(h, w) if min(h, w) > args.min_size else 1.0
        im_in = cv2.resize(im, (int(w*scale), int(h*scale))) if scale != 1.0 else im
        ih, iw = im_in.shape[:2]

        rgb = cv2.cvtColor(im_in, cv2.COLOR_BGR2RGB)
        res = detector.process(rgb)

        face_count = 0
        face_top_pct = headroom_pct = focus_y_pct = None

        if res.detections:
            face_count = len(res.detections)
            # highest face (smallest y)
            top_y = 1e9
            center_y = None
            for d in res.detections:
                bb = d.location_data.relative_bounding_box
                y = bb.ymin * ih
                if y < top_y:
                    top_y = y
                    center_y = (bb.ymin + bb.height/2) * ih
            if scale != 1.0:
                top_y   /= scale
                center_y/= scale
            face_top_pct = clamp(top_y / h * 100.0, 0.0, 100.0)
            headroom_pct = face_top_pct
            focus_y_pct  = clamp(center_y / h * 100.0 - 6.0, 8.0, 35.0)

        crop_safe = int((headroom_pct or 0) >= 6.0) if face_count > 0 else None

        rows.append({
            "image_path": str(p),
            "image_basename": p.name,
            "width": w, "height": h, "ratio": round(w/h,4),
            "face_count": face_count,
            "face_top_pct": round(face_top_pct,2) if face_top_pct is not None else None,
            "headroom_pct": round(headroom_pct,2) if headroom_pct is not None else None,
            "focus_y_pct": round(focus_y_pct,1) if focus_y_pct is not None else None,
            "crop_safe": crop_safe
        })

    pd.DataFrame(rows).to_csv(args.out_csv, index=False, encoding="utf-8")
    print(f"[OK] wrote {args.out_csv}")

if __name__ == "__main__":
    main()



