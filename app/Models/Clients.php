<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Clients extends Model
{
    protected $table = 'clients';

    public $fillable = [
        'client_id', 'manager_id'
    ];
}
