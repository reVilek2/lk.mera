<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\File
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $name
 * @property string $type
 * @property string $extension
 * @property string $path
 * @property string $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $model
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $origin_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereOriginName($value)
 */
class File extends Model
{
    protected $table = 'files';
    public $timestamps = true;
    protected $fillable = ['origin_name', 'name','type', 'path','size'];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function nextAutoIncrementId()
    {
        $autoIncrement = DB::table('INFORMATION_SCHEMA.TABLES')
            ->select('AUTO_INCREMENT as id')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', 'files')
            ->first();

        return $autoIncrement ? $autoIncrement->id : 0;
    }
}
