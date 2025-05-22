<?php
// modulos/admin/categorias/modelo.php

class Categoria
{
    /** @var mysqli */
    private $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Devuelve un array con todas las categorías
     * [
     *   ['id'=>1,'nombre'=>'Games','descripcion'=>'...', 'imagen'=>'img/...'],
     *   ...
     * ]
     */
    public function obtenerTodas(): array
    {
        $sql    = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY nombre";
        $result = $this->db->query($sql);
        return $result
            ? $result->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    /**
     * Devuelve una categoría por su ID, o null si no existe
     */
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, descripcion, imagen 
               FROM categorias 
              WHERE id = ? 
              LIMIT 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows
            ? $res->fetch_assoc()
            : null;
    }

    /**
     * (Opcional) Crea una nueva categoría y devuelve el nuevo ID
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO categorias (nombre, descripcion, parent_id, imagen)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssis",
            $datos['nombre'],
            $datos['descripcion'],
            $datos['parent_id'],
            $datos['imagen']
        );
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return 0;
    }

    /**
     * (Opcional) Actualiza una categoría existente
     */
    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE categorias
                SET nombre      = ?,
                    descripcion = ?,
                    parent_id   = ?,
                    imagen      = ?
              WHERE id = ?"
        );
        $stmt->bind_param(
            "ssisi",
            $datos['nombre'],
            $datos['descripcion'],
            $datos['parent_id'],
            $datos['imagen'],
            $id
        );
        return $stmt->execute();
    }

    /**
     * (Opcional) Elimina una categoría
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
