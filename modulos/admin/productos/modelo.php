<?php
// modulos/admin/productos/modelo.php

class Producto {
    /** @var mysqli */
    private $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

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

    public function crear(array $datos) {
        if (empty($datos['nombre']) || empty($datos['categoria_id']) || !isset($datos['precio_base'])) {
            return false;
        }

        $sql = "
            INSERT INTO productos
                (nombre, descripcion, precio_base, precio_completo, precio_3_meses, precio_12_meses, categoria_id, stock, imagen)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $desc = $datos['descripcion'] ?? '';
        $img = $datos['imagen'] ?? null;
        $stock = $datos['stock'] ?? 0;
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'ssddddiis',
            $datos['nombre'],
            $desc,
            $datos['precio_base'],
            $datos['precio_completo'],
            $datos['precio_3_meses'],
            $datos['precio_12_meses'],
            $datos['categoria_id'],
            $stock,
            $img
        );

        if ($stmt->execute()) {
            $newId = $this->db->insert_id;
            $stmt->close();
            return $newId;
        }
        
        // Para debugging
        error_log("Error al crear producto: " . $this->db->error);
        $stmt->close();
        return false;
    }

    public function actualizar(int $id, array $datos): bool {
        if (empty($datos['nombre']) || empty($datos['categoria_id']) || !isset($datos['precio_base'])) {
            return false;
        }

        $sets = [];
        $types = '';
        $values = [];

        $fields = [
            'nombre',
            'descripcion',
            'precio_base',
            'precio_completo',
            'precio_3_meses',
            'precio_12_meses',
            'categoria_id',
            'stock',
            'imagen'
        ];
        
        foreach ($fields as $f) {
            if (array_key_exists($f, $datos)) {
                $sets[] = "$f = ?";
                switch ($f) {
                    case 'precio_base':
                    case 'precio_completo':
                    case 'precio_3_meses':
                    case 'precio_12_meses':
                        $types .= 'd';
                        break;
                    case 'categoria_id':
                    case 'stock':
                        $types .= 'i';
                        break;
                    default:
                        $types .= 's';
                }
                $values[] = $datos[$f];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE productos SET " . implode(', ', $sets) . " WHERE id = ?";
        $types .= 'i';
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        
        // Para debugging
        if (!$ok) {
            error_log("Error al actualizar producto: " . $this->db->error);
        }
        
        $stmt->close();
        return $ok;
    }

    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function buscarPorNombre(string $termino): array {
        $sql = "
            SELECT p.*, c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.nombre LIKE ?
            ORDER BY p.id DESC
        ";
        $like = '%' . $termino . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }
    
    public function buscar(int $categoriaId = 0, string $buscar = ''): array {
    $sql = "
        SELECT p.*, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE 1
    ";
    $params = [];
    $types  = '';

    if ($categoriaId > 0) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoriaId;
        $types .= 'i';
    }

    if ($buscar !== '') {
        $sql .= " AND p.nombre LIKE ?";
        $params[] = '%' . $buscar . '%';
        $types .= 's';
    }

    $sql .= " ORDER BY p.id DESC";
    $stmt = $this->db->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}


}