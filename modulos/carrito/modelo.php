<?php
// modulos/carrito/modelo.php

// Evitar acceso directo
if (!defined('ACCESO_PERMITIDO')) {
    die("Acceso directo no permitido");
}

class Carrito
{
    /**
     * Asegura que exista el array 'carrito' en la sesión.
     */
    public  static function inicializar()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }

    /**
     * Agrega un producto al carrito o suma cantidad si ya existía.
     *
     * @param int    $producto_id ID del producto
     * @param float  $precio      Precio **total** por unidad (ya calculado según duración/tipo)
     * @param int    $cantidad    Número de unidades a agregar
     * @param array  $opciones    Array asociativo con 'duracion' y 'tipo_plan'
     * @return bool
     */
    public static function agregar($producto_id, $precio, $cantidad = 1, $opciones = [])
    {
        self::inicializar();
        $producto_id = (int) $producto_id;
        $cantidad    = max(1, (int) $cantidad);
        $precio      = max(0, (float) $precio);
        $opciones    = is_array($opciones) ? $opciones : [];

        if (isset($_SESSION['carrito'][$producto_id])) {
            // Si ya existe, simplemente sumamos cantidades
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            // Nuevo producto en el carrito
            $_SESSION['carrito'][$producto_id] = [
                'id'       => $producto_id,
                'precio'   => $precio,
                'cantidad' => $cantidad,
                'opciones' => $opciones,  // guardamos duración y tipo de plan
            ];
        }

        return true;
    }

    /**
     * Actualiza la cantidad de un producto (o lo elimina si cantidad <= 0).
     *
     * @param int $producto_id
     * @param int $cantidad
     * @return bool
     */
    public static function actualizar($producto_id, $cantidad)
    {
        self::inicializar();
        $producto_id = (int) $producto_id;
        $cantidad    = (int) $cantidad;

        if (!isset($_SESSION['carrito'][$producto_id])) {
            return false;
        }

        if ($cantidad <= 0) {
            return self::eliminar($producto_id);
        }

        $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
        return true;
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param int $producto_id
     * @return bool
     */
    public static function eliminar($producto_id)
    {
        self::inicializar();
        $producto_id = (int) $producto_id;

        if (isset($_SESSION['carrito'][$producto_id])) {
            unset($_SESSION['carrito'][$producto_id]);
            return true;
        }
        return false;
    }

    /**
     * Vacía todo el carrito.
     *
     * @return bool
     */
    public static function vaciar()
    {
        self::inicializar();
        $_SESSION['carrito'] = [];
        return true;
    }

    /**
     * Devuelve el array completo del carrito.
     *
     * @return array
     */
    public static function obtener()
    {
        self::inicializar();
        return $_SESSION['carrito'];
    }

    /**
     * Cuenta el total de unidades en el carrito.
     *
     * @return int
     */
    public static function contar()
    {
        self::inicializar();
        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['cantidad'];
        }
        return $total;
    }

    /**
     * Suma el total monetario del carrito:
     * precio * cantidad para cada item.
     *
     * @return float
     */
    public static function calcularTotal()
    {
        self::inicializar();
        $total = 0.0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += ($item['precio'] * $item['cantidad']);
        }
        return $total;
    }
}
