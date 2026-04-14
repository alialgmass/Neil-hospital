<?php

namespace Modules\Surgery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrBed extends Model
{
    protected $table = 'or_beds';

    protected $fillable = ['room_id', 'bed_number', 'status'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(OrRoom::class, 'room_id');
    }
}
