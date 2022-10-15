<?php namespace sLang\Models;

use Illuminate\Database\Eloquent;

class sLangTranslate extends Eloquent\Model
{
    protected $primaryKey = 'tid';
    protected $fillable = ['key'];
}