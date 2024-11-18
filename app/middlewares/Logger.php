<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Usuario as Usuario;

class Logger
{
    public static function LogOperacion(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre_usuario = $parametros['nombre_usuario'];
        $clave = $parametros['clave'];
        
        $usuario = Usuario::where('usuario', $nombre_usuario)->first();
        
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

    public static function GuardarFechaLog(Request $request, RequestHandler $handler): Response
    {   
        // Continua al controller. Aca es como dejarlo pasar, voy a hacer la accion a la vuelta
        $response = $handler->handle($request);

        // Retomo a partir que se ejecuto Loggin y tomo el body de esa respuesta
        $existingContent = json_decode($response->getBody());

        if(isset($existingContent->jwt)){
            $cadena = $existingContent->usuario_id . "," . $existingContent->usuario_nombre . "," . date('Y-m-d H:i:s') . PHP_EOL; 

            $archivo = fopen("ingresos.csv", "a"); // append / agregar
            fwrite($archivo, $cadena);

            fclose($archivo);

            unset($existingContent->usuario_id);
            unset($existingContent->usuario_nombre);

            $response = new Response();
            $response->getBody()->write(json_encode(array('jwt' => $existingContent->jwt)));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}