<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MoneyAmount;

class Document extends Model
{
    const SAVE_FILE_DIR = '/documents';

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

    public function addFile(array $file)
    {
        $this->files()->create([
            'path' => $file['path'],
            'name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'extension' => $file['ext'],
        ]);
    }
}
