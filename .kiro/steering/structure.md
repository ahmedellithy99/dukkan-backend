# Project Structure

## Root Directory
- `.env` / `.env.example` - Environment configuration
- `composer.json` - PHP dependencies and scripts
- `artisan` - Laravel command-line interface
- `phpunit.xml` - PHPUnit testing configuration

## Application Code (`app/`)
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/         # API controllers for frontend
│   │   └── Controller.php
│   ├── Requests/        # Form request validation
│   ├── Resources/       # API response resources
│   └── Middleware/      # Custom middleware
├── Models/             # Eloquent models
│   ├── User.php        # Shop owners and admins
│   ├── Shop.php
│   ├── Product.php
│   ├── Category.php
│   ├── Location.php
│   └── Media.php
├── Filters/            # Query filter classes
│   ├── BaseFilter.php  # Abstract base filter
│   ├── ProductFilter.php
│   ├── ShopFilter.php
│   └── LocationFilter.php
├── Services/           # Business logic services
└── Providers/          # Service providers
```

## Language Files (`lang/`)
```
lang/
├── en/                 # English (default)
│   ├── auth.php        # Authentication messages
│   ├── pagination.php  # Pagination messages
│   ├── passwords.php   # Password reset messages
│   ├── validation.php  # Validation messages
│   ├── api.php         # API response messages
│   ├── categories.php  # Category names
│   ├── attributes.php  # Attribute names
│   └── general.php     # General app messages
└── ar/                 # Arabic
    ├── auth.php
    ├── pagination.php
    ├── passwords.php
    ├── validation.php
    ├── api.php
    ├── categories.php
    ├── attributes.php
    └── general.php
```

## Database Schema (`database/`)
```
database/
├── migrations/         # Database schema migrations
│   ├── create_users_table.php
│   ├── create_locations_table.php
│   ├── create_shops_table.php
│   ├── create_categories_table.php
│   ├── create_subcategories_table.php
│   ├── create_products_table.php
│   ├── create_attributes_table.php
│   ├── create_attribute_values_table.php
│   ├── create_product_attribute_values_table.php
│   ├── create_media_table.php
│   └── create_product_stats_table.php
├── factories/          # Model factories for testing
└── seeders/           # Database seeders
```

## API Routes (`routes/`)
```
routes/
├── api.php            # API endpoints
│   ├── /api/shops     # Shop management
│   ├── /api/products  # Product catalog
│   ├── /api/categories # Category browsing
│   └── /api/locations # Location-based queries
└── console.php        # Artisan commands
```

## Testing (`tests/`)
```
tests/
├── Feature/           # API endpoint tests
│   ├── ShopApiTest.php
│   ├── ProductApiTest.php
│   └── LocationApiTest.php
├── Unit/             # Model and service tests
│   ├── ShopTest.php
│   └── ProductTest.php
└── TestCase.php      # Base test class
```

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
- Polymorphic media attachments
- Support for shop logos, product images
- Optimized for mobile display

### Analytics
- Product view tracking
- WhatsApp click analytics
- Favorite counts

## Naming Conventions

### API Endpoints
- RESTful conventions: `/api/shops`, `/api/products`
- Resource-based routing with proper HTTP verbs
- Consistent JSON response format

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
- **Polymorphic**: Media → (Shops, Products)

## Architecture Patterns

### Query Filter Pattern
- **Reusable filters**: Encapsulate complex query logic in dedicated filter classes
- **Chainable filters**: Combine multiple filters for complex searches
- **Request-based filtering**: Automatically apply filters from request parameters
- **Performance optimized**: Efficient query building with proper indexing

### Clean API Design
- Consistent JSON responses
- Proper HTTP status codes
- Resource-based endpoints
- Request validation

### Database Optimization
- Proper indexing for location queries
- Normalized schema design
- Efficient relationship loading
- Performance-aware queries

### Scalability Considerations
- Location-based partitioning ready
- Translation table structure prepared
- Analytics data separation
- Caching strategy implementation