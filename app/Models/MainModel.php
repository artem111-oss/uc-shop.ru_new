<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BigbangModel
 *
 * @method static Builder|MainModel newModelQuery()
 * @method static Builder|MainModel newQuery()
 * @method static Builder|MainModel query()
 * @mixin Eloquent
 */
class MainModel extends Model
{
  protected $connection = 'mysql';

  public static function table(): string
  {
    return (new static)->getTable();
  }
}
