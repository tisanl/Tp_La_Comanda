<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa as Mesa;
use \App\Models\Pedido as Pedido;

class MesaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = new Mesa();
        $mesa->codigo_unico = generarCodigoAleatorio(5);
        $mesa->estado = "libre";
        $mesa->save();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::all();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $mesa = Mesa::where('codigo_unico', $args['codigo_unico'])->first();
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $mesa = Mesa::find($parametros['id']);
        $mesa->codigo_unico = $parametros['codigo_unico'];
        $mesa->estado = $parametros['estado'];
        
        $mesa->save();

        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_mesa = intval($args['id_mesa']);
        Mesa::find($id_mesa)->delete();

        $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ActualizarEstadoClientePagando($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::find($parametros['id_mesa']);

        if($mesa != null && $mesa->estado == 'cliente_comiendo'){
            $mesa->estado = 'cliente_pagando';
            $mesa->save();

            $payload = json_encode(array("Mesa" => $mesa));
        }  
        else{
          $payload = json_encode(array("Error" => "La mesa no existe o no estan comiendo"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ActualizarEstadoCerrada($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::find($parametros['id_mesa']);

        if($mesa != null && $mesa->estado == 'libre'){
            $mesa->estado = 'cerrada';
            $mesa->save();

            $payload = json_encode(array("Mesa" => $mesa));
        }  
        else{
          $payload = json_encode(array("Error" => "La mesa no existe o aun no esta libre"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ActualizarEstadoLibre($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::find($parametros['id_mesa']);

        if($mesa != null && $mesa->estado == 'cliente_pagando'){
            $mesa->estado = 'libre';
            $mesa->save();

            $payload = json_encode(array("Mesa" => $mesa));
        }  
        else{
          $payload = json_encode(array("Error" => "La mesa no existe o no esta cerrada"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function BuscarMasUsada($request, $response, $args)
    {
        $mesa = Mesa::withCount('pedidos')->orderByDesc('pedidos_count')->first();
      
        if($mesa != null)
        {
            $payload = json_encode(array("Id" => $mesa->id,
                                          "Codigo unico" => $mesa->codigo_unico));
        }
        else{
            $payload = json_encode(array("Error" => "La mesa no existe o no esta cerrada"));
        }
        
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
