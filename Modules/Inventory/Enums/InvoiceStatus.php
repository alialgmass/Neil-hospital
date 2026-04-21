<?php

namespace Modules\Inventory\Enums;

enum InvoiceStatus: string
{
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Posted => 'مرحّل',
            self::Cancelled => 'ملغي',
        };
    }
}
