<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();

        // Creo el producto
        $producto = new Producto();
        $producto->nombre = $parametros['nombre'];
        $producto->precio = floatval($parametros['precio']);
        $producto->zona_preparacion = $parametros['zona_preparacion'];
        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $producto = Producto::obtenerProducto($args['nombre_producto']);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        parse_str(file_get_contents("php://input"), $putData);
        
        $producto = Producto::obtenerProducto($args['nombre_producto']);
        $producto->nombre = $putData['nombre'];
        $producto->precio = floatval($putData['precio']);
        $producto->zona_preparacion = $putData['zona_preparacion'];

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_producto = intval($args['id_producto']);
        Producto::borrarProducto($id_producto);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
