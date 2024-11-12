<?php
require_once './models/PedidoProducto.php';
require_once './interfaces/IApiUsable.php';

class PedidoProductoController extends PedidoProducto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();

        // Creo el pedidoproducto
        $pedidoProducto = new PedidoProducto();

        // Busco el id del pedido
        $pedido = Pedido::obtenerPedido($parametros['codigo_alfanumerico']);
        $pedidoProducto->id_pedido = $pedido->id;

        // Busco el producto
        $producto = Producto::obtenerProducto($parametros['nombre_producto']);
        $pedidoProducto->id_producto = $producto->id;
        $pedidoProducto->cantidad = $parametros['cantidad'];
        $pedidoProducto->precio = $producto->precio * $pedidoProducto->cantidad;

        $pedidoProducto->estado = "para_preparar";

        $pedidoProducto->crearPedidoProducto();

        $payload = json_encode(array("mensaje" => "PedidoProducto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = PedidoProducto::obtenerTodos();
        $payload = json_encode(array("listaPedidoProducto" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $pedidoProducto = PedidoProducto::obtenerPedidoProducto($args['id']);
        $payload = json_encode($pedidoProducto);

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
