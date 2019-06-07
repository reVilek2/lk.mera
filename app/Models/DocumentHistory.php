<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MoneyAmount;
use FileService;

class DocumentHistory extends Model
{
    protected $table = 'documents_history';
    public $timestamps = true;
    protected $fillable = ['document_id','user_id','signed','paid'];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
