<?php

declare(strict_types=1);

foreach ([
    'EVO_BASE_PATH' => dirname(__DIR__, 3) . '/sArticles/demo/',
    'EVO_MANAGER_PATH' => dirname(__DIR__, 3) . '/sArticles/demo/manager/',
    'EVO_SITE_URL' => 'http://127.0.0.1/',
] as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

foreach ([
    'DB' => Illuminate\Support\Facades\DB::class,
    'Event' => Illuminate\Support\Facades\Event::class,
    'Route' => Illuminate\Support\Facades\Route::class,
    'View' => Illuminate\Support\Facades\View::class,
] as $alias => $class) {
    if (!class_exists($alias, false) && class_exists($class)) {
        class_alias($class, $alias);
    }
}
