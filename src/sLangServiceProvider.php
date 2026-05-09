<?php namespace Seiger\sLang;

use EvolutionCMS\ServiceProvider;
use Event;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

class sLangServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Only Manager
        if (IN_MANAGER_MODE) {
            // Add custom routes for package
            include(__DIR__.'/Http/routes.php');

            // Migration for create tables
            $this->loadMigrationsFrom(dirname(__DIR__) . '/database/migrations');

            // Views
            $this->loadViewsFrom(dirname(__DIR__) . '/views', 'sLang');

            // MultiLang
            $this->loadTranslationsFrom(dirname(__DIR__) . '/lang', 'sLang');

            $this->mergeConfigFrom(dirname(__DIR__) . '/config/translates/table.php', 'slang.translates.table');
            Livewire::component('slang.module-panel', \Seiger\sLang\Livewire\ModulePanel::class);
            Livewire::component('slang.settings-panel', \Seiger\sLang\Livewire\SettingsPanel::class);

            // For use config
            $this->publishes([
                dirname(__DIR__) . '/config/sLangAlias.php' => config_path('app/aliases/sLang.php', true),
                dirname(__DIR__) . '/images/seigerit-blue.svg' => public_path('assets/site/seigerit-blue.svg'),
            ]);
        }

        // Check sLang
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/sLangCheck.php', 'cms.settings');

        // Class alias
        $this->app->singleton(sLang::class);
        $this->app->alias(sLang::class, 'sLang');

        Paginator::currentPathResolver(function () {
            return $this->app->make(sLang::class)->resolveCurrentPath();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Add plugins to Evo
        $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');

        // Only Manager
        if (IN_MANAGER_MODE) {
            // Add module to Evo. Module ID is md5('sLangModule').
            $lang = 'en';
            if (isset($_SESSION['mgrUsrConfigSet']['manager_language'])) {
                $lang = $_SESSION['mgrUsrConfigSet']['manager_language'];
            } else {
                if (is_file(evo()->getSiteCacheFilePath())) {
                    $siteCache = file_get_contents(evo()->getSiteCacheFilePath());
                    preg_match('@\$c\[\'manager_language\'\]="\w+@i', $siteCache, $matches);
                    if (count($matches)) {
                        $lang = str_replace('$c[\'manager_language\']="', '', $matches[0]);
                    }
                }
            }
            $lang = include_once dirname(__DIR__) . '/lang/' . $lang . '/global.php';
            $this->app->registerModule(
                $lang['module_title'] ?? $lang['slang'],
                dirname(__DIR__) . '/modules/sLangModule.php',
                $lang['module_icon'] ?? $lang['slang_icon']
            );
        }
    }
}
