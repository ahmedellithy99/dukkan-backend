# Local Marketplace Platform - Database Schema & ERD

## Entity Relationship Diagram

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     USERS       │    │  GOVERNORATES   │    │     CITIES      │    │   LOCATIONS     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ name            │    │ name            │    │ governorate_id  │──┐ │ city_id (FK)    │──┐
│ email (UNIQUE)  │    │ slug (UNIQUE)   │    │ name            │  │ │ area (nullable) │  │
│ phone           │    │ created_at      │    │ slug            │  │ │ latitude        │  │
│ password        │    │ updated_at      │    │ created_at      │  │ │ longitude       │  │
│ role            │    └─────────────────┘    │ updated_at      │  │ │ created_at      │  │
│ status          │             │             └─────────────────┘  │ │ updated_at      │  │
│ created_at      │             └──────────────────────────────────┘ └─────────────────┘  │
│ updated_at      │                                                           │           │
└─────────────────┘                                                           │           │
         │                                                                    │           │
         │                    ┌─────────────────┐                            │           │
         │                    │     SHOPS       │                            │           │
         │                    ├─────────────────┤                            │           │
         │                    │ id (PK)         │                            │           │
         └────────────────────│ owner_id (FK)   │                            │           │
                              │ location_id(FK) │────────────────────────────┘           │
                              │ (UNIQUE)        │                                        │
                              │ name            │                                        │
                              │ slug (UNIQUE)   │                                        │
                              │ description     │                                        │
                              │ whatsapp_number │                                        │
                              │ phone_number    │                                        │
                              │ is_active       │                                        │
                              │ created_at      │                                        │
                              │ updated_at      │                                        │
                              └─────────────────┘                                        │
                                       │                                                 │
                                       │                                                 │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐                      │
│   CATEGORIES    │    │ SUBCATEGORIES   │    │    PRODUCTS     │                      │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤                      │
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │                      │
│ name            │    │ category_id(FK) │──┐ │ shop_id (FK)    │──────────────────────┘
│ slug (UNIQUE)   │    │ name            │  │ │ subcategory_id  │──┐
│ created_at      │    │ slug (UNIQUE)   │  │ │ name            │  │
│ updated_at      │    │ created_at      │  │ │ description     │  │
└─────────────────┘    │ updated_at      │  │ │ price           │  │
         │              └─────────────────┘  │ │ discount_type   │  │
         └─────────────────────────────────────┘ │ discount_value  │  │
                                                │ stock_quantity  │  │
                                                │ track_stock     │  │
                                                │ is_active       │  │
                                                │ created_at      │  │
                                                │ updated_at      │  │
                                                └─────────────────┘  │
                                                         │           │
                                                         │           │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐  │
│   ATTRIBUTES    │    │ATTRIBUTE_VALUES │    │PRODUCT_ATTRIBUTE│  │
├─────────────────┤    ├─────────────────┤    │    _VALUES      │  │
│ id (PK)         │    │ id (PK)         │    ├─────────────────┤  │
│ name            │    │ attribute_id(FK)│──┐ │ product_id (FK) │──┘
│ slug (UNIQUE)   │    │ value           │  │ │ attribute_value │──┐
│ created_at      │    │ created_at      │  │ │ _id (FK)        │  │
│ updated_at      │    │ updated_at      │  │ │ (Composite PK)  │  │
└─────────────────┘    └─────────────────┘  │ └─────────────────┘  │
         │                                  │                    │
         └──────────────────────────────────┘                    │
                                                                 │
                                              ┌──────────────────┘
                                              │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     MEDIA       │    │ PRODUCT_STATS   │    │                 │
├─────────────────┤    ├─────────────────┤    │   POLYMORPHIC   │
│ id (PK)         │    │ product_id (PK) │──┐ │   RELATIONS     │
│ model_type      │    │ views_count     │  │ │                 │
│ model_id        │    │ whatsapp_clicks │  │ │ Media can       │
│ uuid            │    │ sms_clicks      │  │ │ belong to:      │
│ collection_name │    │ favorites_count │  │ │ - Shops         │
│ name            │    │ last_viewed_at  │  │ │ - Products      │
│ file_name       │    │ created_at      │  │ │                 │
│ mime_type       │    │ updated_at      │  │ └─────────────────┘
│ disk            │    └─────────────────┘  │
│ conversions_disk│                         │
│ size            │                         │
│ manipulations   │                         │
│ custom_properties│                        │
│ generated_conversions│                    │
│ responsive_images│                        │
│ order_column    │                         │
│ created_at      │                         │
│ updated_at      │                         │
└─────────────────┘                         │
         │                                  │
         └──────────────────────────────────┘
