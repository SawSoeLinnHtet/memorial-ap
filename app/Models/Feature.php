<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model 
{
    use SoftDeletes;
    
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i',
        'updated_at' => 'datetime:d-m-Y H:i',
        'deleted_at' => 'datetime:d-m-Y H:i',
    ];
    
    public function collections()
    {
        return $this->belongsToMany(Collection::class);
    }
}
