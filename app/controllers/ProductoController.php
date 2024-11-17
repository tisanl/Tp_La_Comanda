<?php

require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Producto as Producto;

//class ProductoController extends Producto implements IApiUsable
class ProductoController implements IApiUsable
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
        $producto->save();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::all();

        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $producto = Producto::obtenerProductoNombre($args['nombre_producto']);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //Obtengo los parametros
        $parametros = $request->getParsedBody();
        
        $producto = Producto::find($parametros['id']);
        $producto->nombre = $parametros['nombre'];
        $producto->precio = floatval($parametros['precio']);
        $producto->zona_preparacion = $parametros['zona_preparacion'];

        $producto->save();

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_producto = intval($args['id_producto']);
        Producto::find($id_producto)->delete();

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodosZonaPreparacion($request, $response, $args)
    {
        $zona_preparacion = $args['zona_preparacion'];

        $lista = Producto::wherezona_preparacion($zona_preparacion)->get();

        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
