<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();
        $foto_mesa = $archivos['foto'];
        
        // Creo el pedido
        $pedido = new Pedido();

        // Genero codigo alfanumerico
        $pedido->codigo_alfanumerico = generarCodigoAleatorio(7);

        // Obtengo la fecha de ahora y la guardo
        $fecha = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
        $pedido->fecha = $fecha->format('Y-m-d H:i:s');

        // Estado por defecto
        $pedido->estado = "en_preparacion";

        // Busco la mesa y guardo el id
        $mesa = Mesa::obtenerMesa($parametros['codigo_mesa']);
        $pedido->id_mesa = $mesa->id;
        
        // Guardo el nombre del cliente
        $pedido->nombre_cliente = $parametros['nombre_cliente'];

        // Creo la nueva ruta para la foto del pedido y la muevo a la carpeta
        $rutaDestino = __DIR__ . '/../fotosMesa/' . $pedido->codigo_alfanumerico . "_" . $pedido->nombre_cliente;
        $foto_mesa->moveTo($rutaDestino);
        $pedido->foto = $rutaDestino;
        
        // Creo el pedido
        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $codigo_alfanumerico = $args['codigo_alfanumerico'];
        $pedido = Pedido::obtenerPedido($codigo_alfanumerico);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        parse_str(file_get_contents("php://input"), $putData);
        
        $pedido = Pedido::obtenerPedido($args['codigo_alfanumerico']);
        
        if($putData['tiempo_preparacion'] != null)
        //$pedido->tiempo_preparacion = DateTime::createFromFormat('Y-m-d H:i:s', $putData['tiempo_preparacion']);
        $pedido->tiempo_preparacion = $putData['tiempo_preparacion'];
        
        if($putData['estado'] != null)
        $pedido->estado = $putData['estado'];

        // Busco la mesa y guardo el id
        if($putData['codigo_mesa'] != null){
          $mesa = Mesa::obtenerMesa($putData['codigo_mesa']);
          $pedido->id_mesa = $mesa->id;
        }

        if($putData['nombre_cliente'] != null)
        $pedido->nombre_cliente = $putData['nombre_cliente'];

        if($putData['id_encuesta'] != null)
        $pedido->id_encuesta = $putData['id_encuesta'];

        Pedido::modificarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $productoId = intval($args['productoId']);
        Producto::borrarProducto($productoId);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
