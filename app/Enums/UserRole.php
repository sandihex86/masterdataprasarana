<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Operator = 'operator';
    case Verifikator = 'verifikator';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Admin => 'Admin',
            self::Operator => 'Operator',
            self::Verifikator => 'Verifikator',
            self::Viewer => 'Viewer',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Superadmin => 'Akses penuh ke seluruh modul, konfigurasi, dan data sensitif.',
            self::Admin => 'Mengelola operasional utama, master data, integrasi, dan dokumentasi sistem.',
            self::Operator => 'Mengelola data operasional harian dan proses import mapping.',
            self::Verifikator => 'Memeriksa hasil import dan meninjau data tanpa hak ubah penuh.',
            self::Viewer => 'Hanya melihat dashboard, dokumentasi, dan data baca.',
        };
    }

    public function grantsFullAccess(): bool
    {
        return match ($this) {
            self::Superadmin, self::Admin => true,
            default => false,
        };
    }

    /**
     * @return array<int, string>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Superadmin, self::Admin => ['*'],
            self::Operator => [
                'master-data:read',
                'master-data:write',
                'imports:read',
                'imports:create',
            ],
            self::Verifikator => [
                'master-data:read',
                'imports:read',
            ],
            self::Viewer => [
                'master-data:read',
            ],
        };
    }

    public function allowsAbility(string $ability): bool
    {
        return $this->grantsFullAccess() || in_array($ability, $this->abilities(), true);
    }

    public function canViewSensitiveMetadata(): bool
    {
        return $this->grantsFullAccess();
    }
}
