<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './utils/codigoAleatorio.php';
require_once './utils/AutentificadorJWT.php';

// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/PedidoProductoController.php';

require_once './middlewares/LoggerMiddleware.php';
require_once './middlewares/AuthMiddleware.php';

// Instantiate App
$app = AppFactory::create();

//$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// =======================================================================================================//
// ============================= Rutas por Defecto ====================================================== //
// =======================================================================================================//
$app->get('[/]', function (Request $request, Response $response) {    
  $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
  
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
});

// =======================================================================================================//
// ============================= Rutas de Usuarios ====================================================== //
// =======================================================================================================//
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{nombre_usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('/{nombre_usuario}', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{id_usuario}', \UsuarioController::class . ':BorrarUno');
  });

// =======================================================================================================//
// ============================= Rutas de Productos ===================================================== //
// =======================================================================================================//
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{nombre_producto}', \ProductoController::class . ':TraerUno');
  $group->post('[/]', \ProductoController::class . ':CargarUno');
  $group->put('/{nombre_producto}', \ProductoController::class . ':ModificarUno');
  $group->delete('/{id_producto}', \ProductoController::class . ':BorrarUno');
});

// =======================================================================================================//
// ============================= Rutas de Mesas ========================================================= //
// =======================================================================================================//
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{codigo_unico}', \MesaController::class . ':TraerUno');
  $group->post('[/]', \MesaController::class . ':CargarUno');
  $group->put('/{codigo_unico}', \MesaController::class . ':ModificarUno');
  $group->delete('/{id_mesa}', \MesaController::class . ':BorrarUno');
});

// =======================================================================================================//
// ============================= Rutas de Pedido ======================================================== //
// =======================================================================================================//
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{codigo_alfanumerico}', \PedidoController::class . ':TraerUno');
  $group->post('[/]', \PedidoController::class . ':CargarUno');
});

// =======================================================================================================//
// ============================= Rutas de PedidoProducto ================================================ //
// =======================================================================================================//
$app->group('/pedidoProducto', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoProductoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoProductoController::class . ':TraerUno');
  $group->post('[/]', \PedidoProductoController::class . ':CargarUno');
});

// =======================================================================================================//
// ============================= Rutas de JWT =========================================================== //
// =======================================================================================================//
// JWT test
$app->group('/jwt', function (RouteCollectorProxy $group) {

  $group->post('/crearToken', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();

    $nombre_usuario = $parametros['nombre_usuario'];
    $clave = $parametros['clave'];

    $usuario = Usuario::obtenerUsuario($nombre_usuario);

    if($usuario != null && password_verify($clave,$usuario->clave))
    {
      $datos = array('id_usuario' => $usuario->id, 'tipo' => $usuario->tipo);
      $token = AutentificadorJWT::CrearToken($datos);
      $payload = json_encode(array('jwt' => $token));
    }
    else{
      $payload = json_encode(array('error' => "Usuario invalido"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/devolverPayLoad', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/devolverDatos', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/verificarToken', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    $esValido = false;

    try {
      AutentificadorJWT::verificarToken($token);
      $esValido = true;
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    if ($esValido) {
      $payload = json_encode(array('valid' => $esValido));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

// JWT en login
$app->group('/auth', function (RouteCollectorProxy $group) {

  $group->post('/login', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $contraseña = $parametros['contraseña'];

    if($usuario == 'prueba' && $contraseña == '1234'){ // EJEMPLO!!! Acá se deberia ir a validar el usuario contra la DB
      $datos = array('usuario' => $usuario);

      $token = AutentificadorJWT::CrearToken($datos);
      $payload = json_encode(array('jwt' => $token));
    } else {
      $payload = json_encode(array('error' => 'Usuario o contraseña incorrectos'));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

});


$app->run();
