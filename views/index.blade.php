@php
    $manager = app(\EvoUI\Support\ManagerContext::class);
    $theme = $manager->theme();
    $themeMode = $manager->themeMode($theme);
    $themeClasses = $manager->themeClasses($theme);
    $moduleTitle = __('sLang::global.module_title') !== 'sLang::global.module_title' ? __('sLang::global.module_title') : __('sLang::global.slang');
    $assetPath = 'core/vendor/seiger/slang/assets/';
    $assetDirectory = rtrim(EVO_BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $assetPath);
    $assetUrl = rtrim(EVO_SITE_URL, '/') . '/' . $assetPath;
    $managerCssVersion = is_file($assetDirectory . 'css/manager.css') ? filemtime($assetDirectory . 'css/manager.css') : time();
    $managerJsVersion = is_file($assetDirectory . 'js/manager.js') ? filemtime($assetDirectory . 'js/manager.js') : time();
@endphp

@include('evo::partials.assets')
<link rel="stylesheet" href="{{ $assetUrl }}css/manager.css?v={{ $managerCssVersion }}">
<script src="{{ $assetUrl }}js/manager.js?v={{ $managerJsVersion }}" defer></script>

<div
    class="evo-ui {{ $themeClasses }}"
    data-evo-ui-root
    data-theme="{{ $theme }}"
    data-theme-mode="{{ $themeMode }}"
    data-slang-module-title="{{ $moduleTitle }}"
>
    <livewire:slang.module-panel
        :tabs="$tabs"
        :active-tab="$get"
        :context="['moduleUrl' => $moduleUrl]"
    />
</div>
