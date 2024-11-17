<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidarBodyMiddleware
{
    private $campos_a_validar = [];

    public function __construct($camposAValidar) {
        $this->campos_a_validar = $camposAValidar;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response 
    {
        $body = $request->getParsedBody();

        foreach ($this->campos_a_validar as $key => $value) {
            if(!isset($body[$value])){
                $response = new Response();
                $response->getBody()->write(json_encode(array('error' => "datos incorrectos, falta " . $value)));
                return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
            }
        }
        
        // Valido todo primero y despues voy al siguiente middelware o a la funcion principal
        $response = $handler->handle($request);

        return $response; 
    }
}