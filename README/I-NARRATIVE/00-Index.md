# Sports Warehouse — Documentation Index

This index defines the **authoritative reading order** for the project documentation.
Documents are ordered so that **purpose and context are understood before constraints and contracts**.

---

## Core Orientation

### 01-Project_Overview_and_Navigation.md  
→ What this project is, why it exists, and how its major parts fit together.

This document provides the **contextual overview** of the system as a whole, including
where the Hero Manager sits within the broader project architecture.

---

### 02-Hero_Manager—Product_Intent_&_UX_Goals.md  
→ What the Hero Manager is for, what problem it solves, and how humans and automation interact.

This document defines the **product intent and UX goals** of the Hero Manager.
It is the primary reference for understanding *why* hero images are selected,
how automation assists humans, and what “success” looks like from a usability perspective.

---

## System Constraints & Guarantees

### 03-Codex-Behavioural_Rules.md  
→ How Codex (and AI-assisted processes) must think, decide, and act within this project.

These rules define **behavioral guardrails** for automated reasoning and assistance.
They do not define product intent; they constrain how intent may be implemented safely.

---

### 04-Codex-Architecture_Invariants.md  
→ Non-negotiable structural constraints for the system.

This document defines architectural decisions that are **frozen** and must not be violated,
regardless of feature evolution or implementation detail changes.

---

### 05-Codex-Routing_Invariants.md  
→ Canonical routing semantics and catalog segment meaning.

This document defines routing behavior, URL structure, and the semantic meaning of
catalog segments to ensure consistency across the application.

---

## Data & Authority Contracts

### 06-Excel-Database_Contract.md  
→ Data meanings, schema rules, and import discipline.

This document defines Excel as the **authoritative editorial source** and specifies
how data is structured, interpreted, and prepared for ingestion.

---

### 07-Hero_Image_Authority_Contract.md  
→ Authority rules governing hero image selection, overrides, and persistence.

This document defines **who or what is allowed to write hero image state**,
and under which conditions, without redefining the purpose of the Hero Manager.

---

### 08-Excel-to-Db_Ingestion_Authority_Contract.md  
→ Rules governing the controlled ingestion of Excel data into the database.

This document specifies ingestion authority, sequencing, and safeguards to ensure
database state accurately reflects the authoritative Excel source.

---
