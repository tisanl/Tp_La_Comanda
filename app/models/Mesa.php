<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{   
    use SoftDeletes;
    protected $table = 'mesas';
    public $incrementing = true;

    protected $fillable = [
        'codigo_unico', 'estado'
    ];

    // Aca la relacion es inversa, le estoy diciendo que la mesa tiene una relacion con pedidos, que cada uno tiene el id de una mesa
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_mesa'); // 'id_mesa' es la clave foránea
    }

    //public $id;
    //public $codigo_unico;
    //public $estado;

    /*
    public function __construct(){}

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo_unico, estado) VALUES (:codigo_unico, :estado)");
        $consulta->bindValue(':codigo_unico', $this->codigo_unico, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_unico, estado FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($codigo_unico)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_unico, estado FROM mesas WHERE codigo_unico = :codigo_unico");
        $consulta->bindValue(':codigo_unico', $codigo_unico, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET codigo_unico = :codigo_unico, estado = :estado WHERE id = :id");
        $consulta->bindValue(':id', $mesa->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo_unico', $mesa->codigo_unico, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $mesa->estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarMesa($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET fecha_baja = :fecha_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }*/
}