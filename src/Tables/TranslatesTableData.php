<?php namespace Seiger\sLang\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangTranslate;

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
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, array<string, mixed>>
     */
    public function columns(array $columns): array
    {
        $defaultLocale = sLang::langDefault();

        $languageColumns = collect(sLang::langConfig())
            ->map(function (string $locale) use ($defaultLocale) {
                $column = [
                    'key' => $locale,
                    'type' => 'text',
                    'label' => strtoupper($locale),
                    'sortable' => true,
                    'sort_field' => $locale,
                    'editable' => true,
                    'rules' => ['nullable', 'string'],
                ];

                if ($locale !== $defaultLocale) {
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
     * @return array<string, string>
     */
    public function modalDefaults(): array
    {
        return collect(sLang::langConfig())
            ->mapWithKeys(fn (string $locale): array => [$locale => ''])
            ->prepend('', 'key')
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    public function modalFields(array $fields, array $data, ?int $id, string $mode): array
    {
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
     * @param array<string, mixed> $data
     */
    public function saveModal(array $data, ?int $id, string $mode): int
    {
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
        $controller->setModifyTables();

        $translate = sLangTranslate::create(['key' => $key]);
        $translateId = (int) $translate->getKey();

        foreach (sLang::langConfig() as $locale) {
            $controller->updateTranslate((string) $translateId, $locale, trim((string) ($data[$locale] ?? '')));
        }

        return $translateId;
    }

    public function createInlineRow(): int
    {
        $this->controller()->setModifyTables();
        $key = $this->uniqueKey('new.translation.' . date('YmdHis'));
        $translate = sLangTranslate::create(['key' => $key]);
        $default = sLang::langDefault();
        $translate->{$default} = $key;
        $translate->save();

        return (int) $translate->getKey();
    }

    public function deleteName(int $id): string
    {
        $translate = sLangTranslate::find($id);

        return $translate ? (string) $translate->key : (string) $id;
    }

    public function deleteRow(int $id): void
    {
        $this->controller()->deleteTranslate($id);
    }

    /**
     * @param array<string, mixed> $action
     */
    public function synchronizeTranslations(array $action = [], ?int $selectedId = null): int
    {
        $before = sLangTranslate::query()->count();
        $this->controller()->parseBlade();
        $after = sLangTranslate::query()->count();

        return max(0, $after - $before);
    }

    /**
     * @param array<string, mixed> $action
     * @return array<string, string>
     */
    public function synchronizeAttributes(array $action = [], ?int $selectedId = null): array
    {
        $title = __('sLang::global.synchronize_help');

        return [
            'title' => is_string($title) ? $title : '',
        ];
    }

    /**
     * @param array<string, mixed> $column
     */
    public function updateInlineField(int $id, string $field, string $value, array $column = []): string
    {
        $translate = sLangTranslate::find($id);

        if (!$translate) {
            return $value;
        }

        $value = trim($value);

        if ($field === 'key') {
            return (string) $translate->key;
        }

        if (in_array($field, sLang::langConfig(), true)) {
            $this->controller()->setModifyTables();
            $this->controller()->updateTranslate((string) $id, $field, $value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $action
     * @param array<string, mixed> $column
     */
    public function autoTranslateInlineField(int $id, string $field, array $action = [], array $column = []): string
    {
        if (!in_array($field, sLang::langConfig(), true) || $field === sLang::langDefault()) {
            return '';
        }

        $this->controller()->setModifyTables();

        return $this->controller()->setAutomaticTranslate((string) $id, $field);
    }

    /**
     * @param array<string, mixed> $action
     * @param array<string, mixed> $column
     */
    public function autoTranslateEmptyColumn(string $field, array $action = [], array $column = []): int
    {
        if (!in_array($field, sLang::langConfig(), true) || $field === sLang::langDefault()) {
            return 0;
        }

        $default = sLang::langDefault();
        $controller = $this->controller();
        $controller->setModifyTables();

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
     * @return Builder<sLangTranslate>
     */
    protected function query(): Builder
    {
        $query = sLangTranslate::query();
        $search = trim((string) ($this->state['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $where) use ($search) {
                $like = '%' . Str::lower($search) . '%';
                $where->orWhereRaw('LOWER(`key`) LIKE ?', [$like]);

                foreach (sLang::langConfig() as $locale) {
                    $where->orWhereRaw('LOWER(`' . str_replace('`', '', $locale) . '`) LIKE ?', [$like]);
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
