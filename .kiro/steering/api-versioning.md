# API Versioning Strategy for Dukkan Backend

## Overview

The Dukkan Backend implements a comprehensive API versioning strategy to ensure backward compatibility, smooth migrations, and sustainable long-term development. This strategy is crucial for a marketplace platform that will evolve with business needs while maintaining stability for existing integrations.

## Versioning Approach

### URI Versioning (Primary)

- **Format**: `/api/v{major}/resource`
- **Examples**: `/api/v1/shops`, `/api/v2/products`
- **Benefits**: Clear, explicit, cacheable, easy to route

### Content Negotiation (Secondary)

- **Header**: `Accept: application/vnd.dukkan.v1+json`
- **Fallback**: When URI version not specified
- **Benefits**: Cleaner URLs for documentation

## Version Lifecycle

### Semantic Versioning

- **Major (v1, v2)**: Breaking changes, new architecture
- **Minor (v1.1, v1.2)**: New features, backward compatible
- **Patch (v1.1.1)**: Bug fixes, security updates

### Support Policy

- **Active Support**: Latest 2 major versions
- **Security Updates**: Latest 3 major versions
- **Deprecation Notice**: 6 months before retirement
- **End of Life**: Clear communication and migration guides

## Implementation Strategy

### Phase 1: Current (v1.0.0)

- All endpoints under `/api/v1/`
- Core marketplace functionality
- Shop and product management
- Basic authentication and authorization

### Phase 2: Enhanced Features (v1.1.0)

- Advanced search and filtering
- Analytics and reporting
- Media management improvements
- Performance optimizations

### Phase 3: Multi-city Support (v2.0.0)

- Breaking changes for multi-city architecture
- Enhanced location management
- Subscription management
- Advanced admin features

## Breaking vs Non-Breaking Changes

### Breaking Changes (Major Version)

- Removing endpoints or fields
- Changing response structure
- Modifying authentication methods
- Changing HTTP status codes
- Renaming resources or parameters

### Non-Breaking Changes (Minor/Patch)

- Adding new endpoints
- Adding optional fields to requests
- Adding fields to responses
- Bug fixes and performance improvements
- New optional query parameters

## API Response Format

### Versioned Response Structure

```json
{
    "api_version": "v1.0.0",
    "success": true,
    "data": {
        // Resource data
    },
    "meta": {
        "pagination": {},
        "version_info": {
            "current": "v1.0.0",
            "latest": "v1.1.0",
            "deprecated": false,
            "sunset_date": null
        }
    }
}
```

### Error Response Format

```json
{
    "api_version": "v1.0.0",
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "fields": {
            "email": ["Email is required"]
        }
    },
    "meta": {
        "version_info": {
            "current": "v1.0.0",
            "latest": "v1.1.0"
        }
    }
}
```

## Route Organization

### Directory Structure

```
routes/
├── api.php              # Version routing and middleware
├── api/
│   ├── v1/
│   │   ├── auth.php     # Authentication routes
│   │   ├── shops.php    # Shop management
│   │   ├── products.php # Product catalog
│   │   └── locations.php # Location services
│   └── v2/
│       ├── auth.php     # Enhanced authentication
│       ├── shops.php    # Multi-city shops
│       └── products.php # Advanced product features
```

### Route Registration

```php
// routes/api.php
Route::prefix('api')->group(function () {
    // v1 routes
    Route::prefix('v1')->group(function () {
        require __DIR__ . '/api/v1/auth.php';
        require __DIR__ . '/api/v1/shops.php';
        require __DIR__ . '/api/v1/products.php';
        require __DIR__ . '/api/v1/locations.php';
    });

    // v2 routes (future)
    Route::prefix('v2')->group(function () {
        require __DIR__ . '/api/v2/auth.php';
        require __DIR__ . '/api/v2/shops.php';
        require __DIR__ . '/api/v2/products.php';
    });
});
```

## Controller Organization

### Versioned Controllers

```
app/Http/Controllers/Api/
├── V1/
│   ├── AuthController.php
│   ├── ShopController.php
│   ├── ProductController.php
│   └── LocationController.php
└── V2/
    ├── AuthController.php
    ├── ShopController.php
    └── ProductController.php
```

### Shared Logic

- **Services**: Version-agnostic business logic
- **Models**: Shared across versions
- **Resources**: Version-specific response formatting
- **Requests**: Version-specific validation

## Migration Strategy

### Deprecation Process

1. **Announce**: 6 months notice via API headers and documentation
2. **Warn**: Add deprecation warnings to responses
3. **Migrate**: Provide migration guides and tools
4. **Sunset**: Remove deprecated version

### Client Communication

- **API Headers**: `X-API-Deprecation-Warning`
- **Documentation**: Version-specific migration guides
- **Email Notifications**: For registered API consumers
- **Status Page**: Version lifecycle information

## Documentation Strategy

### Version-Specific Docs

- Separate documentation for each major version
- Clear migration guides between versions
- Changelog with breaking/non-breaking changes
- Code examples for each version

### API Discovery

- Version information in API responses
- Health check endpoints with version info
- OpenAPI/Swagger specs for each version

## Testing Strategy

### Version Testing

- Separate test suites for each major version
- Backward compatibility tests
- Migration testing between versions
- Performance regression testing

### Continuous Integration

- Automated testing for all supported versions
- Breaking change detection
- API contract testing
- Documentation generation

## Monitoring and Analytics

### Version Usage Tracking

- Monitor API version usage patterns
- Track migration progress
- Identify deprecated version usage
- Performance metrics per version

### Alerts and Notifications

- High usage of deprecated versions
- Breaking change deployments
- Version sunset reminders
- Performance degradation alerts

## Best Practices

### Development Guidelines

1. **Design for Evolution**: Anticipate future changes
2. **Additive Changes**: Prefer adding over modifying
3. **Graceful Degradation**: Handle missing features elegantly
4. **Clear Contracts**: Document all API behaviors
5. **Version Everything**: Include version in all responses

### Client Guidelines

1. **Explicit Versioning**: Always specify API version
2. **Handle Deprecation**: Monitor deprecation warnings
3. **Test Migrations**: Validate new versions thoroughly
4. **Graceful Fallbacks**: Handle version unavailability
5. **Stay Updated**: Follow version lifecycle communications

This versioning strategy ensures the Dukkan Backend can evolve sustainably while maintaining reliability for all stakeholders in the marketplace ecosystem.
