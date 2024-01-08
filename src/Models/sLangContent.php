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
     * Add the language and template variable fields to the query
     *
     * @param Builder $query The query builder instance
     * @param string $locale The language locale
     * @param array $tvNames The array of template variable names
     * @return Builder The modified query builder instance
     */
    public function scopeLangAndTvs($query, $locale, $tvNames = [])
    {
        $query->select('*', 's_lang_content.resource as id', 's_lang_content.pagetitle as pagetitle');
        $query->addSelect('s_lang_content.longtitle as longtitle', 's_lang_content.description as description');
        $query->addSelect('s_lang_content.introtext as introtext', 's_lang_content.content as content');
        $query->addSelect('s_lang_content.menutitle as menutitle');
        $query->selectTvs($tvNames);

        return $query->addSelect('site_content.pagetitle as pagetitle_orig', 'site_content.longtitle as longtitle_orig')
            ->addSelect('site_content.description as description_orig', 'site_content.introtext as introtext_orig')
            ->addSelect('site_content.content as content_orig', 'site_content.menutitle as menutitle_orig')
            ->leftJoin('site_content', 's_lang_content.resource', '=', 'site_content.id')
            ->where('lang', '=', $locale);
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
     *
     * @param \Illuminate\Database\Query\Builder $query The database query builder instance
     * @param string $name The name of the TV to filter by
     * @param mixed $value The value to filter by
     * @return \Illuminate\Database\Query\Builder The modified query builder instance
     */
    public function scopeWhereTv($query, $name, $value)
    {
        return $query->leftJoin('site_tmplvar_contentvalues', 'site_tmplvar_contentvalues.contentid', '=', 's_lang_content.resource')
            ->leftJoin('site_tmplvars', 'site_tmplvars.id', '=', 'site_tmplvar_contentvalues.tmplvarid')
            ->whereName($name)
            ->whereValue($value);
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
            $base_url = MODX_SITE_URL . ltrim($base_url, '/');
        }
        return $base_url;
    }
}