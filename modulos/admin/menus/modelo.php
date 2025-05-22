<?php
// modulos/admin/menus/modelo.php

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

/**
 * Clase para leer/guardar ajustes genéricos en la tabla settings
 */
class Setting {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtener(string $key, $default = '') {
        $key = $this->conexion->real_escape_string($key);
        $sql = "SELECT `value` FROM `settings` WHERE `key` = '$key' LIMIT 1";
        $res = $this->conexion->query($sql);
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            return $row['value'];
        }
        return $default;
    }

    public function guardar(string $key, string $value): bool {
        $k = $this->conexion->real_escape_string($key);
        $v = $this->conexion->real_escape_string($value);
        $sql_check = "SELECT COUNT(*) AS total FROM `settings` WHERE `key` = '$k'";
        $res = $this->conexion->query($sql_check);
        $row = $res->fetch_assoc();
        if ($row['total'] > 0) {
            $sql = "UPDATE `settings` SET `value` = '$v' WHERE `key` = '$k'";
        } else {
            $sql = "INSERT INTO `settings` (`key`,`value`) VALUES ('$k','$v')";
        }
        return (bool)$this->conexion->query($sql);
    }

    public function eliminar(string $key): bool {
        $k = $this->conexion->real_escape_string($key);
        return (bool)$this->conexion->query("DELETE FROM `settings` WHERE `key` = '$k'");
    }

    public function obtenerTodos(): array {
        $sql = "SELECT * FROM `settings` ORDER BY `key`";
        $res = $this->conexion->query($sql);
        $out = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $out[$row['key']] = $row['value'];
            }
        }
        return $out;
    }
    
    /**
     * Obtiene un valor de configuración y lo decodifica como JSON
     */
    public function obtenerJSON(string $key, array $default = []): array {
        $valor = $this->obtener($key);
        if (!$valor) {
            return $default;
        }
        $resultado = json_decode($valor, true);
        return (is_array($resultado)) ? $resultado : $default;
    }
    
    /**
     * Guarda un array como JSON en la configuración
     */
    public function guardarJSON(string $key, array $value): bool {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE);
        return $this->guardar($key, $json);
    }
    
    /**
     * Obtiene la configuración completa de todos los menús
     */
    public function obtenerMenusConfig(): array {
        return $this->obtenerJSON('menus_config', [
            'posiciones' => [
                'header' => [],
                'footer' => [],
                'sidebar' => [],
                'mobile' => []
            ]
        ]);
    }
    
    /**
     * Guarda la configuración de un menú
     */
    public function guardarMenuConfig(int $menu_id, string $posicion, int $orden, int $parent_id = 0): bool {
        $config = $this->obtenerMenusConfig();
        
        // Verificar que la posición existe o crearla
        if (!isset($config['posiciones'][$posicion])) {
            $config['posiciones'][$posicion] = [];
        }
        
        // Actualizar o agregar la configuración del menú
        $encontrado = false;
        foreach ($config['posiciones'][$posicion] as &$menu) {
            if ($menu['id'] == $menu_id) {
                $menu['orden'] = $orden;
                $menu['parent_id'] = $parent_id;
                $encontrado = true;
                break;
            }
        }
        
        if (!$encontrado) {
            $config['posiciones'][$posicion][] = [
                'id' => $menu_id,
                'orden' => $orden,
                'parent_id' => $parent_id
            ];
        }
        
        // Reordenar los menús por orden
        usort($config['posiciones'][$posicion], function($a, $b) {
            return $a['orden'] - $b['orden'];
        });
        
        return $this->guardarJSON('menus_config', $config);
    }
    
    /**
     * Elimina un menú de la configuración
     */
    public function eliminarMenuConfig(int $menu_id): bool {
        $config = $this->obtenerMenusConfig();
        $modificado = false;
        
        foreach ($config['posiciones'] as $posicion => &$menus) {
            foreach ($menus as $key => $menu) {
                if ($menu['id'] == $menu_id) {
                    unset($menus[$key]);
                    $menus = array_values($menus); // Reindexar el array
                    $modificado = true;
                }
            }
        }
        
        if ($modificado) {
            return $this->guardarJSON('menus_config', $config);
        }
        
        return true;
    }
}

/**
 * Clase para CRUD de menús y sus items
 */
class Menu {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array {
        $sql = "SELECT * FROM `menus` ORDER BY `nombre`";
        $res = $this->conexion->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerPorId(int $id): ?array {
        $id = (int)$id;
        $sql = "SELECT * FROM `menus` WHERE `id` = $id LIMIT 1";
        $res = $this->conexion->query($sql);
        return ($res && $res->num_rows) ? $res->fetch_assoc() : null;
    }

