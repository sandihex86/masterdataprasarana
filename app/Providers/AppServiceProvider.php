<?php

namespace App\Providers;

use App\Models\ImportMapping;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\PersonalAccessToken;
use App\Policies\ImportMappingPolicy;
use App\Policies\MasterDataPolicy;
use App\Policies\MasterDataTypePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $applicationUrl = (string) config('app.url');
        $applicationScheme = parse_url($applicationUrl, PHP_URL_SCHEME);

        if ($applicationUrl !== '') {
            URL::forceRootUrl($applicationUrl);
        }

        if (is_string($applicationScheme) && $applicationScheme !== '') {
            URL::forceScheme($applicationScheme);
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Gate::policy(MasterData::class, MasterDataPolicy::class);
        Gate::policy(MasterDataType::class, MasterDataTypePolicy::class);
        Gate::policy(ImportMapping::class, ImportMappingPolicy::class);
    }
}
