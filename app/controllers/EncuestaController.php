<?php

require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

use TCPDF;
use \App\Models\Encuesta as Encuesta;

class EncuestaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();

        // Creo la encuesta
        $encuesta = new Encuesta();
        $encuesta->id_mesa = $parametros['id_mesa'];
        $encuesta->valoracion_restaurante = $parametros['valoracion_restaurante'];
        $encuesta->valoracion_mozo = $parametros['valoracion_mozo'];
        $encuesta->valoracion_cocinero = $parametros['valoracion_cocinero'];
        $encuesta->breve_descripcion = $parametros['breve_descripcion'];
        $encuesta->save();

        $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Encuesta::all();

        $payload = json_encode(array("listaEncuestas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $encuesta = Encuesta::find($args['id_encuesta'])->first();
        $payload = json_encode($encuesta);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //Obtengo los parametros
        $parametros = $request->getParsedBody();
        
        $encuesta = Encuesta::find($parametros['id']);
        $encuesta->id_mesa = $parametros['id_mesa'];
        $encuesta->valoracion_restaurante = floatval($parametros['valoracion_restaurante']);
        $encuesta->valoracion_mozo = $parametros['valoracion_mozo'];
        $encuesta->valoracion_cocinero = $parametros['valoracion_cocinero'];
        $encuesta->breve_descripcion = $parametros['breve_descripcion'];

        $encuesta->save();

        $payload = json_encode(array("mensaje" => "Encuesta modificada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id_encuesta = intval($args['id_encuesta']);
        Encuesta::find($id_encuesta)->delete();

        $payload = json_encode(array("mensaje" => "Encuesta borrada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function RegistrarUno($request)
    {
        // Obtengo los parametros
        $parametros = $request->getParsedBody();
        $mesa = $request->getAttribute('mesa');

        // Creo la encuesta
        $encuesta = new Encuesta();
        $encuesta->id_mesa = $mesa->id;
        $encuesta->valoracion_restaurante = $parametros['valoracion_restaurante'];
        $encuesta->valoracion_mozo = $parametros['valoracion_mozo'];
        $encuesta->valoracion_cocinero = $parametros['valoracion_cocinero'];
        $encuesta->breve_descripcion = $parametros['breve_descripcion'];
        $encuesta->save();

        return $encuesta;
    }

    public static function BuscarMejoresComentarios($request, $response, $args)
    {
        $listaEncuestas = Encuesta::all();

        $encuestaMasValoracion = null;
        $sumaTotalValoracion = 0;

        foreach($listaEncuestas as $encuesta){
          $aux = $encuesta->valoracion_restaurante + $encuesta->valoracion_mozo + $encuesta->valoracion_cocinero;
          if($aux > $sumaTotalValoracion){
              $encuestaMasValoracion = $encuesta;
              $sumaTotalValoracion = $aux;
          }
        }

        $payload = EncuestaController::DevolverEncuestaPdf($encuestaMasValoracion, "Encuesta con mejores comentarios");
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/pdf')
          ->withHeader('Content-Disposition', 'attachment; filename="archivo.pdf"');
    }


    public static function DevolverEncuestaPdf($encuesta, $titulo){
        // Crear una nueva instancia de TCPDF
        $pdf = new TCPDF();
    
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(true, 20);
    
        // Agregar una página
        $pdf->AddPage();
    
        // Configuración general de fuente
        $pdf->SetFont('helvetica', 'B', 16); // Negrita, tamaño 16
    
        // Título
        $pdf->Cell(0, 10, $titulo, 0, 1, 'C');
        $pdf->Ln(10); // Salto de línea

        // Formato del texto
        $texto = "Id: $encuesta->id\n";
        $texto .= "Valoración del restaurante: $encuesta->valoracion_restaurante\n";
        $texto .= "Valoración del mozo: $encuesta->valoracion_mozo\n";
        $texto .= "Valoración del cocinero: $encuesta->valoracion_cocinero\n";
        $texto .= "Breve descripcion: $encuesta->breve_descripcion\n";

        // Escribir el texto en el PDF
        $pdf->Write(10, $texto);
    
        // Generar el PDF como salida
        return $pdf->Output('', 'S'); // 'S' para devolver el PDF como string
    }
}
