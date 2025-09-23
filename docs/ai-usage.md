# AI Usage Guide – Sports Warehouse Project

This document explains how we will use **GitHub Copilot**, **Claude via CodeGPT**, and **ChatGPT-5 (browser)** together in the Sports Warehouse repo. It sets expectations and gives ready-to-paste prompts tailored to this project.

---

## 1. General Principles

- **Copilot (VS Code sidebar + inline)**: best for quick code completions, writing boilerplate, suggesting functions/classes, and staying in flow while coding.  
- **Claude (CodeGPT extension in VS Code)**: best for deeper analysis of whole files or the repo, producing structured plans, and explaining/refactoring large chunks of code.  
- **ChatGPT-5 (browser)**: best for big-picture planning, long-form writing, strategy docs, and iterative discussions with Reuben (the developer).

---

## 2. Project Context

- **Repo name**: `sports-warehouse-home-page`
- **Stack**: PHP (Laravel-like structure), MySQL, Docker, VS Code setup
- **Website purpose**:  
  - Showcase Adidas-led product catalog (with Nike, Stax, Asics as secondary brands).  
  - Use a sidebar product display with carousels, banners, and videos.  
  - Standardized **near-square product card images**, with pop-up viewer for mixed-aspect images.  
  - Database-backed with products, banners, and videos.  
  - Goal: professional-level showcase site, demonstrating DB integration + design skills.

---

## 3. Example Usage Scenarios

### A. Copilot (quick code help)
**When to use:** Inline completions, small fixes, boilerplate.

```plaintext
// Example in PHP
/** 
 * Prompt Copilot inline:
 * "Add a function here that queries products by brand, sorted by price ascending."
 */

