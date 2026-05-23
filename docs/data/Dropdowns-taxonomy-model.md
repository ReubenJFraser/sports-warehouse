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

## Intentional blank `subCategoryParent` exception rule

Normally, `subCategoryParent` is expected to be populated from the Dropdowns `subCategory` -> `subCategoryParent` mapping for each product row.

A narrow exception is allowed for **known Set component rows** where:

- `categoryName = Set` is being used to describe the row's bundle/grouping context, and
- `subCategory` still describes the component’s intrinsic product type (for example `T_Shirt`, `Kid_Shoes`, `Backpack`).

In this exception case, `subCategoryParent` may be intentionally left blank **only when explicitly documented as a known Set component exception**.

Current known intentional blank `subCategoryParent` exceptions:

1. `Adidas | Marvel Spider-Man: T-Shirt` (`categoryName=Set`, `subCategory=T_Shirt`)
2. `Adidas | Marvel Spider-Man: Light-Up Trainers` (`categoryName=Set`, `subCategory=Kid_Shoes`)
3. `Adidas | Marvel Spider-Man: Backpack` (`categoryName=Set`, `subCategory=Backpack`)

Concise rule: **Blank `subCategoryParent` values should be explained. They are acceptable only when documented as known Set component exceptions; otherwise they require review.**
