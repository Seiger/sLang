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
     * Get the content item with lang and original fields
     *
     * @param Builder $query
     * @param string $locale
     * @param array $tvNames
     * @return Builder
     */
    public function scopeLangAndTvs($query, $locale, $tvNames = [])
    {
        $query->select('*', 's_lang_content.resource as id', 's_lang_content.pagetitle as pagetitle');
        $query->addSelect('s_lang_content.longtitle as longtitle', 's_lang_content.description as description');
        $query->addSelect('s_lang_content.introtext as introtext', 's_lang_content.content as content');
        $query->addSelect('s_lang_content.menutitle as menutitle');

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

        return $query->addSelect('site_content.pagetitle as pagetitle_orig', 'site_content.longtitle as longtitle_orig')
            ->addSelect('site_content.description as description_orig', 'site_content.introtext as introtext_orig')
            ->addSelect('site_content.content as content_orig', 'site_content.menutitle as menutitle_orig')
            ->leftJoin('site_content', 's_lang_content.resource', '=', 'site_content.id')
            ->where('lang', '=', $locale);
    }

    /**
     * Only active resources
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('published', '1')->where('deleted', '0');
    }

    /**
     * Filter search
     *
     * @return mixed
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
     * Filtering documents by TV parameter
     *
     * @param Builder $query
     * @param string $name
     * @param string|int $value
     * @return Builder
     */
    public function scopeWhereTv($query, $name, $value)
    {
        return $query->leftJoin('site_tmplvar_contentvalues', 'site_tmplvar_contentvalues.contentid', '=', 's_lang_content.resource')
            ->leftJoin('site_tmplvars', 'site_tmplvars.id', '=', 'site_tmplvar_contentvalues.tmplvarid')
            ->whereName($name)
            ->whereValue($value);
    }

    /**
     * Get the menutitle attribute
     *
     * @return mixed
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
     * Get the resource full link
     *
     * @return string full_link
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