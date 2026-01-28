# Project Structure

## Root Directory

- `.env` / `.env.example` - Environment configuration
- `composer.json` - PHP dependencies and scripts
- `artisan` - Laravel command-line interface
- `phpunit.xml` - PHPUnit testing configuration

## Application Code (`app/`)

### Current Structure (MVP - No Versioning Yet)

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Controller.php    # Base controller only
│   ├── Middleware/           # Custom middleware
│   │   └── DetectCityFromSubdomain.php
├── Models/                   # Eloquent models (✅ Implemented)
│   ├── User.php             # Shop owners and admins
│   ├── Governorate.php      # Egyptian governorates
│   ├── City.php             # Cities within governorates
│   ├── Location.php         # User locations with GPS
│   ├── Shop.php             # Shops with Spatie Media Library
│   ├── Product.php          # Products with Spatie Media Library
│   ├── Category.php         # Product categories
│   ├── Subcategory.php      # Product subcategories
│   ├── Attribute.php        # Product attributes (Color, Size, etc.)
│   ├── AttributeValue.php   # Attribute values (Red, Large, etc.)
│   └── ProductStats.php     # Analytics tracking
└── Providers/               # Service providers
```

### Planned Versioned Structure (🚧 To Be Implemented)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── V1/          # Version 1 controllers (🚧 TODO)
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ShopController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   └── LocationController.php
│   │   │   └── V2/          # Version 2 controllers (🚧 Future)
│   │   └── Controller.php
│   ├── Requests/            # Form request validation (🚧 TODO)
│   │   ├── V1/
│   │   │   ├── StoreShopRequest.php
│   │   │   ├── UpdateShopRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   └── UpdateProductRequest.php
│   │   └── V2/
│   ├── Resources/           # API response resources (🚧 TODO)
│   │   ├── V1/
│   │   │   ├── ShopResource.php
│   │   │   ├── ProductResource.php
│   │   │   ├── CategoryResource.php
│   │   │   └── LocationResource.php
│   │   └── V2/
│   └── Middleware/
├── Filters/                 # Query filter classes (🚧 TODO)
│   ├── BaseFilter.php       # Abstract base filter
│   ├── ProductFilter.php
│   ├── ShopFilter.php
│   └── LocationFilter.php
├── Services/                # Business logic services (🚧 TODO)
│   ├── ShopService.php
│   ├── ProductService.php
│   └── LocationService.php
├── Models/                  # ✅ Already implemented
└── Providers/
```

## Language Files (`lang/`)

### Current Structure (🚧 Basic Laravel Files Only)

```
lang/
├── en/                        # English (default)
│   ├── auth.php              # ✅ Laravel default
│   ├── pagination.php        # ✅ Laravel default
│   ├── passwords.php         # ✅ Laravel default
│   └── validation.php        # ✅ Laravel default
```

### Planned Translation Structure (🚧 To Be Implemented)

```
lang/
├── en/                        # English (default)
│   ├── auth.php              # ✅ Already exists
│   ├── pagination.php        # ✅ Already exists
│   ├── passwords.php         # ✅ Already exists
│   ├── validation.php        # ✅ Already exists
│   ├── api.php               # 🚧 TODO: API response messages
│   ├── categories.php        # 🚧 TODO: Category names
│   ├── attributes.php        # 🚧 TODO: Attribute names
│   ├── attribute_values.php  # 🚧 TODO: Attribute value names
│   └── general.php           # 🚧 TODO: General app messages
└── ar/                       # Arabic (🚧 TODO: Complete Arabic translation)
    ├── auth.php
    ├── pagination.php
    ├── passwords.php
    ├── validation.php
    ├── api.php
    ├── categories.php
    ├── attributes.php
    ├── attribute_values.php
    └── general.php
```

### Implementation Tasks Required:

- [ ] Create API response translation files
- [ ] Implement category/attribute translations
- [ ] Add Arabic translations for all files
- [ ] Create translation helper methods in models
- [ ] Add locale detection middleware

## Database Schema (`database/`)

### ✅ Current Implementation (Completed)

