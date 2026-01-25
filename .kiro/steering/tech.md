# Technology Stack

## Backend Framework
- **Laravel 12.x** - PHP web application framework
- **PHP 8.2+** - Required minimum PHP version

## Database
- **MySQL** - Primary database for production
- **SQLite** - Used for testing (in-memory)
- **Eloquent ORM** - Laravel's database abstraction layer

## API Architecture
- **RESTful API** - Clean API endpoints for frontend consumption
- **JSON responses** - Standardized API responses
- **Authentication** - Laravel Sanctum for API token authentication
- **Validation** - Form request validation for data integrity

## Performance & Scalability
- **Database Indexing** - Optimized indexes for location-based queries
- **Eager Loading** - Prevent N+1 queries with proper relationships
- **Caching** - Redis/database caching for frequently accessed data
- **Queue System** - Background job processing for heavy operations

## Media Management
- **File Storage** - Laravel filesystem for image uploads
- **Image Optimization** - Intervention Image for resizing/optimization
- **Polymorphic Relations** - Flexible media attachment system

## Development Tools
- **Laravel Pint** - PHP code style fixer
- **Laravel Sail** - Docker development environment
- **Laravel Tinker** - Interactive REPL
- **Faker** - Test data generation for shops, products, locations
- **Mockery** - PHP mocking framework

## Testing
- **PHPUnit 11.x** - PHP testing framework
- **Feature Tests** - API endpoint testing
- **Unit Tests** - Model and service testing
- **Database Testing** - In-memory SQLite for fast tests

## Common Commands

### Setup
```bash
composer run setup          # Full project setup
composer install           # Install PHP dependencies
php artisan key:generate    # Generate application key
php artisan migrate         # Run database migrations
php artisan db:seed         # Seed with sample data
```

### Development
```bash
php artisan serve          # Start PHP development server
php artisan queue:work     # Process background jobs
php artisan tinker         # Interactive shell for testing
```

### Database Management
```bash
php artisan migrate        # Run new migrations
php artisan migrate:fresh --seed  # Fresh database with sample data
php artisan make:migration create_table_name  # Create new migration
php artisan make:model ModelName -m  # Create model with migration
```

### API Development
```bash
php artisan make:controller ApiController --api  # Create API controller
php artisan make:request StoreProductRequest     # Create form request
php artisan make:resource ProductResource        # Create API resource
php artisan route:list     # List all API routes
```

### Testing
```bash
php artisan test           # Run all tests
php artisan test --filter=ProductTest  # Run specific test
php artisan make:test ProductApiTest --feature  # Create feature test
```

### Code Quality
```bash
./vendor/bin/pint          # Fix code style issues
php artisan config:clear   # Clear configuration cache
php artisan route:clear    # Clear route cache
php artisan optimize       # Optimize for production
```

### Data Management
```bash
php artisan make:seeder ShopSeeder     # Create database seeder
php artisan make:factory ProductFactory # Create model factory
php artisan storage:link   # Link storage for public file access
```