<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Location;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Policies\Admin\AttributePolicy;
use App\Policies\Admin\AttributeValuePolicy;
use App\Policies\Admin\CategoryPolicy;
use App\Policies\Admin\SubcategoryPolicy;
use App\Policies\Vendor\LocationPolicy;
use App\Policies\Vendor\ShopPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Location::class => LocationPolicy::class,
        Shop::class => ShopPolicy::class,
        Category::class => CategoryPolicy::class,
        Subcategory::class => SubcategoryPolicy::class,
        Attribute::class => AttributePolicy::class,
        AttributeValue::class => AttributeValuePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
