<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MoneyAmount;
use FileService;

/**
 * App\Models\DocumentHistory
 *
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property int $signed
 * @property int $paid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Document $document
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereSigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DocumentHistory whereUserId($value)
 * @mixin \Eloquent
 */
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
