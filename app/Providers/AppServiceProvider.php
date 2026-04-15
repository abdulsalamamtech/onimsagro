<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Api documentation token
        // Scramble::configure()
        //     ->withDocumentTransformers(function (OpenApi $openApi) {
        //         $openApi->secure(
        //             SecurityScheme::http('bearer')
        //         );
        //     });


        // View scrambled API docs only for authenticated users
        Gate::define('viewApiDocs', function (?User $user) {
            // Option A: Allow all logged-in users
            return auth()->check() ?? true;

            // Option B: Allow only specific email (Safer)
            // return in_array($user->email, ['admin@example.com']);
        });
        // Scrample API Docs Token
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
        ]);
        // Pulse authorization
        // Gate::define('viewPulse', function (User $user) {
        //     // return $user->isAdmin();
        // });
    }
}
