# Local Marketplace Platform - Database Schema & ERD

## Entity Relationship Diagram

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     USERS       │    │   LOCATIONS     │    │     SHOPS       │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ name            │    │ city            │    │ owner_id (FK)   │──┐
│ email (UNIQUE)  │    │ area            │    │ location_id(FK) │──┼──┐
│ phone           │    │ latitude        │    │ name            │  │  │
│ password        │    │ longitude       │    │ description     │  │  │
│ role            │    │ created_at      │    │ whatsapp_number │  │  │
│ status          │    │ updated_at      │    │ phone_number    │  │  │
│ created_at      │    └─────────────────┘    │ is_active       │  │  │
│ updated_at      │                           │ created_at      │  │  │
└─────────────────┘                           │ updated_at      │  │  │
         │                                    └─────────────────┘  │  │
         └──────────────────────────────────────────────────────────┘  │
                                                                        │
                  ┌─────────────────────────────────────────────────────┘
                  │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   CATEGORIES    │    │ SUBCATEGORIES   │    │    PRODUCTS     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ name            │    │ category_id(FK) │──┐ │ shop_id (FK)    │──┘
│ slug (UNIQUE)   │    │ name            │  │ │ subcategory_id  │──┐
│ created_at      │    │ slug (UNIQUE)   │  │ │ name            │  │
│ updated_at      │    │ created_at      │  │ │ description     │  │
└─────────────────┘    │ updated_at      │  │ │ price           │  │
         │              └─────────────────┘  │ │ is_active       │  │
         └─────────────────────────────────────┘ │ created_at      │  │
                                                │ updated_at      │  │
                                                └─────────────────┘  │
                                                         │           │
                                                         │           │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐  │
│   ATTRIBUTES    │    │ATTRIBUTE_VALUES │    │PRODUCT_ATTRIBUTE│  │
├─────────────────┤    ├─────────────────┤    │    _VALUES      │  │
│ id (PK)         │    │ id (PK)         │    ├─────────────────┤  │
│ name            │    │ attribute_id(FK)│──┐ │ product_id (FK) │──┘
│ created_at      │    │ value           │  │ │ attribute_value │──┐
│ updated_at      │    │ created_at      │  │ │ _id (FK)        │  │
└─────────────────┘    │ updated_at      │  │ │ (Composite PK)  │  │
         │              └─────────────────┘  │ └─────────────────┘  │
         └─────────────────────────────────────┘                    │
                                                                    │
                                              ┌─────────────────────┘
                                              │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     MEDIA       │    │ PRODUCT_STATS   │    │                 │
├─────────────────┤    ├─────────────────┤    │   POLYMORPHIC   │
│ id (PK)         │    │ product_id (PK) │──┐ │   RELATIONS     │
│ model_type      │    │ views_count     │  │ │                 │
│ model_id        │    │ whatsapp_clicks │  │ │ Media can       │
│ type            │    │ favorites_count │  │ │ belong to:      │
│ path            │    │ last_viewed_at  │  │ │ - Shops         │
│ alt_text        │    │ updated_at      │  │ │ - Products      │
│ display_order   │    └─────────────────┘  │ │                 │
│ created_at      │                         │ └─────────────────┘
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
    role ENUM('shop_owner', 'admin') NOT NULL DEFAULT 'shop_owner',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- LOCATIONS TABLE
