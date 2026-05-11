# Image Colour Model — Authoring and Ingestion Contract

## 1. Purpose

This document defines the **binding rules** governing how product colours are authored and how image paths are generated in the Sports Warehouse system.

This is a contractual document.  
No interpretation or deviation is permitted.

---

## 2. Product Row Definition

- One Excel row represents **one product style**
- A product style may have **multiple colour realizations**
- Colour realizations must not be represented as separate rows

---

## 3. Colour Authoring Rules

- Colours are authored in a column named `colour`
- The value is a **comma-delimited list of lowercase slugs**
- No spaces are permitted

Example:

black,white,red,yellow,green


---

## 4. Excel Responsibilities

Excel must contain:

- all structural inputs required to generate image paths
- the complete set of colours for the product style

Excel must not contain:

- per-colour image-set paths
- file-level image paths
- thumbnail manifests
- any mechanically expanded media artifacts

---

## 5. Image Path Generation

- Image-set paths are generated **downstream**
- Generation is deterministic and based solely on Excel inputs
- One image-set folder is generated per declared colour

No inference, guessing, or normalization is permitted.

---

## 6. thumbnails_json

- `thumbnails_json` is a derived artifact
- It enumerates file-level image paths
- It must not be hand-authored
- It may be regenerated at any time

---

## 7. Validation and Failure

- If a declared colour does not resolve to a valid image set, ingestion must fail
- Missing, extra, or malformed realizations are errors
- Silent fallback behavior is forbidden

---

## 8. Prohibited Patterns

The following are explicitly forbidden:

- one row per colour
- storing expanded image paths in Excel
- storing file-level paths in Excel
- inferring colours from folders
- partial or implicit colour declarations

---

## 9. Authority

This contract is authoritative for:

- Excel authoring
- ingestion
- downstream media generation
- audits

Any change requires explicit versioning and documentation.

---

## 10. Lock Statement

Colours are declared, not expanded, in Excel.  
Image paths are generated, not authored.  
One row declares one product style.

This contract is binding.



