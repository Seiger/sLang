<?php namespace Seiger\sLang\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangTranslate;

class TranslatesTableData
{
    protected string $moduleUrl;

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

    public function rows(int $page, int $perPage): array
    {
        return $this->query()
            ->forPage(max(1, $page), max(1, $perPage))
            ->get()
            ->map(fn (sLangTranslate $translate) => $this->row($translate))
            ->values()
            ->all();
    }

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

    public function filterGroups(): array
    {
        return [];
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

    public function updateInlineField(int $id, string $field, string $value, array $column = []): string
    {
        $translate = sLangTranslate::find($id);

        if (!$translate) {
            return $value;
        }

        $value = trim($value);

        if ($field === 'key') {
            $value = $this->uniqueKey($value !== '' ? $value : (string) $translate->key, $id);
            $translate->key = $value;
            $translate->save();
            $this->controller()->setModifyTables();

            return $value;
        }

        if (in_array($field, sLang::langConfig(), true)) {
            $this->controller()->setModifyTables();
            $this->controller()->updateTranslate((string) $id, $field, $value);
        }

        return $value;
    }

    public function autoTranslateInlineField(int $id, string $field, array $action = [], array $column = []): string
    {
        if (!in_array($field, sLang::langConfig(), true) || $field === sLang::langDefault()) {
            return '';
        }

        $this->controller()->setModifyTables();

        return $this->controller()->setAutomaticTranslate((string) $id, $field);
    }

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

        return $query->orderBy($sort, $direction);
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
