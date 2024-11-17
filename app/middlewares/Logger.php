<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

class Logger
{
    public static function LogOperacion(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre_usuario = $parametros['nombre_usuario'];
        $clave = $parametros['clave'];
        
        $usuario = Usuario::obtenerUsuario($nombre_usuario);
        
        if($usuario != null && password_verify($clave,$usuario->clave))
        {
            $datos = array('id_usuario' => $usuario->id, 'tipo' => $usuario->tipo);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token, 'usuario_id' => $usuario->id, 'usuario_nombre' => $usuario->usuario));
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        }
        else{
            $payload = json_encode(array('error' => "Usuario invalido"));
            $response->getBody()->write($payload);
            return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
        }
    
        
    }
}