<?php

namespace App\Enums;

enum AuditAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case ImportInsert = 'import_insert';
    case ImportUpdate = 'import_update';
    case ImportDelete = 'import_delete';
    case TokenCreate = 'token_create';
    case TokenRevoke = 'token_revoke';
    case MappingUpdate = 'mapping_update';
}
