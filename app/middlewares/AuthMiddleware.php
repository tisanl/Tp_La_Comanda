<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Usuario as Usuario;

class AuthMiddleware
{
    public $usuarios_admitidos = [];
    public $motivo = '';

    public function __construct($datos) {
        $this->usuarios_admitidos = $datos['usuarios_admitidos'];
        $this->motivo = $datos['motivo'];
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
                $request = $request->withAttribute('motivo', $this->motivo);

                // Del autentificador pasa a la siguiente capa
                $response = $handler->handle($request);                
            }
            else{
                $response = new Response();
                $payload = json_encode(array('Error' => 'El usuario no tiene permiso para esta funcion'));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        // Esto es despues de que pegue la vuelta
        return $response;
    }

    public function GuardarLogPostAuth(Request $request, RequestHandler $handler): Response
    {   
        $usuario = $request->getAttribute('usuario');
        $motivo = $request->getAttribute('motivo');

        $cadena = $usuario->id . "," . $usuario->usuario . "," . date('Y-m-d H:i:s') . "," . $motivo. PHP_EOL;

        $archivo = fopen(PATH_INGRESOS, "a"); // append / agregar
        fwrite($archivo, $cadena);

        fclose($archivo);
        
        // Guardo la fecha y paso a la siguiente capa
        $response = $handler->handle($request);

        return $response;
        
    }
}