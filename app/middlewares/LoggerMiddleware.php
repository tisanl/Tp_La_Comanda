<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
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
/*
    public function VerificarRol(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];

        if ($sector === 'admin') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Admin'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }*/
}