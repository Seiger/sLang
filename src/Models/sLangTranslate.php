<?php namespace Seiger\sLang\Models;

use Illuminate\Database\Eloquent;

/**
 * @property int $tid
 * @property string $key
 * @property string|null $uk
 * @property string|null $en
 * @property string|null $az
 * @method static \Illuminate\Database\Eloquent\Builder<self> query()
 * @method static self create(array<string, mixed> $attributes = [])
 * @method static self|null find(mixed $id, array<int, string> $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection<int, self> all(array<int, string> $columns = ['*'])
 */
class sLangTranslate extends Eloquent\Model
{
    protected $primaryKey = 'tid';
    protected $fillable = ['key'];
}