```
database/
├── migrations/                    # Database schema migrations
│   ├── 2026_01_25_161400_create_governorates_table.php
│   ├── 2026_01_25_161430_create_cities_table.php
│   ├── 2026_01_25_161454_create_locations_table.php
│   ├── 2026_01_25_161533_create_shops_table.php
│   ├── 2026_01_25_161603_create_categories_table.php
│   ├── 2026_01_25_161632_create_subcategories_table.php
│   ├── 2026_01_25_161700_create_products_table.php
│   ├── 2026_01_25_161733_create_attributes_table.php
│   ├── 2026_01_25_161806_create_attribute_values_table.php
│   ├── 2026_01_25_161833_create_product_attribute_values_table.php
│   ├── 2026_01_25_161932_create_product_stats_table.php
│   └── 2026_01_28_063719_create_media_table.php (Spatie Media Library)
├── factories/                     # Model factories for testing
│   ├── UserFactory.php           # ✅ Implemented
│   ├── LocationFactory.php       # ✅ Updated for hierarchical structure
│   ├── ShopFactory.php           # ✅ Updated with slug
│   ├── CategoryFactory.php       # ✅ Updated with unique slugs
│   ├── SubcategoryFactory.php    # ✅ Updated with unique slugs
│   └── ProductFactory.php        # ✅ Updated for discount structure
└── seeders/                      # Database seeders
    ├── DatabaseSeeder.php        # ✅ Updated
    ├── GovernorateSeeder.php     # ✅ 27 Egyptian governorates
    └── CitySeeder.php            # ✅ 22 major cities
```

## API Routes (`routes/`)

### Current Structure (🚧 No Versioning Yet)

```
routes/
├── api.php              # Basic Laravel routes (no versioning)
├── web.php              # Web routes
└── console.php          # Artisan commands
```

### Planned Versioned Structure (🚧 To Be Implemented)

```
routes/
├── api.php              # Main API routing with version middleware
├── api/                 # Versioned route files (🚧 TODO)
│   ├── v1/
│   │   ├── auth.php     # Authentication endpoints
│   │   ├── shops.php    # Shop management (CRUD)
│   │   ├── products.php # Product catalog (CRUD + search)
│   │   ├── categories.php # Category browsing
│   │   └── locations.php # Location-based queries
│   └── v2/              # Future version (🚧 Future)
│       ├── auth.php     # Enhanced authentication
│       ├── shops.php    # Multi-city shops
│       └── products.php # Advanced product features
├── web.php
└── console.php
```

### Implementation Tasks Required:

- [ ] Create `routes/api/` directory structure
- [ ] Split current routes into versioned files
- [ ] Update main `api.php` to use version routing
- [ ] Implement version middleware
- [ ] Add version detection logic

## Testing (`tests/`)

### Current Structure (✅ Models & Property Tests)

```
tests/
├── Feature/                    # Integration tests
│   └── ExampleTest.php        # Basic Laravel test
├── Unit/                      # Unit tests (✅ Implemented)
│   ├── ErisPropertyTest.php   # Property-based testing with Eris
│   ├── LocationManagementPropertyTest.php # ✅ Updated for hierarchical locations
│   ├── ProductDiscountTest.php # ✅ Updated for new discount structure
│   ├── UserRegistrationPropertyTest.php # ✅ User registration tests
│   └── ExampleTest.php
└── TestCase.php               # Base test class
```

