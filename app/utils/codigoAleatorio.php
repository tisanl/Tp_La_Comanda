<?php

function generarCodigoAleatorio($longitud){
    $caracteres_permitidos = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($caracteres_permitidos), 0, $longitud);
}