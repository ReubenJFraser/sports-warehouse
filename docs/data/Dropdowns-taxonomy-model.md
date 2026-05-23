# Dropdowns worksheet taxonomy model

This project treats the **Dropdowns** worksheet as two lookup structures that are laid out side by side, not as one single table.

## Lookup structure A: top-level category lookup

- `categoryID`
- `categoryName`

Purpose: product-facing top-level category identity and label.

## Lookup structure B: subcategory-to-parent mapping lookup

- `subCategory`
- `subCategoryParent`

Purpose: validation/mapping helper that maps each `subCategory` (product type) to its top-level taxonomy parent.

## Product-row meaning

- `categoryName` is the product/top-level category shown and used on the product row.
- `subCategory` is the product type.
- `subCategoryParent` is derived from the Dropdowns worksheet mapping (`subCategory` + `subCategoryParent`).

`categoryName` and `subCategoryParent` may duplicate the same value in many product rows, but for different reasons:

- `categoryName` is **product-facing** row data.
- `subCategoryParent` is **validation/mapping-facing** helper data.

## Scope note

This documentation update renames the taxonomy helper field from `parentCategory` to `subCategoryParent` in CSV/import/staging documentation contexts only. It does not change live database tables or frontend publication behavior.
