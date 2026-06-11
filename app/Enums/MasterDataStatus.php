<?php

namespace App\Enums;

enum MasterDataStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case Archived = 'archived';
    case Invalid = 'invalid';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
