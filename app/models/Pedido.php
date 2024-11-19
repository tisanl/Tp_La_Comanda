<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;
    protected $table = 'pedidos';
    public $incrementing = true;

    protected $fillable = [
        'codigo_alfanumerico', 'fecha_estimada_listo', 'fecha_entrega', 'valor_total', 
        'estado', 'id_mesa', 'nombre_cliente', 'foto', 'id_encuesta'
    ];

    public function Mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa'); // Aca se declara y por parametro se le envia la columna que tiene la clave foranea
    }
    /*
    public $id;
    public $codigo_alfanumerico;
    public $fecha;
    public $tiempo_preparacion;
    public $estado;
    public $id_mesa;
    public $nombre_cliente;
    public $foto;
    public $id_encuesta;
    */

    /*
    public function __construct(){}

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta
        ("INSERT INTO pedidos (codigo_alfanumerico, fecha, estado, id_mesa, nombre_cliente, foto) 
                        VALUES (:codigo_alfanumerico, :fecha, :estado, :id_mesa, :nombre_cliente, :foto)");

        $consulta->bindValue(':codigo_alfanumerico', $this->codigo_alfanumerico, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_alfanumerico, fecha, tiempo_preparacion, estado, id_mesa, nombre_cliente, foto, id_encuesta FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($codigo_alfanumerico)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_alfanumerico, fecha, tiempo_preparacion, estado, id_mesa, nombre_cliente, foto, id_encuesta FROM pedidos WHERE codigo_alfanumerico = :codigo_alfanumerico");
        $consulta->bindValue(':codigo_alfanumerico', $codigo_alfanumerico, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos 
        SET tiempo_preparacion = :tiempo_preparacion, estado = :estado, id_mesa = :id_mesa, nombre_cliente = :nombre_cliente, id_encuesta = :id_encuesta
        WHERE id = :id");

        $consulta->bindValue(':id', $pedido->id);
        $consulta->bindValue(':tiempo_preparacion', $pedido->tiempo_preparacion);
        $consulta->bindValue(':estado', $pedido->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $pedido->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':nombre_cliente', $pedido->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id_encuesta', $pedido->id_encuesta, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET fecha_baja = :fecha_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }*/
}