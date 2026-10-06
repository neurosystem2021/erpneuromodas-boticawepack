<?php

/**
 * Prepara el texto de cada articulo para los formatos HTML de boleta y factura.
 * La descripcion pertenece a la linea del producto, no al metodo de pago.
 */
function fn_formatear_productos_impresion(array $productos)
{
    foreach ($productos as &$producto) {
        $codigo = '';
        if (isset($producto['CodigoBarra'])) {
            $codigo = trim((string) $producto['CodigoBarra']);
        } elseif (isset($producto['Codigo'])) {
            $codigo = trim((string) $producto['Codigo']);
        }

        $nombre = isset($producto['Producto'])
            ? trim((string) $producto['Producto'])
            : '';
        $descripcion = isset($producto['Descripcion'])
            ? preg_replace('/\s+/u', ' ', trim((string) $producto['Descripcion']))
            : '';
        $descripcion = trim($descripcion, " \t\n\r\0\x0B()");

        $detalle = $codigo !== '' ? $codigo . ' - ' . $nombre : $nombre;
        if ($descripcion !== '') {
            $detalle .= ' (' . $descripcion . ')';
        }

        $producto['Producto'] = $detalle;
        // Evita que las plantillas antiguas vuelvan a imprimir la descripcion.
        $producto['Descripcion'] = '';
    }
    unset($producto);

    return $productos;
}

