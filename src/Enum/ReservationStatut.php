<?php
namespace App\Enum;

enum ReservationStatut: string
{
    case EN_ATTENTE = 'en_attente';
    case CONFIRMEE = 'confirmee';
    case PASSEE = 'passee';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::CONFIRMEE => 'Confirmée',
            self::PASSEE => 'Passée',
        };
    }
}
