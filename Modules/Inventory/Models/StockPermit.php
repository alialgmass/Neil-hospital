<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockPermit extends Model
{
    use HasUlids;

    protected $fillable = [
        'permit_no',
        'type',
        'department',
        'reason',
        'notes',
        'created_by',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockPermitItem::class, 'permit_id');
    }
}
