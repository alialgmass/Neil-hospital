<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use HasUlids;

    protected $table = 'price_lists';

    protected $fillable = [
        'name',
        'type',
        'ins_company_id',
        'ins_coverage',
        'discount_pct',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ins_coverage' => 'float',
            'discount_pct' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'ins_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class, 'price_list_id');
    }
}
