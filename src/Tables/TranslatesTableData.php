<?php namespace Seiger\sLang\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangTranslate;
use Seiger\sLang\Support\ManagerAccess;
use Seiger\sLang\Support\SettingsAccess;

/**
 * Serve the site-wide dictionary. All keys and configured locales share the same manager permissions.
 */
class TranslatesTableData
{
    protected string $moduleUrl;

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     */
    public function __construct(
        protected array $context = [],
        protected array $state = [],
        protected array $config = [],
    ) {
        $this->moduleUrl = (string) ($context['moduleUrl'] ?? sLang::moduleUrl());
    }

    public function total(): int
    {
        return (clone $this->query())->toBase()->getCountForPagination();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(int $page, int $perPage): array
    {
        $query = $this->query();
        $query->forPage(max(1, $page), max(1, $perPage));
        $translations = $query->get();

        return $translations
            ->map(fn (sLangTranslate $translate) => $this->row($translate))
            ->values()
            ->all();
    }

    /**
     * Expose editable language columns and translation actions according to the manager's permissions.
     *
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, array<string, mixed>>
     */
    public function columns(array $columns): array
    {
        $this->authorize();
        $canWrite = $this->canWrite();
        $defaultLocale = sLang::langDefault();

        $languageColumns = collect(sLang::langConfig())
            ->map(function (string $locale) use ($defaultLocale, $canWrite) {
                $column = [
                    'key' => $locale,
                    'type' => 'text',
                    'label' => strtoupper($locale),
                    'sortable' => true,
                    'sort_field' => $locale,
                    'editable' => $canWrite,
                    'rules' => ['nullable', 'string'],
                ];

                if ($canWrite && $locale !== $defaultLocale) {
                    $column['inline_actions'] = [
                        [
                            'key' => 'auto_translate',
                            'provider' => 'autoTranslateInlineField',
                            'icon' => 'language',
                            'label' => 'sLang::global.auto_translate',
                            'tone' => 'primary',
                        ],
                    ];
                    $column['header_actions'] = [
                        [
                            'key' => 'auto_translate_empty',
                            'provider' => 'autoTranslateEmptyColumn',
                            'icon' => 'language',
                            'label' => 'sLang::global.auto_translate_empty',
                            'tone' => 'primary',
                        ],
                    ];
                }

                return $column;
            })
            ->values()
            ->all();

        return array_merge($columns, $languageColumns);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function filterGroups(): array
    {
        return [];
    }

    /**
     * Supply the create modal's initial values to managers who can write the dictionary.
     *
     * @return array<string, string>
     */
    public function modalDefaults(): array
    {
        $this->authorize(write: true);

        return collect(sLang::langConfig())
            ->mapWithKeys(fn (string $locale): array => [$locale => ''])
            ->prepend('', 'key')
            ->all();
    }

    /**
     * Supply fields for the create modal. EvoUI also asks for these when rendering a closed modal.
     *
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    public function modalFields(array $fields, array $data, ?int $id, string $mode): array
    {
        $this->authorize();
        if (!$this->canWrite()) {
            return [];
        }

        $languageFields = collect(sLang::langConfig())
            ->map(fn (string $locale): array => [
                'name' => $locale,
                'type' => 'text',
                'label' => strtoupper($locale),
                'placeholder' => 'sLang::global.translation_value_placeholder',
                'rules' => ['nullable', 'string'],
            ])
            ->values()
            ->all();

        return array_merge([
            [
                'name' => 'key',
                'type' => 'text',
                'label' => 'KEY',
                'placeholder' => 'sLang::global.translation_key_placeholder',
                'help' => 'sLang::global.translation_key_help',
                'rules' => ['required', 'string', 'max:256'],
            ],
        ], $languageFields);
    }

    /**
     * Create a dictionary entry and save its values for the configured locales.
     *
     * @param array<string, mixed> $data
     */
    public function saveModal(array $data, ?int $id, string $mode): int
    {
        $this->authorize(write: true);

        if ($mode !== 'create') {
            return 0;
        }

        $key = trim((string) ($data['key'] ?? ''));

        if ($key === '') {
            throw ValidationException::withMessages([
                'modalData.key' => __('validation.required', ['attribute' => 'KEY']),
            ]);
        }

        if (mb_strlen($key) > 256) {
            throw ValidationException::withMessages([
                'modalData.key' => __('validation.max.string', ['attribute' => 'KEY', 'max' => 256]),
            ]);
        }

        if (sLangTranslate::query()->where('key', $key)->exists()) {
            throw ValidationException::withMessages([
                'modalData.key' => __('sLang::global.translation_key_exists'),
            ]);
        }

        $controller = $this->controller();
        $controller->requireTranslationColumns();

        $translate = sLangTranslate::create(['key' => $key]);
        $translateId = (int) $translate->getKey();

        foreach (sLang::langConfig() as $locale) {
            $controller->updateTranslate((string) $translateId, $locale, trim((string) ($data[$locale] ?? '')));
        }

        evo()->clearCache('full');

        return $translateId;
    }

    /**
     * Create an entry with a generated key through EvoUI's inline creation action.
     */
    public function createInlineRow(): int
    {
        $this->authorize(write: true);

        $this->controller()->requireTranslationColumns();
        $key = $this->uniqueKey('new.translation.' . date('YmdHis'));
        $translate = sLangTranslate::create(['key' => $key]);
        $default = sLang::langDefault();
        $translate->{$default} = $key;
        $translate->save();
        evo()->clearCache('full');

        return (int) $translate->getKey();
    }

    /**
     * Resolve an entry's label for an authorized delete confirmation.
     */
    public function deleteName(int $id): string
    {
        $this->authorize(write: true);

        $translate = sLangTranslate::find($id);

        return $translate ? (string) $translate->key : (string) $id;
    }

    /**
     * Delete a dictionary entry and refresh the generated translations.
     */
    public function deleteRow(int $id): void
    {
        $this->authorize(write: true);

        $this->controller()->deleteTranslate($id);
    }

    /**
     * Scan templates, add missing dictionary entries and rebuild translation files.
     *
     * @param array<string, mixed> $action
     */
    public function synchronizeTranslations(array $action = [], ?int $selectedId = null): int
    {
        $this->authorize(write: true);

        $before = sLangTranslate::query()->count();
        $this->controller()->parseBlade();
        $after = sLangTranslate::query()->count();

        return max(0, $after - $before);
    }

    /**
     * Provide the synchronization tooltip and disable the action for read-only managers.
     *
     * @param array<string, mixed> $action
     * @return array{title: string, disabled: bool}
     */
    public function synchronizeAttributes(array $action = [], ?int $selectedId = null): array
    {
        $title = __('sLang::global.synchronize_help');

        return [
            'title' => is_string($title) ? $title : '',
        ] + $this->managementAttributes();
    }

    /**
     * Disable mutation buttons in the toolbar for managers with read-only access.
     *
     * @return array{disabled: bool}
     * @since 2.1.0
     */
    public function managementAttributes(): array
    {
        return ['disabled' => !$this->canWrite()];
    }

    /**
     * Expose row actions only to managers who can change the dictionary.
     *
     * @param array<int, array<string, mixed>> $actions
     * @return array<int, array<string, mixed>>
     * @since 2.1.0
     */
    public function rowActions(array $actions): array
    {
        return $this->canWrite() ? $actions : [];
    }

    /**
     * Save a translation in a configured locale while keeping the dictionary key fixed.
     *
     * @param array<string, mixed> $column
     */
    public function updateInlineField(int $id, string $field, string $value, array $column = []): string
    {
        $this->authorize(write: true);

        $translate = sLangTranslate::find($id);

        if (!$translate) {
            return $value;
        }

        $value = trim($value);

        if ($field === 'key') {
            return (string) $translate->key;
        }

        if (in_array($field, sLang::langConfig(), true)) {
            $controller = $this->controller();
            $controller->requireTranslationColumns();
            $controller->updateTranslate((string) $id, $field, $value);
            evo()->clearCache('full');
        }

        return $value;
    }

    /**
     * Translate an existing entry into a configured non-default locale and save the result.
     *
     * @param array<string, mixed> $action
     * @param array<string, mixed> $column
     */
    public function autoTranslateInlineField(int $id, string $field, array $action = [], array $column = []): string
    {
        $this->authorize(write: true);

        if (!in_array($field, sLang::langConfig(), true) || $field === sLang::langDefault()) {
            return '';
        }

        if (!sLangTranslate::query()->whereKey($id)->exists()) {
            return '';
        }

        $controller = $this->controller();
        $controller->requireTranslationColumns();
        $result = $controller->setAutomaticTranslate((string) $id, $field);
        evo()->clearCache('full');

        return $result;
    }

    /**
     * Fill empty translations in a configured non-default locale with the same authority as an inline edit.
     *
     * @param array<string, mixed> $action
     * @param array<string, mixed> $column
     */
    public function autoTranslateEmptyColumn(string $field, array $action = [], array $column = []): int
    {
        $this->authorize(write: true);

        if (!in_array($field, sLang::langConfig(), true) || $field === sLang::langDefault()) {
            return 0;
        }

        $default = sLang::langDefault();
        $controller = $this->controller();
        $controller->requireTranslationColumns();

        $ids = sLangTranslate::query()
            ->where(function (Builder $query) use ($field) {
                $query->whereNull($field)->orWhere($field, '');
            })
            ->whereNotNull($default)
            ->where($default, '!=', '')
            ->orderBy('tid')
            ->pluck('tid')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $translated = 0;

        foreach ($ids as $id) {
            $result = $controller->setAutomaticTranslate((string) $id, $field);

            if (trim((string) $result) !== '') {
                $translated++;
            }
        }

        evo()->clearCache('full');

        return $translated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function row(sLangTranslate $translate): array
    {
        $row = [
            'id' => (int) $translate->getKey(),
            'wire_key' => 'slang-translate-' . $translate->getKey(),
            'key' => (string) $translate->key,
        ];

        foreach (sLang::langConfig() as $locale) {
            $row[$locale] = (string) ($translate->{$locale} ?? '');
        }

        return $row;
    }

    /**
     * Build the search and sort query for authorized dictionary readers.
     *
     * Quote dictionary identifiers using the active database grammar and bind the search pattern.
     *
     * @return Builder<sLangTranslate>
     */
    protected function query(): Builder
    {
        $this->authorize();

        $query = sLangTranslate::query();
        $search = trim((string) ($this->state['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $where) use ($search) {
                $search = Str::lower($search);
                $builder = $where->getQuery();
                $grammar = $builder->getGrammar();

                // PostgreSQL's C locale leaves Cyrillic uppercase letters unchanged in LOWER().
                $cyrillic = $builder->getConnection()->getDriverName() === 'pgsql'
                    ? (preg_replace('/\P{Cyrillic}+/u', '', $search) ?? '')
                    : '';
                $bindings = $cyrillic !== '' ? [Str::upper($cyrillic), $cyrillic] : [];
                $bindings[] = '%' . $search . '%';

                foreach (['key', ...sLang::langConfig()] as $column) {
                    $identifier = $grammar->wrap($column);
                    $expression = $cyrillic !== '' ? "TRANSLATE({$identifier}, ?, ?)" : $identifier;
                    $where->orWhereRaw("LOWER({$expression}) LIKE ?", $bindings);
                }
            });
        }

        $sort = (string) ($this->state['sort'] ?? 'tid');
        $direction = strtolower((string) ($this->state['direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = array_merge(['tid', 'key'], sLang::langConfig());

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'tid';
        }

        $query->orderBy($sort, $direction);

        return $query;
    }

    /**
     * Check the permission used by the writable dictionary controls.
     *
     * @since 2.1.0
     */
    protected function canWrite(): bool
    {
        return SettingsAccess::allows();
    }

    /**
     * Require module access for reads or the settings permission for dictionary administration.
     *
     * @since 2.1.0
     */
    protected function authorize(bool $write = false): void
    {
        if ($write) {
            SettingsAccess::authorize();
            return;
        }

        ManagerAccess::authorize();
    }

    protected function uniqueKey(string $key, int $ignoreId = 0): string
    {
        $base = Str::limit(trim($key) !== '' ? trim($key) : 'new.translation', 252, '');
        $candidate = $base;
        $index = 2;

        while (sLangTranslate::query()
            ->where('key', $candidate)
            ->when($ignoreId > 0, fn (Builder $query) => $query->where('tid', '!=', $ignoreId))
            ->exists()) {
            $suffix = '.' . $index++;
            $candidate = Str::limit($base, 256 - strlen($suffix), '') . $suffix;
        }

        return $candidate;
    }

    protected function controller(): sLangController
    {
        return new sLangController();
    }
}
