<?php

namespace App\Enums;

enum ImportMode: string
{
    case DryRun = 'dry_run';
    case Insert = 'insert';
    case Upsert = 'upsert';
    case ReplaceType = 'replace_type';
}
