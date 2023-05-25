<?php

namespace Momenoor\FormBuilder;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class FormBuilderServiceProvider extends ServiceProvider
{

    const NAME = 'FormBuilder';
    const FIELD = 'Field';
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', static::NAME);
        $this->loadTranslationsFrom(__DIR__ . '/lang', static::NAME);
    }

    public function register()
    {
        $this->commands(Console\Commands\FormMakerCommand::class);

        $this->mergeConfigFrom(__DIR__ . '/config/form-builder.php', static::NAME);


        $this->app->bind(static::NAME, function () {
            return new FormBuilder();
        });

        $this->alias();
    }

    private function alias()
    {
        $this->registerAliasIfNotExists(static::NAME, Facades\FormBuilder::class);
        $this->registerAliasIfNotExists('Request', \Illuminate\Support\Facades\Request::class);
        $this->registerAliasIfNotExists('Route', \Illuminate\Support\Facades\Route::class);
        $this->registerAliasIfNotExists('File', \Illuminate\Support\Facades\File::class);
        $this->registerAliasIfNotExists('Redirect', \Illuminate\Support\Facades\Redirect::class);
    }

    private function registerAliasIfNotExists($alias, $class)
    {
        if (!array_key_exists($alias, AliasLoader::getInstance()->getAliases())) {
            AliasLoader::getInstance()->alias($alias, $class);
        }
    }
}
