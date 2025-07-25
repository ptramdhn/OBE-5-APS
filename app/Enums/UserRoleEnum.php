<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN_PRODI = 'Admin Prodi';
    case DOSEN = 'Dosen';
    case MAHASISWA = 'Mahasiswa';

    public static function options(array $exclude = []): array
    {
        return collect(self::cases())
            ->filter(fn ($item) => ! in_array($item->name, $exclude))
            ->map(fn ($item) => [
                'value' => $item->value,
                'label' => $item->value,
            ])
            ->values()
            ->toArray();
    }
}
