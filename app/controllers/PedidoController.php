<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Mesa as Mesa;

class PedidoController implements IApiUsable
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
        $pedido->codigo_alfanumerico = generarCodigoAleatorio(5);

        // Estado por defecto
        $pedido->estado = "en_preparacion";

        // Busco la mesa y guardo el id
        $mesa = Mesa::where('codigo_unico', $parametros['codigo_mesa'])->first();
        $pedido->id_mesa = $mesa->id;
        
        // Guardo el nombre del cliente
        $pedido->nombre_cliente = $parametros['nombre_cliente'];

        // Creo la nueva ruta para la foto del pedido y la muevo a la carpeta
        $rutaDestino = __DIR__ . '/../fotosMesa/' . $pedido->codigo_alfanumerico . "_" . $pedido->nombre_cliente;
        $foto_mesa->moveTo($rutaDestino);
        $pedido->foto = $rutaDestino;
        
        // Creo el pedido
        $pedido->save();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito", "Codigo alfanumerico del pedido" => $pedido->codigo_alfanumerico));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $codigo_alfanumerico = $args['codigo_alfanumerico'];
        $pedido = Pedido::where("codigo_alfanumerico", $codigo_alfanumerico);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = Pedido::find($parametros['id']);

        if($parametros['fecha_estimada_listo'] != null)
        //$pedido->fecha_estimada_listo = DateTime::createFromFormat('Y-m-d H:i:s', $parametros['fecha_estimada_listo']);
        $pedido->fecha_estimada_listo = $parametros['fecha_estimada_listo'];
        
        if($parametros['estado'] != null)
        $pedido->estado = $parametros['estado'];

        // Busco la mesa y guardo el id
        if($parametros['codigo_mesa'] != null){
          $mesa = Mesa::where('codigo_unico', $parametros['codigo_mesa'])->first();
          $pedido->id_mesa = $mesa->id;
        }

        if($parametros['nombre_cliente'] != null)
        $pedido->nombre_cliente = $parametros['nombre_cliente'];

        if($parametros['id_encuesta'] != null)
        $pedido->id_encuesta = $parametros['id_encuesta'];

        $pedido->save();

        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $pedidoId = intval($args['pedidoId']);
        Pedido::find($pedidoId);

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
