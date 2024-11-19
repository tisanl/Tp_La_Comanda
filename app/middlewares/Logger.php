<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Usuario as Usuario;

class Logger
{
    // Esta funcion estara seguida inmediatamente por GuardarFechaLog que guardara la fecha de loggin luego de hacer la validacion
    public static function LogOperacion(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre_usuario = $parametros['nombre_usuario'];
        $clave = $parametros['clave'];
        
        $usuario = Usuario::where('usuario', $nombre_usuario)->first();
        
        // Si el usuario es valido corroboro la contraseña
        if($usuario != null && password_verify($clave,$usuario->clave))
        {
            // Creo los datos para el jwt
            $datos = array('id_usuario' => $usuario->id, 'tipo' => $usuario->tipo, 'usuario' => $usuario->usuario,);
            $token = AutentificadorJWT::CrearToken($datos);
            // Se agrega a la respuesta el id y nombre del usuario para poder ser guardados en el archivo donde se registran los loggeos
            $payload = json_encode(array('jwt' => $token, 'usuario_id' => $usuario->id, 'usuario_nombre' => $usuario->usuario));

            // Escribo contenido de la respuesta y la retorno al middlware para registrar la fecha
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        }
        else{
            // El usuario no existe y retorno eso
            $payload = json_encode(array('error' => "Usuario invalido"));
            $response->getBody()->write($payload);
            return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
        }

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GuardarFechaLog(Request $request, RequestHandler $handler): Response
    {   
        // Continua al controller. Aca es como dejarlo pasar, voy a hacer la accion a la vuelta
        $response = $handler->handle($request);

        // Retomo a partir que se ejecuto Loggin y tomo el body de esa respuesta
        $existingContent = json_decode($response->getBody());

        // Evaluo si el cuerpo viene con el token, quiere decir que salo bien y tengo los datos para hacer el log
        if(isset($existingContent->jwt)){
            $cadena = $existingContent->usuario_id . "," . $existingContent->usuario_nombre . "," . date('Y-m-d H:i:s') . PHP_EOL; 

            $archivo = fopen("ingresos.csv", "a"); // append / agregar
            fwrite($archivo, $cadena);

            fclose($archivo);
            
            // Elimino lo que no quiero que se vea en el body
            unset($existingContent->usuario_id);
            unset($existingContent->usuario_nombre);

            // Creo una nueva respuesta y le guardo el token
            $response = new Response();
            $response->getBody()->write(json_encode(array('jwt' => $existingContent->jwt)));
        }

        // Salgo del middleware o con el mensaje de error de Loggin o el Token
        return $response->withHeader('Content-Type', 'application/json');
    }
}