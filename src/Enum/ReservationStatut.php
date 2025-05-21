<?php
namespace App\Enum;

enum ReservationStatut: string
{
    case EN_ATTENTE = 'en_attente';
    case REFUSEE = 'refusee';
    case PASSEE = 'passee';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::REFUSEE => 'Refusée',
            self::PASSEE => 'Passée',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
