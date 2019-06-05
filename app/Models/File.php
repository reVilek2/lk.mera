<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    protected $table = 'files';
    public $timestamps = true;
    protected $fillable = ['name','type','extension','path','size'];

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
