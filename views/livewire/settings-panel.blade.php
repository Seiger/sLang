@php
    $defaultLanguage = (string) ($data['s_lang_default'] ?? '');
    $siteLanguageValues = array_map('strval', (array) ($data['s_lang_config'] ?? []));
    $frontendLanguageValues = array_map('strval', (array) ($data['s_lang_front'] ?? []));
    $tvValues = array_map('intval', (array) ($data['s_lang_tvs'] ?? []));
    $lockedSiteLanguages = collect($selectedSiteLanguages)
        ->map(fn (array $option) => $option + ['locked' => (string) ($option['value'] ?? '') === $defaultLanguage])
        ->all();
    $lockedFrontendLanguages = collect($selectedFrontendLanguages)
        ->map(fn (array $option) => $option + ['locked' => (string) ($option['value'] ?? '') === $defaultLanguage])
        ->all();
    $labelHelp = function (string $text, ?string $help = null) {
        return '<span>' . e($text) . '</span>' . ($help ? '<span class="evo-ui-field__help" title="' . e($help) . '" aria-label="' . e($help) . '" data-tooltip="' . e($help) . '" data-evo-tooltip="' . e($help) . '" tabindex="0">?</span>' : '');
    };
@endphp

<form
    class="evo-ui-form slang-settings"
    wire:submit.prevent="save"
    data-evo-form
    data-evo-form-dirty="{{ $dirty ? 'true' : 'false' }}"
>
    <div class="evo-ui-form-heading slang-settings__heading">
        <div>
            <h2>
                <x-evo::icon name="settings" />
                <span>@lang('global.settings_config')</span>
            </h2>
        </div>

        <div class="evo-ui-form-toolbar slang-settings__toolbar" aria-label="@lang('evo::global.form_actions')">
            @if($saved)
                <span class="slang-settings__saved">@lang('evo::global.form_saved')</span>
            @endif
            <x-evo::button
                icon="check"
                :label="__('evo::global.action_save')"
                tone="primary"
                variant="filled"
                type="submit"
                :disabled="!$dirty"
                wire:loading.attr="disabled"
                wire:target="save"
            />
        </div>
    </div>

    <div class="slang-settings__body">
        <section class="slang-settings__section slang-settings__section--main">
            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.lang_def'), __('sLang::global.lang_def_help')) !!}</span>
                <div class="slang-settings__control slang-settings__control--compound">
                    <select class="evo-ui-input" wire:model.live="data.s_lang_default">
                        @foreach($languages as $language)
                            <option value="{{ $language['value'] }}">{{ $language['label'] }}</option>
                        @endforeach
                    </select>
                    <label class="evo-ui-check slang-settings__url-toggle">
                        <input type="checkbox" wire:model.live="data.s_lang_default_show">
                        <span>@lang('sLang::global.use_url')</span>
                    </label>
                    @error('data.s_lang_default') <span class="evo-ui-field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.languages'), __('sLang::global.languages_help')) !!}</span>
                <div class="slang-settings__control">
                    <x-evo::choices
                        class="slang-settings__choices"
                        field="s_lang_config"
                        :options="$languages"
                        :selected-options="$lockedSiteLanguages"
                        :selected-values="$siteLanguageValues"
                        :placeholder="__('sLang::global.select_lang')"
                        :search-placeholder="__('sLang::global.select_lang')"
                    />
                    @error('data.s_lang_config') <span class="evo-ui-field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.lang_front'), __('sLang::global.lang_front_help')) !!}</span>
                <div class="slang-settings__control">
                    <x-evo::choices
                        class="slang-settings__choices"
                        field="s_lang_front"
                        :options="$frontendLanguages"
                        :selected-options="$lockedFrontendLanguages"
                        :selected-values="$frontendLanguageValues"
                        :placeholder="__('sLang::global.select_lang')"
                        :search-placeholder="__('sLang::global.select_lang')"
                    />
                    @error('data.s_lang_front') <span class="evo-ui-field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.lang_folders'), __('sLang::global.lang_folders_help')) !!}</span>
                <div class="slang-settings__control">
                    <div class="slang-settings__segments">
                        @foreach($siteLanguageValues as $locale)
                            <label class="slang-settings__segment">
                                <span>{{ strtoupper($locale) }}</span>
                                <input class="evo-ui-input" type="text" wire:model.live="data.s_lang_url_map.{{ $locale }}" placeholder="{{ $locale }}">
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.multilang_tvs'), __('sLang::global.multilang_tvs_help')) !!}</span>
                <div class="slang-settings__control">
                    <x-evo::choices
                        class="slang-settings__choices"
                        toggle-method="toggleTv"
                        remove-method="removeTv"
                        value-type="int"
                        :options="$templateVariableOptions"
                        :selected-options="$selectedTemplateVariables"
                        :selected-values="$tvValues"
                        :placeholder="__('sLang::global.select_multilang_tvs')"
                        :search-placeholder="__('sLang::global.select_multilang_tvs')"
                    />
                </div>
            </div>

            <div class="slang-settings__row">
                <span class="slang-settings__label">{!! $labelHelp(__('sLang::global.cleanup_obsolete'), __('sLang::global.cleanup_obsolete_help')) !!}</span>
                <div class="slang-settings__control">
                    <div class="slang-settings__maintenance">
                        <div class="slang-settings__maintenance-copy">
                            <strong>{{ trans_choice('sLang::global.cleanup_obsolete_count', $obsoleteTranslationCount, ['count' => $obsoleteTranslationCount]) }}</strong>
                            @if($obsoleteTranslationCount > 0)
                                <span>{{ implode(', ', $obsoleteTranslationSample) }}@if($obsoleteTranslationCount > count($obsoleteTranslationSample)), ...@endif</span>
                            @else
                                <span>@lang('sLang::global.cleanup_obsolete_empty')</span>
                            @endif
                            @if($cleanedObsoleteTranslations !== null)
                                <em>{{ trans_choice('sLang::global.cleanup_obsolete_done', $cleanedObsoleteTranslations, ['count' => $cleanedObsoleteTranslations]) }}</em>
                            @endif
                        </div>
                        <button
                            type="button"
                            class="evo-ui-btn evo-ui-btn--danger"
                            wire:click="cleanupObsoleteTranslations"
                            wire:confirm="@lang('sLang::global.cleanup_obsolete_confirm')"
                            wire:loading.attr="disabled"
                            wire:target="cleanupObsoleteTranslations"
                            @disabled($obsoleteTranslationCount === 0)
                        >
                            <x-evo::icon name="trash" class="evo-ui-btn__icon" />
                            <span>@lang('sLang::global.cleanup_obsolete_action')</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</form>
