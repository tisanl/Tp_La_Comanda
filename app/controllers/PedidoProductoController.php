<?php
require_once './models/PedidoProducto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\PedidoProducto as PedidoProducto;
use \App\Models\Producto as Producto;
use \App\Models\Pedido as Pedido;

class PedidoProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();

        // Creo el pedidoproducto
        $pedidoProducto = new PedidoProducto();

        // Busco el id del pedido
        $pedido = Pedido::where("codigo_alfanumerico", $parametros['codigo_alfanumerico']);
        $pedidoProducto->id_pedido = $pedido->id;

        // Busco el producto
        $producto = Producto::obtenerProductoNombre($parametros['nombre_producto']);
        $pedidoProducto->id_producto = $producto->id;
        $pedidoProducto->cantidad = $parametros['cantidad'];
        $pedidoProducto->precio = $producto->precio * $pedidoProducto->cantidad;

        $pedidoProducto->estado = "para_preparar";

        $pedidoProducto->save();

        $payload = json_encode(array("mensaje" => "PedidoProducto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = PedidoProducto::all();
        $payload = json_encode(array("listaPedidoProducto" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $pedidoProducto = PedidoProducto::find($args['id']);
        $payload = json_encode($pedidoProducto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $pedidoProducto = PedidoProducto::find($parametros['id']);
        $pedidoProducto->estado = $parametros['estado'];
        $pedidoProducto->fecha_estimada_listo = $parametros['fecha_estimada_listo'];

        $pedidoProducto->save();

        $payload = json_encode(array("mensaje" => "PedidoProducto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_pedido_producto = intval($args['id_pedido_producto']);
        PedidoProducto::find($id_pedido_producto)->delete();

        $payload = json_encode(array("mensaje" => "PedidoProducto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
