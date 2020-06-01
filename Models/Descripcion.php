<?php
class DescripcionException extends Exception { }

class Descripcion { 
    private $_id_producto;
    private $_id_usuario;
    private $_cantidad;

    public function __construct($id_producto, $id_usuario, $cantidad) {
        $this->setIdProducto($id_producto);
        $this->setIdUsuario($id_usuario);
        $this->setCantidad($cantidad);
    }

    public function getArray() {
        $pregunta = array();
    
        $pregunta['id_producto'] = $this->getIdProducto();
        $pregunta['id_usuario'] = $this->getIdUsuario();
        $pregunta['cantidad'] = $this->getCantidad();
    
        return $pregunta;
    }

    public static function fromArray($arr) {
        return new Pregunta($arr['id_producto'], $arr['id_usuario'], $arr['cantidad']);
      }

    public function setIdProducto($id_producto) {
        if($id_producto === null || !is_numeric($id_producto) || !is_integer($id_producto) || $id_producto <= 0 ||  $id_producto >= 2137483647){
            throw new DescripcionException("Error en el id del producto de la descripcion");
        }
        $this->_id_producto = $id_producto;
    }
    public function getIdProducto() {
        return $this->_id_producto;
    }

    public function setIdUsuario($id_usuario) {
        if($id_usuario === null || !is_numeric($id_usuario) || !is_integer($id_usuario) || $id_usuario <= 0 ||  $id_usuario >= 2137483647){
            throw new DescripcionException("Error en el id del usuario de la descripcion");
        }

        $this->_id_usuario = $id_usuario;
    }

    public function getIdUsuario() {
        return $this->_id_usuario;
    }

    public function setCantidad($cantidad) {
        if($cantidad === null || !is_numeric($cantidad) || !is_integer($cantidad) || $cantidad <= 0 ||  $cantidad >= 2137483647){
            throw new DescripcionException("Error en la cantidad de la descripcion");
        }

        $this->_cantidad = $cantidad;
    }

    public function getCantidad() {
        return $this->_cantidad;
    }
}
?>