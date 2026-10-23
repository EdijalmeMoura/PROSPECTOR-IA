<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Em desenvolvimento, falha cedo em lazy loading e atribuições inseguras.
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);

        Paginator::useBootstrapFive();

        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
