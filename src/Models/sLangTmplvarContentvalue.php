<?php namespace Seiger\sLang\Models;

use EvolutionCMS\Facades\UrlProcessor;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class sLangTmplvarContentvalue extends Eloquent\Model
{
    protected $table = 's_lang_tmplvar_contentvalues';
    protected $fillable = ['lang', 'tmplvarid', 'contentid', 'value'];
    public $timestamps = false;
}