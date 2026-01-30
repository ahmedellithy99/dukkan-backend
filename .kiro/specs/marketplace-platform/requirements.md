# Requirements Document

## Introduction

Dukkan Backend is a local marketplace platform API that connects customers with local shops for product discovery. The platform focuses on helping customers find products and contact shops directly via WhatsApp or SMS, rather than processing online transactions.

## Glossary

- **System**: The Dukkan Backend API
- **Vendor**: A user who owns and manages a shop on the platform
- **Customer**: An end user browsing products (frontend user)
- **Admin**: A platform administrator with full system access
- **Location**: A geographical area defined by city and area
- **Product**: An item listed by a shop for discovery
- **Category**: High-level product grouping (e.g., Clothes, Shoes)
- **Subcategory**: Specific product type within a category (e.g., T-Shirt, Sneakers)
- **Attribute**: Product property like Size, Color, Gender
- **Media**: Images attached to shops or products

## Requirements

### Requirement 1: User Management

**User Story:** As a vendor, I want to register and manage my account, so that I can list my products on the platform.

#### Acceptance Criteria

1. WHEN a vendor provides valid registration details, THE System SHALL create a new user account with vendor role
2. WHEN a vendor attempts to register with an existing email, THE System SHALL prevent registration and return an error
3. WHEN a vendor logs in with valid credentials, THE System SHALL authenticate them and provide access tokens
4. WHEN an admin manages users, THE System SHALL allow role assignment and status management
5. THE System SHALL hash and securely store all user passwords

### Requirement 2: Location Management

**User Story:** As a vendor, I want to create or select my shop's location, so that customers can find me geographically.

#### Acceptance Criteria

1. WHEN a vendor creates a shop, THE System SHALL allow them to create a new location or select from existing ones
2. WHEN a vendor creates a location, THE System SHALL store city, area, and GPS coordinates
3. THE System SHALL validate GPS coordinates are within acceptable ranges
4. WHEN locations are queried, THE System SHALL support filtering by city and area
5. THE System SHALL maintain location data for proximity-based searches

### Requirement 3: Shop Management

**User Story:** As a vendor, I want to create and manage my shop profile, so that customers can find and contact me.

#### Acceptance Criteria

1. WHEN a vendor creates a shop, THE System SHALL associate it with their user account and selected location
2. WHEN shop details are provided, THE System SHALL validate WhatsApp number format and store contact information
3. WHEN a vendor updates their shop, THE System SHALL maintain data integrity and update timestamps
4. THE System SHALL allow shop owners to activate or deactivate their shops
5. WHEN shops are queried, THE System SHALL filter by active status and location

### Requirement 4: Category System

**User Story:** As a platform administrator, I want to manage product categories, so that products can be organized hierarchically.

#### Acceptance Criteria

1. WHEN an admin creates a category, THE System SHALL generate a unique slug and store the category
2. WHEN an admin creates a subcategory, THE System SHALL associate it with a parent category
3. THE System SHALL prevent duplicate category or subcategory slugs
4. WHEN categories are deleted, THE System SHALL prevent deletion if products exist in subcategories
5. THE System SHALL support querying categories with their subcategories

### Requirement 5: Product Catalog

**User Story:** As a vendor, I want to list my products with details and attributes, so that customers can discover them.

#### Acceptance Criteria

1. WHEN a vendor creates a product, THE System SHALL associate it with their shop and selected subcategory
2. WHEN product details are provided, THE System SHALL validate and store name, description, and optional price
3. THE System SHALL allow products to be activated or deactivated
4. WHEN products are queried, THE System SHALL support filtering by shop, category, subcategory, and active status
5. THE System SHALL maintain product creation and update timestamps

### Requirement 6: Attribute System

**User Story:** As a vendor, I want to assign attributes to my products, so that customers can filter by specific properties.

#### Acceptance Criteria

1. WHEN attributes are created, THE System SHALL store reusable attribute names (Size, Color, Gender)
2. WHEN attribute values are created, THE System SHALL associate them with specific attributes
3. WHEN products are assigned attributes, THE System SHALL create many-to-many relationships via pivot table
4. THE System SHALL prevent duplicate attribute-value combinations
5. WHEN products are queried, THE System SHALL support filtering by multiple attribute values

### Requirement 7: Media Management

**User Story:** As a vendor, I want to upload images for my shop and products, so that customers can see visual representations.

#### Acceptance Criteria

1. WHEN media is uploaded, THE System SHALL store file paths and associate them with shops or products polymorphically
2. THE System SHALL support different media types (logo, banner, product_image)
3. WHEN multiple images are uploaded, THE System SHALL maintain display order
4. THE System SHALL validate file types and sizes before storage
5. WHEN entities are deleted, THE System SHALL handle associated media cleanup

### Requirement 8: Analytics Tracking

**User Story:** As a vendor, I want to track product performance, so that I can understand customer interest.

#### Acceptance Criteria

1. WHEN a product is viewed, THE System SHALL increment the views counter
2. WHEN a WhatsApp contact link is clicked, THE System SHALL track the interaction
3. WHEN products are favorited, THE System SHALL maintain favorite counts
4. THE System SHALL update last_viewed_at timestamps for products
5. THE System SHALL provide analytics data via API endpoints

### Requirement 9: Search and Discovery

**User Story:** As a customer, I want to search and filter products, so that I can find items I'm interested in.

#### Acceptance Criteria

1. WHEN customers search by text, THE System SHALL match product names and descriptions
2. WHEN customers filter by category, THE System SHALL return products from selected categories and subcategories
3. WHEN customers filter by attributes, THE System SHALL return products matching selected attribute values
4. THE System SHALL support combining multiple filters and sorting options

### Requirement 10: API Response Format

**User Story:** As a frontend developer, I want consistent API responses, so that I can reliably integrate with the backend.

#### Acceptance Criteria

1. THE System SHALL return JSON responses for all API endpoints
2. WHEN API requests succeed, THE System SHALL return appropriate HTTP status codes (200, 201)
3. WHEN API requests fail, THE System SHALL return error responses with descriptive messages
4. THE System SHALL include pagination metadata for list endpoints
5. THE System SHALL format timestamps consistently across all responses

### Requirement 11: Data Validation

**User Story:** As a platform administrator, I want data integrity, so that the system maintains clean and valid information.

#### Acceptance Criteria

1. WHEN data is submitted, THE System SHALL validate required fields are present
2. WHEN email addresses are provided, THE System SHALL validate format and uniqueness
3. WHEN GPS coordinates are submitted, THE System SHALL validate they are within valid ranges
4. WHEN foreign key relationships are created, THE System SHALL ensure referenced records exist
5. THE System SHALL sanitize input data to prevent security vulnerabilities
