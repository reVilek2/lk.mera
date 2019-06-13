<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MoneyAmount;
use FileService;

/**
 * App\Models\Document
 *
 * @property int $id
 * @property string $name
 * @property int $client_id
 * @property int $manager_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read mixed $created_at_humanize
 * @property-read mixed $humanize_amount
 * @property-read \App\Models\User $manager
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $signed
 * @property int $paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DocumentHistory[] $history
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereSigned($value)
 */
class Document extends Model
{
    protected $table = 'documents';
    public $timestamps = true;
    protected $fillable = ['name','amount','client_id','manager_id'];
    protected $appends = [
        'humanize_amount',
        'created_at_humanize',
    ];
    public function getCreatedAtHumanizeAttribute()
    {
        return humanize_date($this->created_at, 'd.m.Y');
    }
    function getHumanizeAmountAttribute()
    {
        return MoneyAmount::toHumanize($this->amount);
    }
    public function getAmountAttribute($value)
    {
        return MoneyAmount::toReadable($value);
    }
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = MoneyAmount::toExternal($value);
    }

    /**
     * @return MorphMany
     */
    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }

    public function client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }

    public function manager()
    {
        return $this->hasOne(User::class, 'id', 'manager_id');
    }

    public function history()
    {
        return $this->hasMany(DocumentHistory::class, 'document_id');
    }

    public function addFile(array $file)
    {
        $this->files()->create([
            'type' => $file['type'],
            'origin_name' => $file['origin_name'],
            'name' => $file['name'],
            'path' => $file['path'],
            'size' => $file['size'],
        ]);
    }

    public static function nextAutoIncrementId()
    {
        $autoIncrement = DB::table('INFORMATION_SCHEMA.TABLES')
            ->select('AUTO_INCREMENT as id')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', 'documents')
            ->first();

        return $autoIncrement ? $autoIncrement->id : 0;
    }

    public static function getSaveFileDir()
    {
        $AutoIncrementId = self::nextAutoIncrementId();
        return '/'.FileService::generateFolderName($AutoIncrementId);
    }
}