    public function crear(string $nombre, string $descripcion = ''): int {
        $n = $this->conexion->real_escape_string($nombre);
        $d = $this->conexion->real_escape_string($descripcion);
        $sql = "INSERT INTO `menus` (`nombre`,`descripcion`) VALUES ('$n','$d')";
        if ($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        return 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = ''): bool {
        $id = (int)$id;
        $n  = $this->conexion->real_escape_string($nombre);
        $d  = $this->conexion->real_escape_string($descripcion);
        $sql = "UPDATE `menus` SET `nombre` = '$n', `descripcion` = '$d' WHERE `id` = $id";
        return (bool)$this->conexion->query($sql);
    }

    public function eliminar(int $id): bool {
        $id = (int)$id;
        // primero los items
        $this->conexion->query("DELETE FROM `menu_items` WHERE `menu_id` = $id");
        // luego el menú
        return (bool)$this->conexion->query("DELETE FROM `menus` WHERE `id` = $id");
    }

    public function obtenerItems(int $menu_id): array {
        $mid = (int)$menu_id;
        $sql = "SELECT * FROM `menu_items` WHERE `menu_id` = $mid ORDER BY `parent_id`,`orden`";
        $res = $this->conexion->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerArbolItems(int $menu_id): array {
        $flat = $this->obtenerItems($menu_id);
        $map  = [];
        $tree = [];
        foreach ($flat as $item) {
            $item['children'] = [];
            $map[$item['id']] = $item;
        }
        foreach ($map as $id => &$item) {
            if ($item['parent_id']) {
                if (isset($map[$item['parent_id']])) {
                    $map[$item['parent_id']]['children'][] = &$item;
                } else {
                    $tree[] = &$item;
                }
            } else {
                $tree[] = &$item;
            }
        }
        return $tree;
    }

    public function agregarItem(int $menu_id, string $titulo, string $url, int $parent_id = 0, int $orden = 0, string $clase = '', string $target = '_self'): int {
        $m = (int)$menu_id;
        $p = (int)$parent_id;
        $o = (int)$orden;
        $t = $this->conexion->real_escape_string($titulo);
        $u = $this->conexion->real_escape_string($url);
        $c = $this->conexion->real_escape_string($clase);
        $g = $this->conexion->real_escape_string($target);
        $sql = "INSERT INTO `menu_items` (`menu_id`,`parent_id`,`titulo`,`url`,`orden`,`clase`,`target`)
                VALUES ($m,$p,'$t','$u',$o,'$c','$g')";
        if ($this->conexion->query($sql)) {
            return $this->conexion->insert_id;
        }
        return 0;
    }

    public function actualizarItem(int $id, string $titulo, string $url, int $parent_id = 0, int $orden = 0, string $clase = '', string $target = '_self'): bool {
        $i = (int)$id;
        $p = (int)$parent_id;
        $o = (int)$orden;
        $t = $this->conexion->real_escape_string($titulo);
        $u = $this->conexion->real_escape_string($url);
        $c = $this->conexion->real_escape_string($clase);
        $g = $this->conexion->real_escape_string($target);
        $sql = "UPDATE `menu_items`
                SET `titulo`='$t',`url`='$u',`parent_id`=$p,`orden`=$o,`clase`='$c',`target`='$g'
                WHERE `id` = $i";
        return (bool)$this->conexion->query($sql);
    }

    public function eliminarItem(int $id): bool {
        $i = (int)$id;
        // reasignar hijos al padre
        $res = $this->conexion->query("SELECT `parent_id` FROM `menu_items` WHERE `id` = $i");
        $pid = ($res && $res->num_rows) ? $res->fetch_assoc()['parent_id'] : 0;
        $this->conexion->query("UPDATE `menu_items` SET `parent_id` = $pid WHERE `parent_id` = $i");
        // borrar el item
        return (bool)$this->conexion->query("DELETE FROM `menu_items` WHERE `id` = $i");
    }
    
    /**
     * Obtiene los menús asignados a una posición específica
     */
    public function obtenerMenusPorPosicion(string $posicion, $setting): array {
        $config = $setting->obtenerMenusConfig();
        
        if (!isset($config['posiciones'][$posicion])) {
            return [];
        }
        
        $menus = [];
        foreach ($config['posiciones'][$posicion] as $menuConfig) {
            $menu = $this->obtenerPorId($menuConfig['id']);
            if ($menu) {
                $menu['orden'] = $menuConfig['orden'];
                $menu['parent_id'] = $menuConfig['parent_id'];
                $menu['items'] = $this->obtenerArbolItems($menu['id']);
                $menus[] = $menu;
            }
        }
        
        // Ordenar menús por el campo orden
        usort($menus, function($a, $b) {
            return $a['orden'] - $b['orden'];
        });
        
        return $menus;
    }
    
    /**
     * Gestiona las relaciones jerárquicas entre múltiples menús
     */
    public function obtenerArbolMenus($setting): array {
        $menusPlanos = $this->obtenerTodos();
        $config = $setting->obtenerMenusConfig();
        $mapa = [];
        $arbol = [];
        
        // Prepara el mapa con todos los menús y su estructura básica
        foreach ($menusPlanos as $menu) {
            $menuId = $menu['id'];
            $menu['children'] = [];
            $menu['posicion'] = '';
            $menu['orden'] = 0;
            $menu['parent_id'] = 0;
            
            // Buscar la configuración del menú
            foreach ($config['posiciones'] as $posicion => $menus) {
                foreach ($menus as $menuConfig) {
                    if ($menuConfig['id'] == $menuId) {
                        $menu['posicion'] = $posicion;
                        $menu['orden'] = $menuConfig['orden'];
                        $menu['parent_id'] = $menuConfig['parent_id'];
                        break 2;
                    }
                }
            }
            
            $mapa[$menuId] = $menu;
        }
        
        // Construye el árbol jerárquico de menús
        foreach ($mapa as $id => &$menu) {
            if ($menu['parent_id']) {
                if (isset($mapa[$menu['parent_id']])) {
                    $mapa[$menu['parent_id']]['children'][] = &$menu;
                } else {
                    $arbol[] = &$menu;
                }
            } else {
                $arbol[] = &$menu;
            }
        }
        
        return $arbol;
    }
    
    /**
     * Renderiza un menú basado en su posición
     */
    public function renderizarMenu(int $menu_id, string $template = 'default'): string {
        $items = $this->obtenerArbolItems($menu_id);
        $output = '';
        
        switch ($template) {
            case 'header':
                $output = $this->renderizarMenuHeader($items);
                break;
            case 'footer':
                $output = $this->renderizarMenuFooter($items);
                break;
            case 'sidebar':
                $output = $this->renderizarMenuSidebar($items);
                break;
            case 'mobile':
                $output = $this->renderizarMenuMobile($items);
                break;
            default:
                $output = $this->renderizarMenuDefault($items);
        }
        
        return $output;
    }
    
    /**
     * Renderiza un menú en formato header
     */
    private function renderizarMenuHeader(array $items): string {
        $html = '<nav class="header-menu"><ul class="menu-principal">';
        
        foreach ($items as $item) {
            $html .= $this->renderizarItemHeader($item);
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Renderiza un item de menú en formato header
     */
    private function renderizarItemHeader(array $item): string {
        $clase = $item['clase'] ? ' class="' . $item['clase'] . '"' : '';
        $html = '<li' . $clase . '>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" target="' . $item['target'] . '">';
        $html .= htmlspecialchars($item['titulo']);
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul class="submenu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderizarItemHeader($child);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
     * Renderiza un menú en formato footer
     */
    private function renderizarMenuFooter(array $items): string {
        $html = '<nav class="footer-menu"><ul class="menu-links">';
        
        foreach ($items as $item) {
            $html .= $this->renderizarItemFooter($item);
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Renderiza un item de menú en formato footer
     */
    private function renderizarItemFooter(array $item): string {
        $clase = $item['clase'] ? ' class="' . $item['clase'] . '"' : '';
        $html = '<li' . $clase . '>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" target="' . $item['target'] . '">';
        $html .= htmlspecialchars($item['titulo']);
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul class="footer-submenu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderizarItemFooter($child);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
     * Renderiza un menú en formato sidebar
     */
    private function renderizarMenuSidebar(array $items): string {
        $html = '<nav class="sidebar-menu"><ul class="menu-vertical">';
        
        foreach ($items as $item) {
            $html .= $this->renderizarItemSidebar($item);
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Renderiza un item de menú en formato sidebar
     */
    private function renderizarItemSidebar(array $item): string {
        $clase = $item['clase'] ? ' class="' . $item['clase'] . '"' : '';
        $html = '<li' . $clase . '>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" target="' . $item['target'] . '">';
        $html .= htmlspecialchars($item['titulo']);
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul class="sidebar-submenu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderizarItemSidebar($child);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
     * Renderiza un menú en formato mobile
     */
    private function renderizarMenuMobile(array $items): string {
        $html = '<nav class="mobile-menu"><ul class="menu-movil">';
        
        foreach ($items as $item) {
            $html .= $this->renderizarItemMobile($item);
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Renderiza un item de menú en formato mobile
     */
    private function renderizarItemMobile(array $item): string {
        $clase = $item['clase'] ? ' class="' . $item['clase'] . '"' : '';
        $html = '<li' . $clase . '>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" target="' . $item['target'] . '">';
        $html .= htmlspecialchars($item['titulo']);
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<button class="submenu-toggle">+</button>';
            $html .= '<ul class="mobile-submenu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderizarItemMobile($child);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
     * Renderiza un menú en formato default
     */
    private function renderizarMenuDefault(array $items): string {
        $html = '<nav class="menu-default"><ul>';
        
        foreach ($items as $item) {
            $html .= $this->renderizarItemDefault($item);
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Renderiza un item de menú en formato default
     */
    private function renderizarItemDefault(array $item): string {
        $clase = $item['clase'] ? ' class="' . $item['clase'] . '"' : '';
        $html = '<li' . $clase . '>';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" target="' . $item['target'] . '">';
        $html .= htmlspecialchars($item['titulo']);
        $html .= '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul>';
            foreach ($item['children'] as $child) {
                $html .= $this->renderizarItemDefault($child);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }
    
    /**
     * Método auxiliar para cargar todos los menús de una posición
     */
    public function cargarMenusPorPosicion(string $posicion, $setting): string {
        $menus = $this->obtenerMenusPorPosicion($posicion, $setting);
        $output = '';
        
        foreach ($menus as $menu) {
            $output .= $this->renderizarMenu($menu['id'], $posicion);
        }
        
        return $output;
    }
}