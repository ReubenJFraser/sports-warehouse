# Sports Warehouse Product Catalogue Pipeline
## Excel → SQL → Website Architecture

---

## Purpose

This README explains the conceptual architecture behind the **Sports Warehouse product catalogue pipeline**.

The document exists to preserve a clear understanding of how the system functions as a simplified form of the **product information management pipelines used by large e-commerce companies**.

The goal is not merely to document implementation mechanics, but to capture the **design reasoning** behind the pipeline so that future development decisions remain aligned with the system’s architectural intent.

This document ensures that the pipeline is understood as a **deliberate editorial data workflow**, rather than an accidental or temporary solution.

---

## Scope

This document covers:

- the conceptual architecture of the Sports Warehouse product catalogue
- the role of Excel as an editorial data source
- the role of SQL databases in staging and production
- how the pipeline resembles the product information management (PIM) systems used in professional e-commerce environments

This document does not cover:

- the implementation details of database schemas
- SQL queries used for importing product data
- website application code
- deployment procedures

Those topics are documented elsewhere.

---

## System Overview

The Sports Warehouse product catalogue follows a structured editorial data pipeline:

Excel → Local SQL Database → Production SQL Database → Website

Conceptually the workflow can be represented as:

Excel (Product Authoring)  
↓  
Local MySQL Database (Development / Staging)  
↓  
Cloud MySQL Database (Production)  
↓  
Website Application (Customer Interface)

Each stage in this pipeline serves a specific responsibility within the overall system.

---

## Conceptual Roles

### Excel — Product Authoring Layer

Excel acts as the **primary editorial interface** for the product catalogue.

Product data is created and maintained in spreadsheet form before being imported into the SQL database.

Typical information managed within Excel includes:

- product names
- brand identifiers
- categories
- gender classification
- product descriptions
- price information
- image paths
- product attributes

Excel provides several advantages for this role:

- human-readable editing
- bulk editing capabilities
- structured columns that enforce consistency
- easy validation of large product catalogues
- simple export of structured data

In conceptual terms, Excel functions as a **lightweight Product Information Management (PIM) system**.

---

### Local SQL Database — Development and Staging

The local database environment, accessed through DBeaver, serves as the **staging layer** of the pipeline.

Responsibilities include:

- importing data from Excel
- validating data integrity
- testing schema changes
- confirming query behaviour
- verifying data relationships

This environment allows experimentation without affecting the production website.

In large commercial systems this layer would correspond to a **staging database or integration database**.

---

### Cloud SQL Database — Production Environment

The production database hosted on Cloudways stores the live product catalogue used by the website.

Responsibilities include:

- storing the product catalogue
- supporting website queries
- serving product information to users
- maintaining data consistency

The production database receives data only after it has been validated locally.

This separation between staging and production is an important engineering practice that reduces the risk of data corruption.

---

### Website — Customer Experience Layer

The website reads product information directly from the production SQL database.

Responsibilities include:

- displaying product listings
- supporting category navigation
- enabling filtering and sorting
- presenting product descriptions and images

The website functions purely as a **consumer of structured catalogue data**.

It does not act as the authoritative source for product information.

---

## Relationship to Professional E-commerce Architectures

Large e-commerce companies use a similar conceptual pipeline.

A typical enterprise architecture resembles:

Product Editors  
↓  
Product Information Management System (PIM)  
↓  
Data Processing Pipeline (ETL)  
↓  
SQL Production Database  
↓  
Website and APIs

In this architecture the PIM system manages all editorial product information.

Examples of enterprise PIM platforms include:

- Akeneo
- Pimcore
- inRiver

These platforms provide structured editing environments for managing product catalogues.

---

## Excel as a Lightweight PIM

Within the Sports Warehouse system, Excel performs many of the same roles as a PIM system.

Comparable responsibilities include:

Product catalogue editing  
Attribute management  
Category assignment  
Image path management  
Bulk data updates  
Structured export to the production database

This relationship can be summarized conceptually as:

Excel = Simplified Product Information Management System

Although Excel lacks advanced workflow tools found in enterprise PIM systems, it provides the essential capabilities required for managing a structured product catalogue.

---

## Staging and Production Separation

Large companies rarely write product data directly to the production database.

Instead they follow a staged process:

PIM → Staging Database → Validation → Production Database

The Sports Warehouse system mirrors this approach:

Excel → Local SQL Database → Production SQL Database

This staged workflow provides several advantages:

- data validation before deployment
- protection of the production environment
- safer schema experimentation
- controlled updates to the catalogue

The conceptual model therefore remains consistent with professional e-commerce practices.

---

## Why SQL Is Well Suited to Product Catalogues

Product catalogues contain highly structured relationships.

Typical relationships include:

Product → Brand  
Product → Category  
Product → Gender  
Product → Price  
Product → Images

Relational databases are designed to manage these structured relationships efficiently.

SQL databases provide:

- relational table structures
- indexed queries
- efficient filtering
- reliable joins between related entities

For this reason, most major e-commerce platforms continue to use SQL databases.

Examples include:

Shopify (MySQL)  
WooCommerce (MySQL)  
Magento (MySQL)  
BigCommerce (MySQL / PostgreSQL)

The Sports Warehouse architecture therefore aligns with established industry practice.

---

## Difference Between Small-Scale and Enterprise Systems

The primary difference between the Sports Warehouse system and large e-commerce infrastructures is scale rather than architectural principle.

Enterprise platforms replace Excel with specialised software:

Product Editors  
↓  
PIM System  
↓  
ETL Pipelines  
↓  
SQL Database Cluster  
↓  
Website Infrastructure

However, the conceptual structure remains the same:

Editorial product management → Structured database → Website delivery

The Sports Warehouse pipeline therefore represents a **simplified implementation of the same underlying concept**.

---

## Optional Future Extensions

The current architecture focuses exclusively on the **product catalogue**.

Advanced systems sometimes introduce additional data stores for behavioural data.

Examples include:

User activity logs  
Search analytics  
Recommendation systems  
Customer browsing behaviour

These systems may use NoSQL databases such as MongoDB.

However, such systems typically complement rather than replace the SQL catalogue database.

These patterns are considered optional and are not part of the current Sports Warehouse architecture.

---

## Known Gaps and Open Questions

The following areas remain open for potential future evolution:

- automated synchronization between Excel and the SQL database
- validation scripts for detecting inconsistent product attributes
- image asset management workflows
- integration with analytics systems

These areas do not affect the conceptual pipeline described in this document.

---

## Guiding Principles

The Sports Warehouse product catalogue pipeline follows several guiding principles:

Editorial data must originate outside the website application.

The SQL database remains the authoritative storage layer for product information.

Product data changes must pass through staging before reaching production.

Human-readable editing environments are preferred for catalogue management.

The system architecture should remain stable and understandable rather than overly complex.

These principles ensure that the product catalogue remains maintainable as the system evolves.
