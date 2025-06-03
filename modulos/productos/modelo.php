<?php
// public_html/modulos/productos/modelo.php

if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Producto
{
    /** @var mysqli */
    protected $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtener todos los productos, opcionalmente filtrados por ID de categoría.
     */
    public function obtenerTodos(int $categoria_id = null): array
    {
        $sql = "
            SELECT 
                p.*, 
                c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
        ";

        if ($categoria_id !== null) {
            $sql .= " WHERE p.categoria_id = ?";
            $stmt = $this->db->prepare($sql . " ORDER BY p.destacado DESC, p.id DESC");
            $stmt->bind_param('i', $categoria_id);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY p.destacado DESC, p.id DESC");
        }

        if (!$stmt) {
            die("Error preparando statement: " . $this->db->error);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener productos destacados (limitado por cantidad).
     */
    public function obtenerDestacados(int $limite = 6): array
    {
        $sql = "
            SELECT 
                p.*, 
                c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.destacado = 1
            ORDER BY p.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener un producto por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT 
                p.*, 
                c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows === 1 ? $res->fetch_assoc() : null;
    }

    /**
     * Obtener el ID de una categoría a partir de su nombre (sin importar mayúsculas/minúsculas).
     */
    public function obtenerIdCategoriaPorNombre(string $nombre): ?int
    {
        $sql = "SELECT id FROM categorias WHERE LOWER(nombre) = LOWER(?) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            die("Error preparando statement: " . $this->db->error);
        }

        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $fila = $res->fetch_assoc();
            return (int)$fila['id'];
        }

        return null;
    }

    /**
     * Generar código de acceso único.
     */
    public function generarCodigoAcceso(): string
    {
        return md5(uniqid((string)microtime(true), true));
    }
}