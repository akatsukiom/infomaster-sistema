<?php
// public_html/modulos/productos/modelo.php

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Producto
{
    /** @var mysqli */
    protected $db;

    /**
     * Recibe la conexión MySQLi
     */
    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Devuelve todos los productos, opcionalmente filtrados por categoría
     *
     * @param int|null $categoria_id
     * @return array
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

        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Productos destacados (limitado)
     *
     * @param int $limite
     * @return array
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
     * Obtener un producto por su ID
     *
     * @param int $id
     * @return array|null
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
     * Genera un código único para productos digitales
     *
     * @return string
     */
    public function generarCodigoAcceso(): string
    {
        return md5(uniqid((string)microtime(true), true));
    }
}
