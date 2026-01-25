# Design Document

## Overview

The Dukkan Backend is a RESTful API built with Laravel 12.x that serves as the backend for a local marketplace platform. The system enables product discovery and direct communication between customers and shop owners via WhatsApp/SMS, without handling online transactions.

The architecture follows clean API design principles with a normalized database schema optimized for location-based queries and product filtering. The system is designed to scale from a single city to multiple cities while maintaining performance.

## Architecture

### API-First Design
- **RESTful endpoints** following Laravel conventions
- **JSON-only responses** with consistent formatting
- **Token-based authentication** using Laravel Sanctum
- **Request validation** through Form Request classes
- **Resource transformations** for consistent API responses

### Database Architecture
- **MySQL primary database** with optimized indexing
- **Normalized schema** to eliminate data redundancy
- **Foreign key constraints** for data integrity
- **Polymorphic relationships** for flexible media attachments
- **Separate analytics tables** for performance optimization

### Scalability Patterns
- **Location-based partitioning** ready for multi-city expansion
- **Query optimization** with proper indexing strategies
- **Eager loading** to prevent N+1 query problems
- **Caching layers** for frequently accessed data
- **Queue system** for background processing

## Components and Interfaces

### Authentication System
```php
// User authentication and authorization
POST /api/auth/register    // Shop owner registration
POST /api/auth/login       // User authentication
POST /api/auth/logout      // Token invalidation
GET  /api/auth/me          // Current user profile
```

### Location Management
```php
// Location-based queries
GET /api/locations         // List all locations
GET /api/locations/{id}    // Get specific location
POST /api/locations        // Create location (shop owners can create)
PUT /api/locations/{id}    // Update location (creator only)
```

### Shop Management
```php
// Shop CRUD operations
GET /api/shops             // List shops with filtering
GET /api/shops/{id}        // Get shop details
POST /api/shops            // Create shop (shop owner)
PUT /api/shops/{id}        // Update shop (owner only)
DELETE /api/shops/{id}     // Delete shop (owner only)
```

### Product Catalog
```php
// Product discovery and management
GET /api/products          // List products with advanced filtering
GET /api/products/{id}     // Get product details
POST /api/products         // Create product (shop owner)
PUT /api/products/{id}     // Update product (owner only)
DELETE /api/products/{id}  // Delete product (owner only)
```

### Category System
```php
// Hierarchical category management
GET /api/categories        // List categories with subcategories
GET /api/categories/{id}   // Get category details
POST /api/categories       // Create category (admin only)
PUT /api/categories/{id}   // Update category (admin only)
```

### Media Management
```php
// File upload and management
POST /api/media            // Upload media files
GET /api/media/{id}        // Get media details
DELETE /api/media/{id}     // Delete media (owner only)
```

### Analytics Endpoints
```php
// Performance tracking
POST /api/products/{id}/view      // Track product view
POST /api/products/{id}/whatsapp  // Track WhatsApp click
POST /api/products/{id}/favorite  // Track favorite action
GET /api/analytics/products       // Get product analytics
```

## Data Models

### Core Entities

#### User Model
```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'status'];
    
    // Relationships
    public function shops() { return $this->hasMany(Shop::class, 'owner_id'); }
    
    // Scopes
    public function scopeShopOwners($query) { return $query->where('role', 'shop_owner'); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
```

#### Location Model
```php
class Location extends Model
{
    protected $fillable = ['city', 'area', 'latitude', 'longitude'];
    
    // Relationships
    public function shops() { return $this->hasMany(Shop::class); }
    
    // Scopes
    public function scopeByCity($query, $city) { return $query->where('city', $city); }
    public function scopeNearby($query, $lat, $lng, $radius) { /* proximity query */ }
}
```

#### Location Model
```php
class Location extends Model
{
    protected $fillable = ['city', 'area', 'latitude', 'longitude'];
    
    // Relationships
    public function shops() { return $this->hasMany(Shop::class); }
    
    // Scopes
    public function scopeByCity($query, $city) { return $query->where('city', $city); }
    public function scopeNearby($query, $lat, $lng, $radius) { /* proximity query */ }
}
```

#### Shop Model
```php
class Shop extends Model
{
    protected $fillable = ['owner_id', 'location_id', 'name', 'description', 'whatsapp_number', 'phone_number', 'is_active'];
    
    // Relationships
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function location() { return $this->belongsTo(Location::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function media() { return $this->morphMany(Media::class, 'model'); }
    
    // Scopes
    public function scopeActive($query) { return $query->where('is_active', true); }
}
```

#### Product Model
```php
class Product extends Model
{
    protected $fillable = ['shop_id', 'subcategory_id', 'name', 'description', 'price', 'is_active'];
    
    // Relationships
    public function shop() { return $this->belongsTo(Shop::class); }
    public function subcategory() { return $this->belongsTo(Subcategory::class); }
    public function attributeValues() { return $this->belongsToMany(AttributeValue::class, 'product_attribute_values'); }
    public function media() { return $this->morphMany(Media::class, 'model'); }
    public function stats() { return $this->hasOne(ProductStats::class); }
    
    // Scopes
    public function scopeActive($query) { return $query->where('is_active', true); }
}
```

### Query Filter Integration
The system implements the Query Filter Pattern for complex product discovery:

```php
// Usage in ProductController
public function index(Request $request)
{
    $products = Product::with(['shop.location', 'subcategory.category', 'media'])
        ->filter(new ProductFilter($request))
        ->paginate(20);
        
    return ProductResource::collection($products);
}
```

