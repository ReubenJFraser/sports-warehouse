# System Data Governance Model
## Authority, Control, and Flow of Product Data in the Sports Warehouse System

---

## Purpose

This README defines the **data governance model** for the Sports Warehouse system.

Its purpose is to establish clear authority over product data and to document how data moves through the system from creation to website presentation.

The document ensures that all contributors understand:

- which system is the authoritative source of product data
- which systems perform validation or transformation
- which systems consume data in read-only form

By defining these governance roles explicitly, the system avoids ambiguity about where product information originates and how updates are applied.

---

## Scope

This document covers:

- the authority hierarchy of data sources
- the flow of product data through the system
- the responsibilities of each layer in the pipeline
- the rules governing how product data may be modified

This document does not cover:

- database schema design
- SQL queries used to import or update data
- website application code
- REST API implementation

Those topics are documented separately.

---

## Governance Principles

The Sports Warehouse system follows several core governance principles.

Product data must have a single authoritative source.

Product editing must occur in a controlled editorial environment.

Production systems must not allow uncontrolled modification of product data.

Changes to product information must propagate through a predictable pipeline.

Consumers of product data must not become editors of that data.

These principles ensure consistency and prevent fragmentation of the product catalogue.

---

## Data Authority Hierarchy

Product information flows through the system according to a clearly defined authority hierarchy.

Excel Product Database  
Authoritative source of product data

Local MySQL Database  
Validation and staging environment

Cloud MySQL Database  
Production database serving the website

Website Application  
Read-only consumer of product data

Each layer has different responsibilities and authority levels.

---

## Excel as the Source of Truth

The Excel product database is the **authoritative source of product information**.

All product data originates here.

Examples of data maintained in Excel include:

- product names
- product descriptions
- brand assignments
- category classification
- gender designation
- product attributes
- price information
- image paths

Excel serves as the editorial control point for the catalogue.

Changes to the catalogue must begin in Excel before entering the database pipeline.

The website and production database must never become alternative editing environments for product data.

---

## Local Database Governance Role

The local MySQL database accessed through DBeaver acts as the **staging and validation environment**.

Its responsibilities include:

- importing product data from Excel
- verifying schema compatibility
- validating data integrity
- testing filtering and query behaviour
- identifying errors before deployment

This environment allows safe experimentation without affecting the live website.

No direct editing of product data should occur in this environment except for testing purposes.

---

## Production Database Governance Role

The Cloudways MySQL database serves as the **production storage layer** for the product catalogue.

Responsibilities include:

- storing the validated product catalogue
- serving product data to the website
- supporting filtering and sorting queries
- maintaining data consistency

The production database receives updates only after validation in the staging environment.

Direct editing of product records in production is discouraged because it bypasses the editorial workflow.

---

## Website Governance Role

The website functions as a **read-only consumer of product data**.

Its responsibilities include:

- retrieving product information from the production database
- presenting product listings to customers
- supporting category navigation and filtering
- displaying images and descriptions

The website must not become a system for editing catalogue data.

All product changes must originate upstream in the Excel database.

---

## Data Flow Through the System

Product information moves through the system according to the following pipeline.

Excel Product Database  
↓ Export

Local MySQL Database  
↓ Validation

Cloud MySQL Production Database  
↓ Query

Website Application

This pipeline ensures that all product data passes through validation before reaching the production environment.

The pipeline also guarantees that the website always displays consistent and verified product information.

---

## Governance Rules for Data Modification

The following rules govern how product data may be modified.

Product attributes must be edited in the Excel database.

Database imports must occur through the defined staging process.

Production database records must not be manually edited except during controlled maintenance.

Website interfaces must not provide direct editing capabilities for catalogue data.

Any structural changes to the catalogue schema must first be tested in the staging environment.

These rules maintain consistency across the entire catalogue.

---

## Relationship to Other Architecture Documents

This document forms part of a broader architectural documentation set.

Related documents include:

Product Catalogue Architecture  
Defines the structural pipeline used to deliver product information.

MongoDB Learning Architecture  
Explains the role of MongoDB as a parallel learning system rather than part of the operational catalogue database.

Together, these documents define the full data architecture of the Sports Warehouse system.

---

## Known Gaps and Open Questions

Several governance areas may evolve in the future.

Possible future improvements include:

- automated validation scripts during data import
- version control for Excel catalogue files
- structured auditing of catalogue changes
- automated synchronization pipelines

These areas may be addressed as the system matures.

---

## Guiding Principles

The Sports Warehouse data architecture follows several guiding principles.

Product information must originate in a controlled editorial system.

The SQL database remains the authoritative storage layer for catalogue data.

Production systems must not become alternative editing environments.

Data must move through a predictable and verifiable pipeline.

Architectural clarity is prioritised over unnecessary technological complexity.

These principles ensure that the product catalogue remains maintainable as the system evolves.