```

## Complete SQL Schema

```sql
-- =============================================
-- USERS TABLE (Shop Owners & Admins)
-- =============================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('vendor', 'admin') NOT NULL DEFAULT 'vendor',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- GOVERNORATES TABLE
-- =============================================
CREATE TABLE governorates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    slug VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CITIES TABLE
-- =============================================
CREATE TABLE cities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    governorate_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(30) NOT NULL,
    slug VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (governorate_id) REFERENCES governorates(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_governorate_slug (governorate_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- LOCATIONS TABLE
-- =============================================
CREATE TABLE locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_id BIGINT UNSIGNED NOT NULL,
    area VARCHAR(100) NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
    INDEX idx_locations_city (city_id),
    INDEX idx_locations_city_area (city_id, area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SHOPS TABLE
-- =============================================
CREATE TABLE shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    whatsapp_number VARCHAR(20) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE RESTRICT,

    INDEX idx_shops_location_active (location_id, is_active),
    INDEX idx_shops_owner_active (owner_id, is_active),
    FULLTEXT idx_shops_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CATEGORIES TABLE
-- =============================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    slug VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBCATEGORIES TABLE
-- =============================================
CREATE TABLE subcategories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(40) NOT NULL,
    slug VARCHAR(40) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PRODUCTS TABLE
-- =============================================
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    subcategory_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NULL,
    discount_type ENUM('percent', 'amount') NULL,
    discount_value DECIMAL(10, 2) NULL,
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    track_stock BOOLEAN NOT NULL DEFAULT TRUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE RESTRICT,

    UNIQUE KEY unique_shop_slug (shop_id, slug),
    INDEX idx_products_shop_active_created (shop_id, is_active, created_at),
    INDEX idx_products_subcategory_active_created (subcategory_id, is_active, created_at),
    FULLTEXT idx_products_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ATTRIBUTES TABLE
-- =============================================
CREATE TABLE attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ATTRIBUTE_VALUES TABLE
-- =============================================
CREATE TABLE attribute_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attribute_id BIGINT UNSIGNED NOT NULL,
    value VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attribute_value (attribute_id, value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PRODUCT_ATTRIBUTE_VALUES TABLE (Pivot)
-- =============================================
CREATE TABLE product_attribute_values (
    product_id BIGINT UNSIGNED NOT NULL,
    attribute_value_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (product_id, attribute_value_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_value_id) REFERENCES attribute_values(id) ON DELETE CASCADE,

    INDEX idx_pav_attribute_value_product (attribute_value_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- MEDIA TABLE (Spatie Media Library)
-- =============================================
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    uuid CHAR(36) NULL UNIQUE,
    collection_name VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NULL,
    disk VARCHAR(255) NOT NULL,
    conversions_disk VARCHAR(255) NULL,
    size BIGINT UNSIGNED NOT NULL,
    manipulations JSON NOT NULL,
    custom_properties JSON NOT NULL,
    generated_conversions JSON NOT NULL,
    responsive_images JSON NOT NULL,
    order_column INTEGER UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_media_model (model_type, model_id),
    INDEX idx_media_order (order_column)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PRODUCT_STATS TABLE
-- =============================================
CREATE TABLE product_stats (
    product_id BIGINT UNSIGNED PRIMARY KEY,
    views_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    whatsapp_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    sms_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    favorites_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

    INDEX idx_product_stats_views (views_count),
    INDEX idx_product_stats_last_viewed (last_viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Key Design Decisions & Performance Optimizations

### 1. **Indexing Strategy**

- **Hierarchical location queries**: Optimized indexes for governorate → city → location relationships
- **Search optimization**: Fulltext indexes on shop and product names/descriptions (MySQL only)
- **Filter optimization**: Composite indexes on `(location_id, is_active)`, `(shop_id, is_active, created_at)`
- **Attribute filtering**: Reverse index `(attribute_value_id, product_id)` for efficient attribute-based product filtering
- **Analytics queries**: Focused indexes on `views_count` and `last_viewed_at` for performance

### 2. **Data Types & Constraints**

- **Coordinates**: `DECIMAL(10,8)` and `DECIMAL(11,8)` for precise GPS coordinates
- **Price & discounts**: `DECIMAL(10,2)` for accurate monetary values
- **Phone numbers**: `VARCHAR(20)` to accommodate international formats
- **Slugs**: Translation-ready slugs for categories, subcategories, and attributes
- **ENUMs**: Used for fixed value sets (role, status, discount_type)

### 3. **Translation Architecture**

- **Slug-based translations**: All major entities have slugs for future translation support
- **Shop-scoped product slugs**: Products have unique slugs within each shop, enabling SEO-friendly URLs like `/shops/shop-name/products/product-slug`
- **Consistent naming**: Slugs serve as translation keys across all languages
- **Future-ready**: Schema prepared for translation tables or JSON columns
- **SEO-friendly**: Slugs enable readable URLs for shops and products

### 4. **Analytics & Performance**

- **Separate stats table**: `product_stats` isolated for high-frequency updates
- **SMS tracking**: Added `sms_clicks` for Egyptian market preferences
- **Optimized counters**: `BIGINT UNSIGNED` for high-traffic scenarios
- **Minimal indexing**: Only essential indexes to avoid write performance impact

### 5. **Data Integrity**

- **Cascade deletes**: User deletion removes shops and products
- **Restrict deletes**: Location/category deletion prevented if referenced
- **Unique constraints**: Proper uniqueness for slugs, emails, and attribute combinations
- **Foreign key constraints**: Maintain referential integrity across all relationships

### 6. **Scalability Considerations**

- **Hierarchical locations**: Ready for multi-city expansion with governorate → city → location structure
- **Polymorphic media**: Flexible Spatie Media Library integration for shops and products
- **Pivot table optimization**: Efficient product-attribute relationships with smart indexing
- **Stock management**: Built-in inventory tracking with optional stock monitoring

### 7. **Future Expansion Hooks**

- **Multi-city architecture**: Location hierarchy supports unlimited city expansion
- **Translation system**: Slug-based keys ready for multilingual content
- **Advanced analytics**: Stats table extensible for additional metrics
- **Media collections**: Spatie Media Library supports unlimited file types and conversions

## Database Relationships

### Core Relationships

- **One-to-Many**: User → Shops (a user can own multiple shops)
- **One-to-One**: Location ↔ Shop (each location has exactly one shop, each shop has one unique location)
- **One-to-Many**: Shop → Products (a shop can have multiple products)
- **Many-to-Many**: Products ↔ AttributeValues (products can have multiple attribute values)
- **Polymorphic**: Spatie Media → (Shops, Products) with automatic WebP conversions

### Hierarchical Location Structure

- **Governorate → Cities**: One-to-Many (governorates contain multiple cities)
- **City → Locations**: One-to-Many (cities contain multiple user-generated locations)
- **Location → Shop**: One-to-One (each location is unique to one shop)

### Business Logic Implications

- **Unique Shop Locations**: Each shop must have its own unique location record with specific GPS coordinates
- **Shared Areas**: Multiple shops can be in the same area/neighborhood, but each has distinct coordinates
- **Location Ownership**: Locations are tied to shops and cannot be shared between shops
- **Scalable Geography**: The hierarchical structure supports expansion to multiple governorates and cities

### 8. **Query Performance Patterns**

```sql
-- Hierarchical location-based shop discovery
SELECT s.*, l.area, c.name as city_name, g.name as governorate_name
FROM shops s
JOIN locations l ON s.location_id = l.id
JOIN cities c ON l.city_id = c.id
JOIN governorates g ON c.governorate_id = g.id
WHERE c.slug = 'cairo' AND s.is_active = 1;

-- Multi-city expansion ready
SELECT s.*, l.area, c.name as city_name, g.name as governorate_name
FROM shops s
JOIN locations l ON s.location_id = l.id
JOIN cities c ON l.city_id = c.id
JOIN governorates g ON c.governorate_id = g.id
WHERE g.slug = 'cairo' AND s.is_active = 1;

-- Product search with hierarchical location context
SELECT p.*, s.name as shop_name, sc.name as subcategory,
       l.area, c.name as city_name, g.name as governorate_name
FROM products p
JOIN shops s ON p.shop_id = s.id
JOIN locations l ON s.location_id = l.id
JOIN cities c ON l.city_id = c.id
JOIN governorates g ON c.governorate_id = g.id
JOIN subcategories sc ON p.subcategory_id = sc.id
WHERE p.is_active = 1 AND s.is_active = 1 AND c.slug = 'cairo';

-- Popular products by stats with location
SELECT p.*, ps.views_count, ps.whatsapp_clicks,
       c.name as city_name, g.name as governorate_name
FROM products p
JOIN product_stats ps ON p.id = ps.product_id
JOIN shops s ON p.shop_id = s.id
JOIN locations l ON s.location_id = l.id
JOIN cities c ON l.city_id = c.id
JOIN governorates g ON c.governorate_id = g.id
ORDER BY ps.views_count DESC;
```

This schema provides a solid foundation for your local marketplace platform with room for growth and excellent query performance.
