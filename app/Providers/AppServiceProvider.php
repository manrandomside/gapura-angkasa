<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
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
        try {
            // Existing Vite configuration
            Vite::prefetch(concurrency: 3);
            
            // ENHANCED: Register Employee Observer for automatic masa kerja calculation
            // dengan error handling dan logging untuk debugging
            if (class_exists(Employee::class)) {
                if (class_exists(EmployeeObserver::class)) {
                    Employee::observe(EmployeeObserver::class);
                    
                    // Log successful observer registration untuk debugging
                    Log::info('AppServiceProvider: EmployeeObserver successfully registered', [
                        'observer_class' => EmployeeObserver::class,
                        'model_class' => Employee::class,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } else {
                    // Log error jika EmployeeObserver class tidak ditemukan
                    Log::error('AppServiceProvider: EmployeeObserver class not found', [
                        'expected_class' => EmployeeObserver::class,
                        'model_class' => Employee::class,
                        'suggestion' => 'Please ensure EmployeeObserver.php exists in app/Observers/'
                    ]);
                }
            } else {
                // Log error jika Employee model tidak ditemukan
                Log::error('AppServiceProvider: Employee model not found', [
                    'expected_class' => Employee::class,
                    'suggestion' => 'Please ensure Employee.php exists in app/Models/'
                ]);
            }
            
            // ENHANCED: Additional configuration untuk masa kerja calculation
            $this->configureMasaKerjaCalculation();
            
        } catch (\Exception $e) {
            // Log comprehensive error untuk troubleshooting
            Log::error('AppServiceProvider: Error during bootstrap process', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw exception agar aplikasi aware ada masalah
            throw $e;
        }
    }
    
    /**
     * Configure masa kerja calculation settings
     * ENHANCED: Additional configuration untuk masa kerja functionality
     */
    private function configureMasaKerjaCalculation(): void
    {
        try {
            // Set timezone default untuk aplikasi (WITA)
            if (config('app.timezone') !== 'Asia/Makassar') {
                Log::info('AppServiceProvider: Timezone configuration check', [
                    'current_timezone' => config('app.timezone'),
                    'recommended_timezone' => 'Asia/Makassar',
                    'note' => 'Consider updating timezone in config/app.php for WITA support'
                ]);
            }
            
            // Verify Observer methods exist untuk debugging
            if (class_exists(EmployeeObserver::class)) {
                $observerMethods = get_class_methods(EmployeeObserver::class);
                $requiredMethods = ['creating', 'updating'];
                
                $missingMethods = array_diff($requiredMethods, $observerMethods);
                
                if (empty($missingMethods)) {
                    Log::info('AppServiceProvider: EmployeeObserver methods verified', [
                        'available_methods' => $observerMethods,
                        'required_methods' => $requiredMethods,
                        'status' => 'All required methods found'
                    ]);
                } else {
                    Log::warning('AppServiceProvider: EmployeeObserver missing methods', [
                        'available_methods' => $observerMethods,
                        'required_methods' => $requiredMethods,
                        'missing_methods' => $missingMethods,
                        'suggestion' => 'Add missing methods to EmployeeObserver'
                    ]);
                }
            }
            
            Log::info('AppServiceProvider: Masa kerja configuration completed successfully');
            
        } catch (\Exception $e) {
            Log::error('AppServiceProvider: Error in configureMasaKerjaCalculation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * ENHANCED: Debug method untuk troubleshooting (dapat dipanggil dari controller jika diperlukan)
     */
    public static function debugObserverStatus(): array
    {
        return [
            'employee_class_exists' => class_exists(Employee::class),
            'observer_class_exists' => class_exists(EmployeeObserver::class),
            'observer_methods' => class_exists(EmployeeObserver::class) ? get_class_methods(EmployeeObserver::class) : [],
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'timestamp' => now()->toDateTimeString()
        ];
    }
}