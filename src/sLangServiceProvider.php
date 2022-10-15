<?php namespace Seiger\sLang;

use EvolutionCMS\ServiceProvider;
use Event;

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

            // For use config
            $this->publishes([
                dirname(__DIR__) . '/config/sLangAlias.php' => config_path('app/aliases/sLang.php', true),
            ]);
        }

        $this->app->singleton(sLang::class);
        $this->app->alias(sLang::class, 'sLang');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Only Manager
        if (IN_MANAGER_MODE) {
            // Add plugins to Evo
            $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');

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
            $this->app->registerModule($lang['slang'], dirname(__DIR__) . '/modules/sLangModule.php', $lang['slang_icon']);
        }
    }
}