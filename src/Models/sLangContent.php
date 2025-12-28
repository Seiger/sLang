<?php namespace Seiger\sLang\Models;

use EvolutionCMS\Facades\UrlProcessor;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class sLangContent extends Eloquent\Model
{
    protected $table = 's_lang_content';
    protected $fillable = ['resource', 'lang', 'pagetitle', 'longtitle', 'description', 'introtext', 'content', 'menutitle', 'seotitle', 'seodescription'];

    /**
     * Add the language and template variable fields to the query.
     *
     * @param Builder $query The query builder instance
     * @param string $locale The language locale
     * @param array $tvNames The array of template variable names
     * @return Builder The modified query builder instance
     *
     * @deprecated since 1.0.8
     * TODO: REMOVE IN v1.2
     */
    public function scopeLangAndTvs($query, $locale, $tvNames = [])
    {
        $query = $this->scopeLang($query, $locale);
        return $query->withTVs($tvNames);
    }

    /**
     * Limit the query to a specific language. Falls back to evo()->getLocale() when locale is not provided.
     */
    public function scopeLang($query, ?string $locale = null)
    {
        $query = $query->withoutGlobalScope('language');
        $locale = static::resolveLocale($locale);
        $this->applyContentSelects($query);
        return $query->where('lang', '=', $locale);
    }

    /**
     * Append template variable fields while preserving base selects.
     */
    public function scopeWithTVs($query, array $tvNames = [])
    {
        $this->applyContentSelects($query);
        return $this->scopeSelectTvs($query, $tvNames);
    }

    /**
     * Adds TV selections to the query based on the given TV names.
     *
     * @param \Illuminate\Database\Query\Builder $query The database query builder instance
     * @param array $tvNames The array of TV names to select
     * @return \Illuminate\Database\Query\Builder The modified query builder instance
     */
    public function scopeSelectTvs($query, $tvNames = [])
    {
        if (count($tvNames)) {
            foreach ($tvNames as $tvName) {
                $query->addSelect(
                    DB::raw("(SELECT value FROM " . DB::getTablePrefix() . "site_tmplvar_contentvalues
                        WHERE " . DB::getTablePrefix() . "site_tmplvar_contentvalues.contentid = " . DB::getTablePrefix() . "s_lang_content.resource
                        AND " . DB::getTablePrefix() . "site_tmplvar_contentvalues.tmplvarid = (
                            SELECT " . DB::getTablePrefix() . "site_tmplvars.id 
                            FROM " . DB::getTablePrefix() . "site_tmplvars
                            WHERE " . DB::getTablePrefix() . "site_tmplvars.name = '" . $tvName . "'
                        ) LIMIT 1) as " . $tvName
                    )
                );
            }
        }
        return $query;
    }

    /**
     * Adds conditions to the query to filter for active items.
     *
     * @param \Illuminate\Database\Query\Builder $query The database query builder instance
     * @return \Illuminate\Database\Query\Builder The modified query builder instance
     */
    public function scopeActive($query)
    {
        return $query->where('published', '1')->where('deleted', '0');
    }

    /**
     * Performs a search on the query based on the search term.
     *
     * @return \Illuminate\Database\Query\Builder|null The modified query builder instance or null if no search term is provided
     */
    public function scopeSearch()
    {
        if (request()->has('search')) {
            $fields = collect(['pagetitle', 'longtitle']);

            $search = Str::of(request('search'))
                ->stripTags()
                ->replaceMatches('/[^\p{L}\p{N}\@\.!#$%&\'*+-\/=?^_`{|}~]/iu', ' ') // allowed symbol in email
                ->replaceMatches('/(\s){2,}/', '$1') // removing extra spaces
                ->trim()->explode(' ')
                ->filter(fn($word) => mb_strlen($word) > 2);

            $select = collect([0]);

            $search->map(fn($word) => $fields->map(fn($field) => $select->push("(CASE WHEN `".DB::getTablePrefix()."s_lang_content`.`{$field}` LIKE '%{$word}%' THEN 1 ELSE 0 END)"))); // Generate points source

            return $this->addSelect('*', DB::Raw('(' . $select->implode(' + ') . ') as points'))
                ->when($search->count(), fn($query) => $query->where(fn($query) => $search->map(fn($word) => $fields->map(fn($field) => $query->orWhere($field, 'like', "%{$word}%")))))
                ->orderByDesc('points');
        }
    }

    /**
     * Adds a WHERE clause to the query that filters results based on the given TV and value.
     * If value is an array, uses WHERE IN clause, otherwise uses WHERE clause with the specified operator.
     *
     * @param \Illuminate\Database\Query\Builder $query The database query builder instance
     * @param string $name The name of the TV to filter by
     * @param mixed $value The value to filter by (can be a single value or an array)
     * @param string $operator The comparison operator (=, >, <, >=, <=, !=, <>, like, not like). Default is '='
     * @return \Illuminate\Database\Query\Builder The modified query builder instance
     */
    public function scopeWhereTv($query, $name, $value, $operator = '=')
    {
        $tvValuesAlias = 'tv_values_' . $name;
        $tvVarsAlias = 'tv_vars_' . $name;

        // Check if join already exists to avoid "Not unique table/alias" error
        $eloquentQuery = $query->getQuery();
        $hasJoin = $this->hasTvJoin($eloquentQuery->joins ?? [], $tvValuesAlias);

        if (!$hasJoin) {
            $query = $query->leftJoin('site_tmplvar_contentvalues as ' . $tvValuesAlias, function($join) use ($tvValuesAlias) {
                $join->on($tvValuesAlias . '.contentid', '=', 's_lang_content.resource');
            });

            // Update query reference and check again for vars join
            $eloquentQuery = $query->getQuery();
            $hasVarsJoin = $this->hasTvJoin($eloquentQuery->joins ?? [], $tvVarsAlias);
            if (!$hasVarsJoin) {
                $query = $query->leftJoin('site_tmplvars as ' . $tvVarsAlias, function($join) use ($name, $tvValuesAlias, $tvVarsAlias) {
                    $join->on($tvVarsAlias . '.id', '=', $tvValuesAlias . '.tmplvarid')
                        ->where($tvVarsAlias . '.name', '=', $name);
                });
            }
        }

        if (is_array($value)) {
            // For arrays, use whereIn regardless of operator (array comparison doesn't make sense with >, <, etc.)
            return $query->whereIn($tvValuesAlias . '.value', $value);
        }

        // Handle LIKE operators
        $operator = strtolower($operator);
        if ($operator === 'like' || $operator === 'not like') {
            // If value doesn't already contain wildcards, add them for LIKE search
            $likeValue = $value;
            if (strpos($value, '%') === false) {
                $likeValue = '%' . $value . '%';
            }
            return $query->where($tvValuesAlias . '.value', $operator, $likeValue);
        }

        // For numeric comparisons, cast the value to numeric
        if (in_array($operator, ['>', '<', '>=', '<='])) {
            // Cast to numeric for comparison operators
            $prefix = DB::getTablePrefix();
            $fullAlias = $prefix . $tvValuesAlias;
            $column = '`' . $fullAlias . '`.`value`';
            return $query->whereRaw('CAST(' . $column . ' AS DECIMAL(10,2)) ' . $operator . ' ?', [(float)$value]);
        }

        // For equality operators, use standard where clause
        return $query->where($tvValuesAlias . '.value', $operator, $value);
    }

    /**
     * Retrieves the menu title attribute.
     *
     * This method returns the value of the menu title attribute for the current instance.
     * If the menu title attribute is empty, it falls back to the original menu title value (menutitle_orig).
     * If both the menu title attribute and the original menu title value are empty, it falls back to the
     * current page title attribute (pagetitle), or the original page title value (pagetitle_orig) if empty.
     * If both the menu title attribute, original menu title value, and the page title attribute are empty, it
     * returns an empty string.
     *
     * @return string The menu title attribute value
     */
    public function getMenutitleAttribute()
    {
        $menutitle_orig = $this->menutitle_orig ?? '';
        $pagetitle_orig = $this->pagetitle_orig ?? '';
        $menutitle = empty($this->attributes['menutitle']) ? $menutitle_orig : $this->attributes['menutitle'];
        $pagetitle = empty($this->pagetitle) ? $pagetitle_orig : $this->pagetitle;
        return empty($menutitle) ? $pagetitle : $menutitle;
    }

    /**
     * Get the full link attribute for the resource.
     *
     * @return string The full link attribute
     */
    public function getFullLinkAttribute()
    {
        $base_url = UrlProcessor::makeUrl($this->resource);
        if (str_starts_with($base_url, '/')) {
            $base_url = EVO_SITE_URL . ltrim($base_url, '/');
        }
        return $base_url;
    }

    /**
     * Applies base selects and joins required for language-aware scopes.
     */
    protected function applyContentSelects(Builder $query): Builder
    {
        $eloquentQuery = $query->getQuery();

        if (empty($eloquentQuery->columns)) {
            $query->select('s_lang_content.*');
        }

        $query->addSelect(
            's_lang_content.resource as id',
            's_lang_content.pagetitle as pagetitle',
            's_lang_content.longtitle as longtitle',
            's_lang_content.description as description',
            's_lang_content.introtext as introtext',
            's_lang_content.content as content',
            's_lang_content.menutitle as menutitle'
        );

        if (!$this->hasSiteContentJoin($eloquentQuery->joins ?? [])) {
            $query->leftJoin('site_content', 's_lang_content.resource', '=', 'site_content.id');
        }

        return $query->addSelect(
            'site_content.pagetitle as pagetitle_orig',
            'site_content.longtitle as longtitle_orig',
            'site_content.description as description_orig',
            'site_content.introtext as introtext_orig',
            'site_content.content as content_orig',
            'site_content.menutitle as menutitle_orig'
        );
    }

    /**
     * Detect whether site_content join has already been added.
     */
    protected function hasSiteContentJoin(array $joins): bool
    {
        foreach ($joins as $join) {
            if (($join->table ?? null) === 'site_content') {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect whether TV join with the given alias has already been added.
     */
    protected function hasTvJoin(array $joins, string $alias): bool
    {
        foreach ($joins as $join) {
            $table = $join->table ?? '';

            // Check if table is exactly the alias
            if ($table === $alias) {
                return true;
            }

            // Check for "table as alias" format (with or without backticks and table prefix)
            // Pattern: table_name as alias or `prefix_table_name` as `alias`
            if (preg_match('/\s+as\s+`?' . preg_quote($alias, '/') . '`?/i', $table)) {
                return true;
            }

            // Check if table ends with the alias (for prefixed tables without backticks)
            if (str_ends_with($table, ' as ' . $alias) || str_ends_with($table, ' AS ' . $alias)) {
                return true;
            }

            // Check the alias property if it exists
            if (isset($join->alias) && $join->alias === $alias) {
                return true;
            }

            // Check if the table string contains the alias (for cases where Laravel stores full SQL)
            // Look for patterns like: site_tmplvar_contentvalues as tv_values_price
            $prefix = DB::getTablePrefix();
            $patterns = [
                $prefix . 'site_tmplvar_contentvalues as ' . $alias,
                $prefix . 'site_tmplvars as ' . $alias,
                'site_tmplvar_contentvalues as ' . $alias,
                'site_tmplvars as ' . $alias,
            ];

            foreach ($patterns as $pattern) {
                if (str_contains($table, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve the locale used by language-aware scopes.
     */
    protected static function resolveLocale(?string $locale): string
    {
        $locale = $locale ?? (function_exists('evo') ? (string)evo()->getLocale() : '');

        if ($locale === '' && function_exists('evo')) {
            $locale = (string)evo()->getConfig('lang', 'uk');
        }

        if ($locale === '') {
            $locale = 'uk';
        }

        $underscorePos = strpos($locale, '_');
        if ($underscorePos !== false) {
            $locale = substr($locale, 0, $underscorePos);
        }

        return strtolower($locale);
    }

    /**
     * Register the default language scope for the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('language', function (Builder $builder) {
            /** @var self $model */
            $model = new static;
            $locale = static::resolveLocale(null);
            $model->applyContentSelects($builder);
            $builder->where($model->getTable() . '.lang', '=', $locale);
        });
    }
}