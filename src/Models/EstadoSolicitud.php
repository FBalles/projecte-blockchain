<?php
declare(strict_types=1);

enum EstadoSolicitud: string
{
    case Pendiente  = 'pendiente';
    case Aceptada   = 'aceptada';
    case Rechazada  = 'rechazada';
    case Realizada  = 'realizada';
    case Validada   = 'validada';

    public function label(): string
    {
        return match($this) {
            self::Pendiente  => 'Pendiente',
            self::Aceptada   => 'Aceptada',
            self::Rechazada  => 'Rechazada',
            self::Realizada  => 'Realizada',
            self::Validada   => 'Validada',
        };
    }
}