-- =============================================
CREATE TABLE locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_locations_city (city),
    INDEX idx_locations_area (area),
    INDEX idx_locations_coordinates (latitude, longitude),
    UNIQUE KEY unique_city_area (city, area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SHOPS TABLE
-- =============================================
CREATE TABLE shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    whatsapp_number VARCHAR(20) NOT NULL,
    phone_number VARCHAR(20),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE RESTRICT,
    
    INDEX idx_shops_owner (owner_id),
    INDEX idx_shops_location (location_id),
    INDEX idx_shops_active (is_active),
    INDEX idx_shops_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CATEGORIES TABLE
-- =============================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBCATEGORIES TABLE
-- =============================================
CREATE TABLE subcategories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    
    INDEX idx_subcategories_category (category_id),
    INDEX idx_subcategories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PRODUCTS TABLE
-- =============================================
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    subcategory_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE RESTRICT,
    
    INDEX idx_products_shop (shop_id),
    INDEX idx_products_subcategory (subcategory_id),
    INDEX idx_products_active (is_active),
    INDEX idx_products_name (name),
    INDEX idx_products_price (price),
    INDEX idx_products_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ATTRIBUTES TABLE
-- =============================================
CREATE TABLE attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_attributes_name (name)
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
    
    INDEX idx_attribute_values_attribute (attribute_id),
    INDEX idx_attribute_values_value (value),
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
    
    INDEX idx_pav_product (product_id),
    INDEX idx_pav_attribute_value (attribute_value_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- MEDIA TABLE (Polymorphic)
-- =============================================
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    type ENUM('logo', 'banner', 'product_image', 'ad') NOT NULL,
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    display_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_media_model (model_type, model_id),
    INDEX idx_media_type (type),
    INDEX idx_media_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PRODUCT_STATS TABLE
-- =============================================
CREATE TABLE product_stats (
    product_id BIGINT UNSIGNED PRIMARY KEY,
    views_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    whatsapp_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    favorites_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    
    INDEX idx_product_stats_views (views_count),
    INDEX idx_product_stats_whatsapp (whatsapp_clicks),
    INDEX idx_product_stats_favorites (favorites_count),
    INDEX idx_product_stats_last_viewed (last_viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Key Design Decisions & Performance Optimizations

### 1. **Indexing Strategy**
- **Location-based queries**: Composite index on `(latitude, longitude)` for proximity searches
- **Search optimization**: Indexes on `name` fields for text searches
- **Filter optimization**: Indexes on `is_active`, `status`, `role` for common filters
- **Relationship optimization**: Foreign key indexes for efficient joins

### 2. **Data Types & Constraints**
- **Coordinates**: `DECIMAL(10,8)` and `DECIMAL(11,8)` for precise GPS coordinates
- **Price**: `DECIMAL(10,2)` for accurate monetary values
- **Phone numbers**: `VARCHAR(20)` to accommodate international formats
- **ENUMs**: Used for fixed value sets (role, status, media type)

### 3. **Scalability Considerations**
- **City expansion**: `locations` table ready for multiple cities
- **Translation ready**: Schema can accommodate translation tables later
- **Analytics separation**: `product_stats` separate for performance
- **Polymorphic media**: Flexible attachment system

### 4. **Data Integrity**
- **Cascade deletes**: User deletion removes shops and products
- **Restrict deletes**: Location/category deletion prevented if referenced
- **Unique constraints**: Email, slugs, and attribute-value combinations
- **Foreign key constraints**: Maintain referential integrity

### 5. **Future Expansion Hooks**
- **Multi-city**: Location table structure supports expansion
- **Translations**: Can add `*_translations` tables later
- **Advanced analytics**: Stats table can be extended
- **Media types**: ENUM can be expanded for new media types

### 6. **Query Performance Patterns**
```sql
-- Location-based shop discovery
SELECT s.*, l.city, l.area 
FROM shops s 
JOIN locations l ON s.location_id = l.id 
WHERE l.city = 'Cairo' AND s.is_active = 1;

-- Product search with attributes
SELECT p.*, s.name as shop_name, sc.name as subcategory 
FROM products p 
JOIN shops s ON p.shop_id = s.id 
JOIN subcategories sc ON p.subcategory_id = sc.id 
WHERE p.is_active = 1 AND s.is_active = 1;

-- Popular products by stats
SELECT p.*, ps.views_count, ps.whatsapp_clicks 
FROM products p 
JOIN product_stats ps ON p.id = ps.product_id 
ORDER BY ps.views_count DESC;
```

This schema provides a solid foundation for your local marketplace platform with room for growth and excellent query performance.