<?php

namespace Modules\CyberFranco\Providers;

use App\Models\PdfRequestVerification;
use App\Observers\PdfRequestVerificationObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Routing\Router;
use App\Http\Middleware\PdfRequestHashUuid;
use App\Http\Middleware\ValidateApiFrancoRequest;
use Modules\CyberFranco\Console\AddFrancoBackpackRoute;

class FrancoServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerMiddlewares();
        $this->registerEvents();
        $this->registerFactories();
        $this->registercommands();
        $this->loadMigrationsFrom(module_path('CyberFranco', 'Database/Migrations'));
        $this->francoPublish();
    }



    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('CyberFranco', 'Config/config.php') => config_path('pdf_request.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('CyberFranco', 'Config/config.php'), 'pdf_request'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/franco');

        $sourcePath = module_path('CyberFranco', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/franco';
        }, \Config::get('view.paths')), [$sourcePath]), 'franco');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/franco');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'franco');
        } else {
            $this->loadTranslationsFrom(module_path('CyberFranco', 'Resources/lang'), 'franco');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
//        if (! app()->environment('production') && $this->app->runningInConsole()) {
//            app(Factory::class)->load(module_path('CyberFranco', 'Database/factories'));
//        }
    }


    public function francoPublish() {

        //Publishing and overwriting app folders
        $this->publishes([
            __DIR__ . '/../app/Models' => app_path('Models'),
            __DIR__ . '/../app/Policies' => app_path('Policies'),
            __DIR__ . '/../app/Services' => app_path('Services'),
            __DIR__ . '/../app/Notifications' => app_path('Notifications'),
            __DIR__ . '/../app/Observers' => app_path('Observers'),
            __DIR__ . '/../app/Http/Controllers' => app_path('Http/Controllers'),
            __DIR__ . '/../app/Http/Controllers/Admin' => app_path('Http/Controllers/Admin'),
            __DIR__ . '/../app/Http/Middleware' => app_path('Http/Middleware'),
            __DIR__ . '/../app/Http/Requests' => app_path('Http/Requests'),
        ], 'app');

        //Publishing and overwriting public folders
//        $this->publishes([
//            __DIR__ . '/../public/admin/assets/css' => public_path('admin/assets/css'),
//            __DIR__ . '/../public/admin/pages' => public_path('admin/pages'),
//        ], 'public');
    }


    public function registerMiddlewares() {

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('pdf-uuid', PdfRequestHashUuid::class);
        $router->aliasMiddleware('validate-api-franco', ValidateApiFrancoRequest::class);

    }

    public function registerEvents() {

        if (class_exists(PdfRequestVerification::class)) {
            PdfRequestVerification::observe(PdfRequestVerificationObserver::class);
        }

    }

    public function registerCommands() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddFrancoBackpackRoute::class,
            ]);
        }
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