### Database Relationships
- **Users → Shops**: One-to-Many (shop ownership)
- **Locations → Shops**: One-to-Many (geographical association)
- **Shops → Products**: One-to-Many (product ownership)
- **Categories → Subcategories**: One-to-Many (hierarchical structure)
- **Products ↔ AttributeValues**: Many-to-Many (flexible attributes)
- **Media**: Polymorphic (attachable to shops and products)

## Error Handling

### API Error Responses
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "phone": ["The phone format is invalid."]
    },
    "code": "VALIDATION_ERROR"
}
```

### Exception Handling Strategy
- **Validation errors**: Return 422 with detailed field errors
- **Authentication errors**: Return 401 with clear messages
- **Authorization errors**: Return 403 with permission context
- **Not found errors**: Return 404 with resource context
- **Server errors**: Return 500 with logged error IDs

### Input Sanitization
- **XSS prevention**: HTML entity encoding for text inputs
- **SQL injection prevention**: Eloquent ORM parameterized queries
- **File upload validation**: Type, size, and content validation
- **Rate limiting**: API endpoint throttling

## Testing Strategy

### Dual Testing Approach
The system employs both unit testing and property-based testing for comprehensive coverage:

**Unit Tests:**
- Specific examples and edge cases
- Integration points between components
- Error condition handling
- Authentication and authorization flows

**Property-Based Tests:**
- Universal properties across all inputs
- Minimum 100 iterations per test
- Comprehensive input coverage through randomization
- Each test references design document properties

### Test Configuration
- **PHPUnit 11.x** for test execution
- **SQLite in-memory** database for fast testing
- **Factory classes** for test data generation
- **Feature tests** for API endpoint validation
- **Unit tests** for model and service logic

Property tests will be tagged with format:
**Feature: marketplace-platform, Property {number}: {property_text}**

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: User Registration and Authentication
*For any* valid user registration data, creating a user account should result in a user with the correct role, hashed password, and unique email constraint enforcement
**Validates: Requirements 1.1, 1.2, 1.5**

### Property 2: Authentication Token Management  
*For any* valid user credentials, authentication should provide access tokens, and invalid credentials should be rejected
**Validates: Requirements 1.3**

### Property 3: Location Management Integrity
*For any* location data with valid GPS coordinates, the system should store the location and allow shop owners to create locations
**Validates: Requirements 2.1, 2.2, 2.3**

### Property 4: Location-Based Queries
*For any* location filtering criteria, the system should return accurate results and support proximity-based searches
**Validates: Requirements 2.4, 2.5**

### Property 5: Shop Creation and Management
*For any* shop owner and valid shop data, creating a shop should properly associate it with the owner and selected location, with correct validation
**Validates: Requirements 3.1, 3.2, 3.3**

### Property 6: Shop Status and Filtering
*For any* shop status change or query filter, the system should maintain status integrity and support location-based filtering
**Validates: Requirements 3.4, 3.5**

### Property 7: Category Hierarchy Management
*For any* category or subcategory creation, the system should generate unique slugs and maintain proper hierarchical relationships
**Validates: Requirements 4.1, 4.2, 4.3**

### Property 8: Category Referential Integrity
*For any* category deletion attempt, the system should prevent deletion when products exist and support proper relationship loading
**Validates: Requirements 4.4, 4.5**

### Property 9: Product Creation and Management
*For any* product data from a shop owner, the system should create products with proper relationships and validate all required fields
**Validates: Requirements 5.1, 5.2, 5.5**

### Property 10: Product Status and Filtering
*For any* product status change or complex filtering request, the system should maintain status integrity and support multi-criteria filtering
**Validates: Requirements 5.3, 5.4**

### Property 11: Attribute System Management
*For any* attribute and attribute value creation, the system should maintain reusable attributes and prevent duplicate combinations
**Validates: Requirements 6.1, 6.2, 6.4**

### Property 12: Product Attribute Relationships
*For any* product-attribute assignment, the system should create proper many-to-many relationships and support attribute-based filtering
**Validates: Requirements 6.3, 6.5**

### Property 13: Media Management and Polymorphism
*For any* media upload, the system should store files correctly, maintain polymorphic relationships, and validate file types
**Validates: Requirements 7.1, 7.2, 7.4**

### Property 14: Media Ordering and Cleanup
*For any* multiple media uploads or entity deletion, the system should maintain display order and handle cascade cleanup
**Validates: Requirements 7.3, 7.5**

### Property 15: Analytics Tracking Accuracy
*For any* user interaction (view, WhatsApp click, favorite), the system should accurately track and increment counters
**Validates: Requirements 8.1, 8.2, 8.3**

### Property 16: Analytics Data Management
*For any* analytics query, the system should maintain accurate timestamps and provide data via API endpoints
**Validates: Requirements 8.4, 8.5**

### Property 17: Search and Discovery Functionality
*For any* search query or filter combination, the system should return accurate results matching text, location, category, and attribute criteria
**Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5**

### Property 18: API Response Consistency
*For any* API request, the system should return consistent JSON responses with appropriate HTTP status codes and error handling
**Validates: Requirements 10.1, 10.2, 10.3**

### Property 19: API Pagination and Formatting
*For any* list endpoint request, the system should include pagination metadata and maintain consistent timestamp formatting
**Validates: Requirements 10.4, 10.5**

### Property 20: Data Validation and Security
*For any* data submission, the system should validate required fields, email formats, GPS coordinates, and sanitize input for security
**Validates: Requirements 11.1, 11.2, 11.3, 11.5**

### Property 21: Referential Integrity
*For any* foreign key relationship creation, the system should ensure referenced records exist and maintain database integrity
**Validates: Requirements 11.4**