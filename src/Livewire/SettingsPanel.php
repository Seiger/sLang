<?php namespace Seiger\sLang\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;

class SettingsPanel extends Component
{
    public array $data = [];
    public array $cleanData = [];
    public bool $saved = false;
    public bool $dirty = false;
    public ?int $cleanedObsoleteTranslations = null;

    public function mount(): void
    {
        $this->fillData();
    }

    public function updatedData(mixed $value = null, ?string $key = null): void
    {
        if ($key === 's_lang_default') {
            $this->ensureDefaultLanguage();
        }

        $this->normalizeLanguageSelections();
        $this->saved = false;
        $this->cleanedObsoleteTranslations = null;
        $this->dirty = $this->snapshot($this->data) !== $this->snapshot($this->cleanData);
    }

    public function toggleChoice(string $field, string $value): void
    {
        if (!in_array($field, ['s_lang_config', 's_lang_front'], true)) {
            return;
        }

        $current = array_values(array_map('strval', (array) data_get($this->data, $field, [])));
        $default = (string) data_get($this->data, 's_lang_default', sLang::langDefault());

        if (in_array($value, $current, true)) {
            if ($value !== $default) {
                $current = array_values(array_diff($current, [$value]));
            }
        } else {
            $current[] = $value;
        }

        data_set($this->data, $field, array_values(array_unique($current)));
        $this->ensureDefaultLanguage();
        $this->normalizeLanguageSelections();
        $this->updatedData();
    }

    public function removeChoice(string $field, string $value): void
    {
        if (!in_array($field, ['s_lang_config', 's_lang_front'], true)) {
            return;
        }

        $default = (string) data_get($this->data, 's_lang_default', sLang::langDefault());
        if ($value === $default) {
            return;
        }

        $current = array_values(array_map('strval', (array) data_get($this->data, $field, [])));
        data_set($this->data, $field, array_values(array_diff($current, [$value])));
        $this->ensureDefaultLanguage();
        $this->normalizeLanguageSelections();
        $this->updatedData();
    }

    public function toggleTv(int $id): void
    {
        $current = array_values(array_map('intval', (array) data_get($this->data, 's_lang_tvs', [])));

        if (in_array($id, $current, true)) {
            $current = array_values(array_diff($current, [$id]));
        } else {
            $current[] = $id;
        }

        data_set($this->data, 's_lang_tvs', array_values(array_unique($current)));
        $this->updatedData();
    }

    public function removeTv(int $id): void
    {
        $current = array_values(array_map('intval', (array) data_get($this->data, 's_lang_tvs', [])));
        data_set($this->data, 's_lang_tvs', array_values(array_diff($current, [$id])));
        $this->updatedData();
    }

    public function save(): void
    {
        $this->saved = false;
        $languages = array_keys(sLang::langList());

        $validated = $this->validate([
            'data.s_lang_default' => ['required', 'string', 'in:' . implode(',', $languages)],
            'data.s_lang_default_show' => ['boolean'],
            'data.s_lang_config' => ['required', 'array', 'min:1'],
            'data.s_lang_config.*' => ['string', 'in:' . implode(',', $languages)],
            'data.s_lang_front' => ['required', 'array', 'min:1'],
            'data.s_lang_front.*' => ['string', 'in:' . implode(',', $languages)],
            'data.s_lang_url_map' => ['array'],
            'data.s_lang_tvs' => ['array'],
            'data.s_lang_tvs.*' => ['integer'],
        ]);

        $payload = $validated['data'];
        $default = (string) $payload['s_lang_default'];
        $config = array_values(array_unique((array) $payload['s_lang_config']));
        $front = array_values(array_unique((array) $payload['s_lang_front']));

        if (!in_array($default, $config, true)) {
            $config[] = $default;
        }

        $front = array_values(array_intersect($front, $config));
        if (!in_array($default, $front, true)) {
            $front[] = $default;
        }

        $controller = new sLangController();
        $controller->setLangDefault($default);
        $controller->setLangDefaultShow(!empty($payload['s_lang_default_show']) ? 1 : 0);
        $controller->setLangConfig($config);
        $controller->setLangFront($front);
        $controller->setLangUrlMap((array) ($payload['s_lang_url_map'] ?? []));
        $controller->setLangTvs((array) ($payload['s_lang_tvs'] ?? []));
        $controller->setModifyTables();
        $controller->setOnOffLangModule(1);

        $this->fillData();
        $this->saved = true;
        $this->dirty = false;
        $this->dispatch('evo-ui:form.saved', preset: 'slang.settings');
    }

    public function cleanupObsoleteTranslations(): void
    {
        $controller = new sLangController();
        $this->cleanedObsoleteTranslations = $controller->cleanupObsoleteTranslations();
        $this->saved = false;
    }

