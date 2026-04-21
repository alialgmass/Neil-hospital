<?php

namespace Modules\Inventory\Enums;

enum PermitType: string
{
    case Issue = 'issue';
    case Add = 'add';

    public function label(): string
    {
        return match ($this) {
            self::Issue => 'صرف',
            self::Add => 'إضافة',
        };
    }
}
