# Sports Warehouse – Database Schema (DDL Extract)

This file contains the **current table structure** (as of Aug 2025) and a **future schema suggestion** for handling multiple product images in the database instead of relying on filesystem paths.

---

## Current Tables

### Table: `category`

```sql
CREATE TABLE `category` (
  `categoryId` int NOT NULL,
  `categoryName` varchar(100) NOT NULL,
  `parentCategory` varchar(100) DEFAULT NULL,
  `subCategory` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`categoryId`),
  KEY `ix_categoryName` (`categoryName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

Table: item

CREATE TABLE `item` (
  `itemId` int NOT NULL AUTO_INCREMENT,
  `itemName` varchar(150) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `salePrice` decimal(10,2) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `categoryId` int NOT NULL DEFAULT '0',
  `categoryName` varchar(100) DEFAULT NULL,
  `parentCategory` varchar(100) DEFAULT NULL,
  `activity_tags` varchar(100) DEFAULT NULL,
  `age_group` varchar(20) DEFAULT NULL,
  `size_type` varchar(20) DEFAULT NULL,
  `fit_style` varchar(20) DEFAULT NULL,
  `images` varchar(2000) DEFAULT NULL,
  `orientation` char(1) NOT NULL DEFAULT 'S' COMMENT '''P'' = portrait, ''L'' = landscape, ''S'' = square (default)',
  `thumbnails_json` text COMMENT 'JSON array of all source-image paths',
  `altText` text,
  `ariaText` text,
  `videoAltText` text,
  `videos` text,
  PRIMARY KEY (`itemId`),
  KEY `itemId` (`itemId`),
  KEY `FK_itemCategory` (`categoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=latin1;

Future Schema Suggestion

To properly support multiple images per product, we propose introducing a new item_image table.
This replaces reliance on serialized strings (item.images, thumbnails_json) and provides normalized DB storage.

Table: item_image (proposed)

CREATE TABLE `item_image` (
  `imageId` int NOT NULL AUTO_INCREMENT,
  `itemId` int NOT NULL,
  `imageUrl` varchar(500) NOT NULL,       -- relative or absolute URL
  `isPrimary` tinyint(1) NOT NULL DEFAULT 0,
  `altText` varchar(500) DEFAULT NULL,    -- accessibility text
  `ariaText` varchar(500) DEFAULT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `sortOrder` int DEFAULT 0,              -- determines order in carousel
  PRIMARY KEY (`imageId`),
  KEY `FK_itemImage_item` (`itemId`),
  CONSTRAINT `FK_itemImage_item`
    FOREIGN KEY (`itemId`) REFERENCES `item` (`itemId`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

Notes

DB as Source of Truth: Going forward, item_image is the authoritative store for product media.

Migration Plan:

Migrate existing JSON/image strings from item.images → rows in item_image.

Retain item.images only as a legacy fallback until migration is complete.

Primary Image: Set via isPrimary=1 (one per item).

Gallery / Carousel: All images for an item can be retrieved with a simple join:

SELECT * 
FROM item_image 
WHERE itemId = ? 
ORDER BY isPrimary DESC, sortOrder ASC;

Accessibility: Alt/ARIA text now lives alongside each image, not crammed into item.
