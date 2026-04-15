<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPermitItem extends Model
{
    protected $fillable = [
        'permit_id',
        'item_id',
        'item_name',
        'qty',
        'unit_cost',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(StockPermit::class, 'permit_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
