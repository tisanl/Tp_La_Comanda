<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        // Creamos el usuario
        $usuario = new Usuario();
        $usuario->usuario = $parametros['usuario'];
        $usuario->clave = $parametros['clave'];
        $usuario->tipo = $parametros['tipo'];
        $usuario->estado = "activo";
        $usuario->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usuario = Usuario::obtenerUsuario($args['nombre_usuario']);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        parse_str(file_get_contents("php://input"), $putData);

        $usuario = Usuario::obtenerUsuario($args['nombre_usuario']);
        $usuario->usuario = $putData['usuario'];
        $usuario->clave = password_hash($putData['clave'], PASSWORD_DEFAULT);
        $usuario->tipo = $putData['tipo'];
        $usuario->estado = $putData['estado'];

        Usuario::modificarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_usuario = intval($args['id_usuario']);
        Usuario::borrarUsuario($id_usuario);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
