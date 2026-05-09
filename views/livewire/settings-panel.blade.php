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
        </section>
    </div>
</form>

<style>
    .slang-settings {
        display: grid;
        gap: 14px;
    }

    .slang-settings__heading {
        min-height: 34px;
    }

    .slang-settings__toolbar {
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 0;
        overflow: visible;
    }

    .slang-settings__saved {
        color: var(--evo-ui-success);
        font-size: 12px;
        font-weight: 650;
        white-space: nowrap;
    }

    .slang-settings__section {
        display: grid;
        gap: 12px;
        padding: 2px 0;
    }

    .slang-settings__section-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--evo-ui-text);
        font-weight: 650;
    }

    .slang-settings__section-title svg {
        width: 18px;
        height: 18px;
        color: var(--evo-ui-muted);
    }

    .slang-settings__row {
        display: grid;
        grid-template-columns: minmax(200px, 260px) minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .slang-settings__control--compound {
        display: grid;
        grid-template-columns: minmax(220px, 300px) max-content;
        gap: 14px;
        align-items: center;
    }

    .slang-settings__url-toggle {
        min-height: 30px;
        margin: 0;
        white-space: nowrap;
    }

    .slang-settings__label {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        justify-self: end;
        gap: 6px;
        min-height: 34px;
        color: var(--evo-ui-muted);
        font-size: 13px;
        font-weight: 650;
        text-align: right;
    }

    .slang-settings__control,
    .slang-settings__choices {
        min-width: 0;
    }

    .slang-settings__choices .evo-ui-choices__control {
        cursor: pointer;
    }

    .slang-settings__segments {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(118px, 150px));
        gap: 8px;
    }

    .slang-settings__segment {
        display: grid;
        gap: 4px;
    }

    .slang-settings__segment span {
        color: var(--evo-ui-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .slang-settings__segment .evo-ui-input {
        min-height: 30px;
        padding-inline: 9px;
    }

    @media (max-width: 760px) {
        .slang-settings__row,
        .slang-settings__control--compound {
            grid-template-columns: 1fr;
        }

        .slang-settings__url-toggle {
            justify-self: start;
        }

        .slang-settings__label {
            justify-content: flex-start;
            justify-self: start;
            text-align: left;
        }
    }
</style>
