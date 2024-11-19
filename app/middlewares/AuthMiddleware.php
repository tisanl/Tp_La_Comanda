<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Usuario as Usuario;

class AuthMiddleware
{
    public $usuarios_admitidos = [];

    public function __construct($usuarios_admitidos) {
        $this->usuarios_admitidos = $usuarios_admitidos;
    }

    /**
     * Example middleware invokable class
     *
     * @param  Request  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            
            $data = AutentificadorJWT::ObtenerData($token);

            $usuario = Usuario::find($data->id_usuario);

            if(in_array($usuario->tipo,$this->usuarios_admitidos)){
                // Guardo este atributo en el request
                $request = $request->withAttribute('usuario', $usuario);
                // Del autentificador pasa a la siguiente capa
                $response = $handler->handle($request);
            }
            else{
                $response = new Response();
                $payload = json_encode(array('Error' => 'El usuario no tiene permiso para esta funcion'));
                $response->getBody()->write($payload);
            }
            
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        
        // Esto es despues de que pegue la vuelta
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GuardarLogPostAuth(Request $request, RequestHandler $handler): Response
    {   
        $usuario = $request->getAttribute('usuario');

        $cadena = $usuario->id . "," . $usuario->usuario . "," . date('Y-m-d H:i:s') . PHP_EOL;

        $archivo = fopen("ingresos.csv", "a"); // append / agregar
        fwrite($archivo, $cadena);

        fclose($archivo);

        // Guardo la fecha y paso a la siguiente capa
        $response = $handler->handle($request);
        
        // Esto es despues de que pegue la vuelta
        return $response->withHeader('Content-Type', 'application/json');
    }
}