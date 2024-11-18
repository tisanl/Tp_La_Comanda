<?php
require_once './models/PedidoProducto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\PedidoProducto as PedidoProducto;
use \App\Models\Producto as Producto;
use \App\Models\Pedido as Pedido;
use \App\Models\Usuario as Usuario;

use Slim\Psr7\Response;

class PedidoProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();

        // Creo el pedidoproducto
        $pedidoProducto = new PedidoProducto();

        // Busco el id del pedido
        $pedido = Pedido::find($parametros['id_pedido']);
        $pedidoProducto->id_pedido = $pedido->id;

        // Busco el producto
        $producto = Producto::find($parametros['id_producto']);
        $pedidoProducto->id_producto = $producto->id;
        $pedidoProducto->cantidad = $parametros['cantidad'];
        $pedidoProducto->precio = $producto->precio * $pedidoProducto->cantidad;

        $pedidoProducto->estado = "pendiente";

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

    public static function MostrarPendientesPorTipoEmpleado($request, $response, $args)
    {
        $usuario = Usuario::find(intval($args["id_usuario"]));

        if($usuario->tipo == "cocinero"){
          $lista_pendientes = PedidoProducto::where('estado', 'pendiente')
                                              ->whereHas('Producto', function ($query){
                                                $query->where('productos.zona_preparacion', 'cocina')->orWhere('productos.zona_preparacion', 'candy_bar');
                                              })->with(['Producto' => function ($query) {
                                                $query->where('zona_preparacion', 'cocina')
                                                      ->orWhere('zona_preparacion', 'candy_bar');
                                              }])->get();
        }
        else if($usuario->tipo == "bartender"){
          $lista_pendientes = PedidoProducto::where('estado', 'pendiente')->
                                              whereHas('Producto', function ($query){
                                                $query->where('productos.zona_preparacion', 'barra_tragos');
                                              })->get();
        }
        else if($usuario->tipo == "cervecero"){
          $lista_pendientes = PedidoProducto::where('estado', 'pendiente')->
                                              whereHas('Producto', function ($query){
                                                $query->where('productos.zona_preparacion', 'barra_chopera');
                                              })->get();
        }
        
        $payload = ["Tipo de empleado" => $usuario->tipo];

        foreach($lista_pendientes as $pedido_producto){
          // Acceder al Producto relacionado
          $producto = $pedido_producto->Producto;
          
          array_push($payload,array("Producto" => $producto->nombre,
                      "Id del PedidoProducto" => $pedido_producto->id,
                      "Cantidad" => $pedido_producto->cantidad,
                      "Zona de preparacion" => $producto->zona_preparacion,
                      "Estado" => $pedido_producto->estado));
        }

        $payload = json_encode($payload);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ActualizarEnPreparacion($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $pedidoProducto = PedidoProducto::find(intval($parametros['id']));
        if($pedidoProducto == null){
            $response->getBody()->write(json_encode(array('Error' => "No existe PedidoProducto con ese Id")));
            return $response->withHeader('Content-Type', 'application/json');
        }
        if($pedidoProducto->estado != "pendiente"){
          $response->getBody()->write(json_encode(array('Error' => "El PedidoProducto no esta pendiente de preparacion")));
          return $response->withHeader('Content-Type', 'application/json');
        }

        $usuario = $pedidoProducto->Usuario;
        $producto = $pedidoProducto->Producto;
        $pedido = $pedidoProducto->Pedido;

        if(($usuario->tipo == "cocinero" && ($producto->zona_preparacion == "cocina" || $producto->zona_preparacion == "candy_bar")) ||
            ($usuario->tipo == "bartender" && $producto->zona_preparacion == "barra_tragos") ||
            ($usuario->tipo == "cervecero" && $producto->zona_preparacion == "barra_chopera"))
        {
            $pedidoProducto->estado = "en_preparacion";
            $pedidoProducto->id_usuario = $usuario->id;

            $fecha = new DateTime('now');
            $fecha_estimada_listo = $fecha->modify('+'. $parametros['minutos_estimados_demora'] .' minutes');
            $pedidoProducto->fecha_estimada_listo = $fecha_estimada_listo->format('Y-m-d H:i:s');

            $pedidoProducto->save();

            if($pedido->fecha_estimada_listo == null || $pedidoProducto->fecha_estimada_listo > $pedido->fecha_estimada_listo){
              $pedido->fecha_estimada_listo = $pedidoProducto->fecha_estimada_listo;
              $pedido->save();
            }

            $payload = json_encode(array("Id PedidoPorducto:" => $pedidoProducto->id,
                                          "Nombre de Usuario:" => $usuario->usuario,
                                          "Nombre de Producto:" => $producto->nombre,
                                          "Fecha estimada listo:" => $pedidoProducto->fecha_estimada_listo,
                                          "Nombre de Usuario:" => $usuario->usuario));
        }
        else
        {
          $payload = json_encode(array("Error de permiso" => "El tipo de usuario no pertenece al area que hace estos pedidos"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
