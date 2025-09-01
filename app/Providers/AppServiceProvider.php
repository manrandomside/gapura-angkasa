<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Employee;
use App\Observers\EmployeeObserver;

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
        // Existing Vite configuration
        Vite::prefetch(concurrency: 3);
        
        // Register Employee Observer for automatic masa kerja calculation
        Employee::observe(EmployeeObserver::class);
    }
}