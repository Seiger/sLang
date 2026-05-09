@props([
    'lang',
])

@php($label = __('sLang::global.auto_translate') . ' ' . strtoupper(sLang::langDefault()) . ' => ' . strtoupper($lang))

<button
    type="button"
    class="evo-ui-btn evo-ui-btn--icon evo-ui-btn--primary slang-resource-translate"
    data-lang="{{ $lang }}"
    data-slang-translate="1"
    title="{{ $label }}"
    aria-label="{{ $label }}"
>
    <x-evo::icon name="language" class="evo-ui-btn__icon" />
    <span class="evo-ui-sr-only">{{ $label }}</span>
</button>
