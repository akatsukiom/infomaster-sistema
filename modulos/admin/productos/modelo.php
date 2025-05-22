<?php
// modulos/admin/productos/modelo.php

class Producto {
    /** @var mysqli */
    private $db;

    /**
     * Constructor: recibe la conexión mysqli
     */
    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    /**
     * Obtiene todos los productos, opcionalmente filtrados por categoría.
     * @param int $categoriaId Si >0, filtra por esa categoría; si 0 devuelve todos.
     * @return array Lista de productos (each: ['id','nombre','descripcion','precio_base','categoria_id','imagen',...])
     */
    public function obtenerTodos(int $categoriaId = 0): array {
        if ($categoriaId > 0) {
            $sql = "
                SELECT p.*, c.nombre AS categoria
                  FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
                 WHERE p.categoria_id = ?
              ORDER BY p.id DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $categoriaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        } else {
            $sql = "
                SELECT p.*, c.nombre AS categoria
                  FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
              ORDER BY p.id DESC
            ";
            $rs = $this->db->query($sql);
            return $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    /**
     * Obtiene un solo producto por su ID.
     * @param int $id
     * @return array|false Los datos del producto o false si no existe.
     */
    public function obtenerPorId(int $id) {
        $sql = "
            SELECT p.*, c.nombre AS categoria
              FROM productos p
         LEFT JOIN categorias c ON p.categoria_id = c.id
             WHERE p.id = ? LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }

    /**
     * Inserta un nuevo producto.
     * @param array $datos Debe incluir al menos ['nombre','categoria_id','precio_base'].
     * @return int|false El nuevo ID, o false en error.
     */
    public function crear(array $datos) {
        // Validación mínima
        if (empty($datos['nombre']) || empty($datos['categoria_id']) || !isset($datos['precio_base'])) {
            return false;
        }

        $sql = "
            INSERT INTO productos
                (nombre, descripcion, precio_base, categoria_id, imagen)
            VALUES
                (?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        $desc  = $datos['descripcion']  ?? '';
        $img   = $datos['imagen']       ?? null;
        $stmt->bind_param(
            'ssdis',
            $datos['nombre'],
            $desc,
            $datos['precio_base'],
            $datos['categoria_id'],
            $img
        );
        if ($stmt->execute()) {
            $newId = $this->db->insert_id;
            $stmt->close();
            return $newId;
        }
        $stmt->close();
        return false;
    }

    /**
     * Actualiza un producto existente.
     * @param int   $id
     * @param array $datos Puede incluir ['nombre','descripcion','precio_base','categoria_id','imagen']
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool {
        // Validación mínima
        if (empty($datos['nombre']) || empty($datos['categoria_id']) || !isset($datos['precio_base'])) {
            return false;
        }

        $sets = [];
        $types = '';
        $values = [];

        // Campos fijos
        $fields = ['nombre','descripcion','precio_base','categoria_id','imagen'];
        foreach ($fields as $f) {
            if (array_key_exists($f, $datos)) {
                $sets[]    = "$f = ?";
                switch ($f) {
                    case 'precio_base':
                        $types .= 'd'; // double
                        break;
                    case 'categoria_id':
                        $types .= 'i'; // integer
                        break;
                    default:
                        $types .= 's';
                }
                $values[]  = $datos[$f];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE productos SET " . implode(', ', $sets) . " WHERE id = ?";
        $types .= 'i';
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        // bind all params dynamically
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Elimina un producto por ID.
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