    public function render()
    {
        $obsoleteTranslationKeys = $this->obsoleteTranslationKeys();

        return view('sLang::livewire.settings-panel', [
            'languages' => $this->languageOptions(),
            'frontendLanguages' => $this->frontendLanguageOptions(),
            'templateVariables' => sLang::templateVariables(),
            'selectedSiteLanguages' => $this->selectedLanguageOptions('s_lang_config'),
            'selectedFrontendLanguages' => $this->selectedLanguageOptions('s_lang_front'),
            'selectedTemplateVariables' => $this->selectedTemplateVariableOptions(),
            'templateVariableOptions' => $this->templateVariableOptions(),
            'saved' => $this->saved,
            'dirty' => $this->dirty,
            'cleanedObsoleteTranslations' => $this->cleanedObsoleteTranslations,
            'obsoleteTranslationCount' => count($obsoleteTranslationKeys),
            'obsoleteTranslationSample' => array_slice($obsoleteTranslationKeys, 0, 5),
        ]);
    }

    protected function fillData(): void
    {
        $config = sLang::langConfig();
        $urlMap = [];

        foreach ($config as $locale) {
            $urlMap[$locale] = sLang::langSegment($locale);
        }

        $this->data = [
            's_lang_default' => sLang::langDefault(),
            's_lang_default_show' => sLang::defaultInUrl(),
            's_lang_config' => $config,
            's_lang_front' => sLang::langFront(),
            's_lang_url_map' => $urlMap,
            's_lang_tvs' => array_map('intval', sLang::langTvs()),
        ];
        $this->cleanData = $this->data;
    }

    protected function ensureDefaultLanguage(): void
    {
        $default = (string) data_get($this->data, 's_lang_default', sLang::langDefault());

        foreach (['s_lang_config', 's_lang_front'] as $field) {
            $values = array_values(array_map('strval', (array) data_get($this->data, $field, [])));
            if (!in_array($default, $values, true)) {
                array_unshift($values, $default);
            }
            data_set($this->data, $field, array_values(array_unique($values)));
        }
    }

    protected function normalizeLanguageSelections(): void
    {
        $default = (string) data_get($this->data, 's_lang_default', sLang::langDefault());
        $available = array_keys(sLang::langList());
        $site = array_values(array_intersect(
            array_map('strval', (array) data_get($this->data, 's_lang_config', [])),
            $available
        ));

        if (!in_array($default, $site, true)) {
            array_unshift($site, $default);
        }

        $site = array_values(array_unique($site));
        $front = array_values(array_intersect(
            array_map('strval', (array) data_get($this->data, 's_lang_front', [])),
            $site
        ));

        if (!in_array($default, $front, true)) {
            array_unshift($front, $default);
        }

        $urlMap = (array) data_get($this->data, 's_lang_url_map', []);
        $normalizedMap = [];
        foreach ($site as $locale) {
            $normalizedMap[$locale] = (string) ($urlMap[$locale] ?? sLang::langSegment($locale));
        }

        data_set($this->data, 's_lang_config', $site);
        data_set($this->data, 's_lang_front', array_values(array_unique($front)));
        data_set($this->data, 's_lang_url_map', $normalizedMap);
    }

    protected function languageOptions(): array
    {
        return collect(sLang::langList())
            ->map(fn (array $language, string $locale) => [
                'value' => $locale,
                'label' => ($language['name'] ?? Str::upper($locale)) . ' (' . __('sLang::global.lang_' . $locale) . ')',
            ])
            ->values()
            ->all();
    }

    protected function selectedLanguageOptions(string $field): array
    {
        $selected = array_map('strval', (array) data_get($this->data, $field, []));

        return collect($this->languageOptions())
            ->filter(fn (array $option) => in_array((string) $option['value'], $selected, true))
            ->values()
            ->all();
    }

    protected function frontendLanguageOptions(): array
    {
        $siteLanguages = array_map('strval', (array) data_get($this->data, 's_lang_config', []));

        return collect($this->languageOptions())
            ->filter(fn (array $option) => in_array((string) $option['value'], $siteLanguages, true))
            ->values()
            ->all();
    }

    protected function selectedTemplateVariableOptions(): array
    {
        $selected = array_map('intval', (array) data_get($this->data, 's_lang_tvs', []));

        return collect($this->templateVariableOptions())
            ->filter(fn (array $option) => in_array((int) $option['value'], $selected, true))
            ->values()
            ->all();
    }

    public function templateVariableOptions(): array
    {
        return collect(sLang::templateVariables())
            ->map(fn ($variable) => [
                'value' => (int) $variable->id,
                'label' => $this->templateVariableLabel($variable),
            ])
            ->values()
            ->all();
    }

    public function templateVariableLabel($variable): string
    {
        $caption = trim((string) ($variable->caption ?? ''));
        $name = trim((string) ($variable->name ?? ''));

        return ($caption !== '' ? $caption : $name) . ($name !== '' ? ' (' . $name . ')' : '');
    }

    protected function obsoleteTranslationKeys(): array
    {
        return (new sLangController())->obsoleteTranslationKeys();
    }

    protected function snapshot(array $data): string
    {
        ksort($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
}
