# Query Filter Pattern Implementation

## Overview
The Query Filter Pattern provides a clean, reusable way to handle complex filtering logic for your marketplace platform. This pattern is essential for product discovery, location-based searches, and attribute filtering.

## Pattern Structure

### Base Filter Class
```php
<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Request $request;
    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter) && $this->hasValue($value)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    protected function getFilters(): array
    {
        return $this->request->all();
    }

    protected function hasValue($value): bool
    {
        return $value !== null && $value !== '' && $value !== [];
    }
}
```

### Product Filter Implementation
```php
<?php

namespace App\Filters;

class ProductFilter extends BaseFilter
{
    /**
     * Filter by category slug
     */
    public function category($slug)
    {
        $this->builder->whereHas('subcategory.category', function ($query) use ($slug) {
            $query->where('slug', $slug);
        });
    }

    /**
     * Filter by subcategory slug
     */
    public function subcategory($slug)
    {
        $this->builder->whereHas('subcategory', function ($query) use ($slug) {
            $query->where('slug', $slug);
        });
    }

    /**
     * Filter by price range
     */
    public function price_min($price)
    {
        $this->builder->where('price', '>=', $price);
    }

    public function price_max($price)
    {
        $this->builder->where('price', '<=', $price);
    }

    /**
     * Filter by location (city)
     */
    public function city($city)
    {
        $this->builder->whereHas('shop.location', function ($query) use ($city) {
            $query->where('city', $city);
        });
    }

    /**
     * Filter by area
     */
    public function area($area)
    {
        $this->builder->whereHas('shop.location', function ($query) use ($area) {
            $query->where('area', $area);
        });
    }

    /**
     * Filter by shop
     */
    public function shop($shopId)
    {
        $this->builder->where('shop_id', $shopId);
    }

    /**
     * Filter by attributes (size, color, etc.)
     */
    public function attributes($attributes)
    {
        if (!is_array($attributes)) {
            return;
        }

        foreach ($attributes as $attributeName => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            $this->builder->whereHas('attributeValues', function ($query) use ($attributeName, $values) {
                $query->whereHas('attribute', function ($attrQuery) use ($attributeName) {
                    $attrQuery->where('name', $attributeName);
                })->whereIn('value', $values);
            });
        }
    }

    /**
     * Search by product name
     */
    public function search($term)
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Filter by active status
     */
    public function active($status = true)
    {
        $this->builder->where('is_active', $status);
    }

    /**
     * Sort by popularity (views)
     */
    public function sort_by($sortBy)
    {
        switch ($sortBy) {
            case 'popular':
                $this->builder->leftJoin('product_stats', 'products.id', '=', 'product_stats.product_id')
                             ->orderByDesc('product_stats.views_count');
                break;
            case 'price_low':
                $this->builder->orderBy('price', 'asc');
                break;
            case 'price_high':
                $this->builder->orderByDesc('price');
                break;
            case 'newest':
                $this->builder->orderByDesc('created_at');
                break;
            default:
                $this->builder->orderByDesc('created_at');
        }
    }

    /**
     * Filter by proximity (requires coordinates)
     */
    public function near($coordinates)
    {
        if (!isset($coordinates['lat']) || !isset($coordinates['lng'])) {
            return;
        }

        $lat = $coordinates['lat'];
        $lng = $coordinates['lng'];
        $radius = $coordinates['radius'] ?? 10; // Default 10km

        $this->builder->whereHas('shop.location', function ($query) use ($lat, $lng, $radius) {
            $query->selectRaw("
                *, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) AS distance
            ", [$lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');
        });
    }
}
```

### Shop Filter Implementation
```php
<?php

namespace App\Filters;

class ShopFilter extends BaseFilter
{
    /**
     * Filter by city
     */
    public function city($city)
    {
        $this->builder->whereHas('location', function ($query) use ($city) {
            $query->where('city', $city);
        });
    }

    /**
     * Filter by area
     */
    public function area($area)
    {
        $this->builder->whereHas('location', function ($query) use ($area) {
            $query->where('area', $area);
        });
    }

    /**
     * Search by shop name
     */
    public function search($term)
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Filter by active status
     */
    public function active($status = true)
    {
        $this->builder->where('is_active', $status);
    }

    /**
     * Filter shops with products in specific category
     */
    public function has_category($categorySlug)
    {
        $this->builder->whereHas('products.subcategory.category', function ($query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        });
    }

    /**
     * Filter by proximity
     */
    public function near($coordinates)
    {
        if (!isset($coordinates['lat']) || !isset($coordinates['lng'])) {
            return;
        }

        $lat = $coordinates['lat'];
        $lng = $coordinates['lng'];
        $radius = $coordinates['radius'] ?? 10;

        $this->builder->whereHas('location', function ($query) use ($lat, $lng, $radius) {
            $query->selectRaw("
                *, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) AS distance
            ", [$lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');
        });
    }
}
```

## Model Integration

### Filterable Trait
```php
<?php

namespace App\Traits;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, BaseFilter $filter): Builder
    {
        return $filter->apply($query);
    }
}
```

### Model Implementation
```php
<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Filterable;

    // ... model code
}
```

## Controller Usage

### API Controller Example
```php
<?php

namespace App\Http\Controllers\Api;

use App\Filters\ProductFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['shop.location', 'subcategory.category', 'media'])
            ->filter(new ProductFilter($request))
            ->paginate(20);

        return ProductResource::collection($products);
    }
}
```

## API Usage Examples

### Basic Filtering
```
GET /api/products?category=clothes&city=cairo&active=1
```

### Advanced Filtering
```
GET /api/products?category=clothes&subcategory=t-shirt&price_min=100&price_max=500&sort_by=popular
```

### Attribute Filtering
```
GET /api/products?attributes[size][]=M&attributes[size][]=L&attributes[color][]=red
```

### Location-based Search
```
GET /api/products?near[lat]=30.0444&near[lng]=31.2357&near[radius]=5
```

### Search with Multiple Filters
```
GET /api/products?search=nike&category=shoes&city=cairo&sort_by=price_low
```

## Performance Considerations

### 1. **Eager Loading**
```php
Product::with(['shop.location', 'subcategory.category', 'attributeValues.attribute'])
    ->filter(new ProductFilter($request))
    ->paginate(20);
```

### 2. **Index Optimization**
Ensure proper indexes exist for filtered columns:
```sql
-- For location-based queries
INDEX idx_locations_coordinates (latitude, longitude)

-- For category filtering
INDEX idx_subcategories_category (category_id)

-- For attribute filtering
INDEX idx_product_attribute_values_composite (product_id, attribute_value_id)
```

### 3. **Query Caching**
```php
public function index(Request $request)
{
    $cacheKey = 'products_' . md5(serialize($request->all()));
    
    $products = Cache::remember($cacheKey, 300, function () use ($request) {
        return Product::filter(new ProductFilter($request))->paginate(20);
    });

    return ProductResource::collection($products);
}
```

## Benefits

1. **Reusable**: Filter classes can be used across different controllers
2. **Testable**: Each filter method can be unit tested independently
3. **Maintainable**: Complex query logic is organized and easy to modify
4. **Performant**: Optimized queries with proper indexing
5. **Flexible**: Easy to add new filters without modifying existing code
6. **Clean Controllers**: Controllers remain thin and focused

This pattern is perfect for your marketplace platform where users need to filter products by location, category, price, attributes, and other criteria efficiently.