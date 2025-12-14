<?php

declare(strict_types=1);

namespace Princeminky\Promptable\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PromptableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load views for this package under the unique "promptable" namespace
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'promptable');

        // Register a Blade directive so @promptable() renders the view
        Blade::directive('promptable', function (): string {
            return "<?php echo view('promptable::prompt')->render(); ?>";
        });

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/promptable'),
        ], 'promptable-views');
    }
}
