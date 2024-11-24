<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        // Creamos el usuario
        $usuario = new Usuario();
        $usuario->usuario = $parametros['usuario'];
        $usuario->clave = password_hash($parametros['clave'], PASSWORD_DEFAULT);
        $usuario->tipo = $parametros['tipo'];
        $usuario->estado = "activo";
        $usuario->save();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::all();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usuario = Usuario::where('usuario', $args['nombre_usuario'])->first();
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        //Obtengo los parametros
        $parametros = $request->getParsedBody();

        $usuario = Usuario::find($parametros['id']);
        $usuario->usuario = $parametros['usuario'];
        $usuario->clave = password_hash($parametros['clave'], PASSWORD_DEFAULT);
        $usuario->tipo = $parametros['tipo'];
        $usuario->estado = $parametros['estado'];

        $usuario->save();

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_usuario = intval($args['id_usuario']);
        Usuario::find($id_usuario)->delete();

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function DescargarArchivoLog($request, $response, $args)
    {
        $archivo = fopen("ingresos.csv", "r");
        $contenido = fread($archivo, filesize("ingresos.csv"));
        fclose($archivo);

        $response->getBody()->write($contenido);
        return $response
          ->withHeader('Content-Type', 'text/csv')
          ->withHeader('Content-Disposition', 'attachment; filename="archivo.csv"');
    }
}
