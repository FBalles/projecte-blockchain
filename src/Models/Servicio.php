<?php
declare(strict_types=1);

class Servicio
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $descripcion,
        public readonly float $precio,
        public readonly bool $disponible,
    ) {}

    public static function todos(): array
    {
        $stmt = db()->query("SELECT * FROM servicios ORDER BY nombre");
        return array_map(fn($r) => new self(
            (int)$r['id'], $r['nombre'], $r['descripcion'],
            (float)$r['precio'], (bool)$r['disponible']
        ), $stmt->fetchAll());
    }

    public static function disponibles(): array
    {
        $stmt = db()->query("SELECT * FROM servicios WHERE disponible = 1 ORDER BY nombre");
        return array_map(fn($r) => new self(
            (int)$r['id'], $r['nombre'], $r['descripcion'],
            (float)$r['precio'], (bool)$r['disponible']
        ), $stmt->fetchAll());
    }

    public static function guardar(string $nombre, string $descripcion, float $precio, bool $disponible): void
    {
        $stmt = db()->prepare("INSERT INTO servicios (nombre, descripcion, precio, disponible) VALUES (?,?,?,?)");
        $stmt->execute([$nombre, $descripcion, $precio, $disponible]);
    }

    public static function toggle(int $id): void
    {
        db()->prepare("UPDATE servicios SET disponible = !disponible WHERE id = ?")->execute([$id]);
    }
}