### Planned API Testing Structure (🚧 To Be Implemented)

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── V1/                # Version 1 API tests (🚧 TODO)
│   │   │   ├── AuthApiTest.php
│   │   │   ├── ShopApiTest.php
│   │   │   ├── ProductApiTest.php
│   │   │   ├── CategoryApiTest.php
│   │   │   └── LocationApiTest.php
│   │   └── V2/                # Version 2 API tests (🚧 Future)
│   └── ExampleTest.php
├── Unit/                      # ✅ Already implemented
└── TestCase.php
```

### Implementation Tasks Required:

- [ ] Create versioned API test structure
- [ ] Implement API endpoint tests for V1
- [ ] Add version compatibility tests
- [ ] Create API contract tests
- [ ] Add performance regression tests

## Core Database Entities

### Users (Shop Owners)

- Authentication and authorization
- Shop ownership management
- Role-based access (shop_owner, admin)

### Locations

- City and area management
- GPS coordinates for mapping
- Scalable for multiple cities

### Shops

- Shop profiles and contact information
- Location association
- WhatsApp/SMS contact details

### Product Catalog

- **Categories**: High-level groups (Clothes, Shoes, Accessories)
- **Subcategories**: Specific types (T-Shirt, Sneakers, Bag)
- **Products**: Individual items with pricing
- **Attributes**: Flexible properties (Size, Color, Gender)

### Media Management

- Spatie Media Library for polymorphic media attachments
- Automatic WebP conversions for optimized delivery
- Support for shop logos, banners, ads, and product images
- Optimized for mobile display with multiple image sizes

### Analytics

- Product view tracking
- WhatsApp click analytics
- Favorite counts

## Naming Conventions

### API Endpoints

- **Versioned RESTful conventions**: `/api/v1/shops`, `/api/v1/products`
- **URI versioning strategy**: Major version in URL path
- **Resource-based routing** with proper HTTP verbs
- **Consistent JSON response format** across all versions
- **Backward compatibility** maintained for at least 2 major versions

### Database Tables

- Snake_case plural: `users`, `shops`, `products`
- Foreign keys: `shop_id`, `category_id`
- Pivot tables: `product_attribute_values`
- Timestamps: `created_at`, `updated_at`

### PHP Classes

- **Models**: PascalCase singular (`Shop`, `Product`)
- **Controllers**: `ShopController`, `ProductController`
- **Requests**: `StoreProductRequest`, `UpdateShopRequest`
- **Resources**: `ProductResource`, `ShopResource`

### Language Files

- **File names**: Snake_case (`categories.php`, `api.php`)
- **Translation keys**: Snake_case with dots (`api.success`, `categories.clothes`)
- **Locale codes**: `en` (English), `ar` (Arabic)
- **Nested arrays**: For organized translations

### Database Relationships

- **One-to-Many**: User → Shops, Shop → Products
- **Many-to-Many**: Products ↔ AttributeValues
- **Polymorphic**: Spatie Media → (Shops, Products) with automatic WebP conversions

## Implementation Roadmap

### ✅ Phase 1: Database & Models (COMPLETED)

- [x] Hierarchical location structure (Governorates → Cities → Locations)
- [x] All Eloquent models with relationships
- [x] Database migrations and seeders
- [x] Model factories for testing
- [x] Property-based tests for data integrity
- [x] Spatie Media Library integration
- [x] Translation-ready slugs

### 🚧 Phase 2: API Versioning Implementation (CURRENT PRIORITY)

- [ ] **Create versioned route structure** (`routes/api/v1/`)
- [ ] **Implement V1 controllers** (`app/Http/Controllers/Api/V1/`)
- [ ] **Create API resources** (`app/Http/Resources/V1/`)
- [ ] **Add form request validation** (`app/Http/Requests/V1/`)
- [ ] **Version detection middleware**
- [ ] **API response formatting with version info**
- [ ] **Basic authentication setup (Sanctum)**

### 🚧 Phase 3: Core API Endpoints (NEXT)

- [ ] Authentication endpoints (`/api/v1/auth`)
- [ ] Shop management (`/api/v1/shops`)
- [ ] Product catalog (`/api/v1/products`)
- [ ] Category browsing (`/api/v1/categories`)
- [ ] Location services (`/api/v1/locations`)
- [ ] Media upload endpoints
- [ ] Search and filtering

### 🚧 Phase 4: Advanced Features (FUTURE)

- [ ] Query filter classes
- [ ] Business logic services
- [ ] Translation system implementation
- [ ] Advanced analytics
- [ ] Performance optimizations
- [ ] Comprehensive API testing

### 🚧 Phase 5: Multi-city Support (V2.0.0)

- [ ] Enhanced location management
- [ ] Subscription management
- [ ] Admin dashboard features
- [ ] Advanced search capabilities
