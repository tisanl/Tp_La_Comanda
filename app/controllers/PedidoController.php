<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Mesa as Mesa;
use \App\Models\Encuesta as Encuesta;

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

        // Busco la mesa y guardo el id
        $mesa = Mesa::where('codigo_unico', $parametros['codigo_mesa'])->first();
        
        if($mesa != null){
            $pedido->id_mesa = $mesa->id;
            $mesa->estado = 'cliente_esperando_pedido';
            $mesa->save();
    
            // Genero codigo alfanumerico
            $pedido->codigo_alfanumerico = generarCodigoAleatorio(5);
    
            // Estado por defecto
            $pedido->estado = "en_preparacion";
            
            // Guardo el nombre del cliente
            $pedido->nombre_cliente = $parametros['nombre_cliente'];
    
            // Creo la nueva ruta para la foto del pedido y la muevo a la carpeta
            $rutaDestino = __DIR__ . '/../fotosMesa/' . $pedido->codigo_alfanumerico . "_" . $pedido->nombre_cliente;
            $foto_mesa->moveTo($rutaDestino);
            $pedido->foto = $rutaDestino;
            
            // Creo el pedido
            $pedido->save();
    
            $payload = json_encode(array("mensaje" => "Pedido creado con exito", "Codigo alfanumerico del pedido" => $pedido->codigo_alfanumerico, "Id del pedido" => $pedido->id));
        }else{
            $payload = json_encode(array("mensaje" => 'No existe una mesa con ese codigo'));
        }

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

    public static function MostrarListosParaServir($request, $response, $args)
    {
        $listaPedidos = Pedido::where('estado', 'listo_para_servir')->get();

        $payload = json_encode($listaPedidos);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ActualizarEstadoEntregado($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = Pedido::find($parametros['id_pedido']);

        if($pedido != null && $pedido->estado == 'listo_para_servir'){
            $pedido->estado = 'entregado';
            
            $mesa = Mesa::find($pedido->id_mesa);
            $mesa->estado = 'cliente_comiendo';

            $pedido->save();
            $mesa->save();

            $payload = json_encode(array("Pedido" => $pedido,"Mesa" => $mesa));
        }  
        else{
          $payload = json_encode(array("Error" => "El pedido no existe o no esta listo para servir"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ObtenerEstadoPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = Pedido::where("codigo_alfanumerico", $parametros['codigo_pedido'])->first();
        
        if($pedido != null){
              $mesa = $pedido->Mesa;
              if($parametros['codigo_mesa'] == $mesa->codigo_unico)
              {
                  if($pedido->estado == "en_preparacion")
                  {
                      if($pedido->fecha_estimada_listo != null){
                        $fecha = DateTime::createFromFormat('Y-m-d H:i:s',$pedido->fecha_estimada_listo);
                        $payload = json_encode(array("Estado" => "El pedido aun se encuentra en prepracion",
                                                      "Hora estimada de entrega" => 'A las '. $fecha->format('H:i')));
                      }
                      else{
                        $payload = json_encode(array("Estado" => "El pedido aun se encuentra en prepracion"));
                      }
                  }
                  else if($pedido->estado == "listo_para_servir"){
                    $payload = json_encode(array("Estado" => "El pedido esta listo para servir"));
                  }
                  else if($pedido->estado == "entregado"){
                      $fecha = DateTime::createFromFormat('Y-m-d H:i:s',$pedido->fecha_entrega);
                      $payload = json_encode(array("Estado" => "El pedido ya fue entregado",
                                                    "Fecha de entrega" => 'El dia '. $fecha->format('d/m/Y') .' a las'. $fecha->format('H:i'),
                                                    "Total" => '$' . $pedido->valor_total));
                  }

              }
              else
              {
                  $payload = json_encode(array("Error" => "El codigo no corresponde a ninguna mesa"));
              }
        }  
        else
        {
          $payload = json_encode(array("Error" => "El codigo no corresponde a ningun pedido"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function SubirEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedido = Pedido::where("codigo_alfanumerico", $parametros['codigo_pedido'])->first();
        
        if($pedido != null){
              if($pedido->id_encuesta != null)
              {
                  $payload = json_encode(array("Error" => "El pedido ya tiene una encuesta asociada"));
                  $response->getBody()->write($payload);
                  return $response
                    ->withHeader('Content-Type', 'application/json');
              }
              $mesa = $pedido->Mesa;
              if($parametros['codigo_mesa'] == $mesa->codigo_unico)
              {
                  if($pedido->estado == "entregado")
                  {
                      if(strlen($parametros['breve_descripcion'])<66){
                        $request = $request->withAttribute('mesa', $mesa);
                        $encuesta = EncuestaController::RegistrarUno($request);

                        $pedido->id_encuesta = $encuesta->id;
                        $pedido->save();

                        $payload = json_encode(array("Estado" => "Se registro la encuesta y se asocio al pedido"));
                      }
                    else $payload = json_encode(array("Estado" => "La descripcion no puede suuperar los 66 caracteres"));
                  }
                  else $payload = json_encode(array("Estado" => "El pedido no fue entregado aun"));
              }
              else $payload = json_encode(array("Error" => "El codigo no corresponde a ninguna mesa"));
        }  
        else
        {
          $payload = json_encode(array("Error" => "El codigo no corresponde a ningun pedido"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
