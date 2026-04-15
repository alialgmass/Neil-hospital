<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Service;

class PriceListItem extends Model
{
    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'service_id',
        'price',
    ];

    protected function casts(): array
    {
        return ['price' => 'float'];
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
