<?php

namespace Modules\Surgery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrRoom extends Model
{
    protected $table = 'or_rooms';

    protected $fillable = ['name', 'status', 'total_beds'];

    public function beds(): HasMany
    {
        return $this->hasMany(OrBed::class, 'room_id');
    }
}
