<?php

namespace Modules\Surgery\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surgery extends Model
{
    use HasUlids;

    protected $fillable = [
        'booking_id',
        'or_bed_id',
        'surgeon_id',
        'dept',
        'eye',
        'procedure',
        'anaesthesia',
        'status',
        'pre_op_notes',
        'op_report',
        'post_op_notes',
        'complications',
        'scheduled_at',
        'started_at',
        'ended_at',
        'supplies_used',
        'supply_total',
    ];

    protected $casts = [
        'supplies_used' => 'array',
        'supply_total'  => 'decimal:2',
        'scheduled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(\Modules\Booking\Models\Booking::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(\Modules\Doctor\Models\Doctor::class, 'surgeon_id');
    }

    public function orBed(): BelongsTo
    {
        return $this->belongsTo(OrBed::class, 'or_bed_id');
    }
}
