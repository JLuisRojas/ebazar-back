<?php
class PreguntaException extends Exception { }

class Pregunta { 
    private $_id_producto;
    private $_id_usuario;
    private $_pregunta;
    private $_respuesta;
    private $_fecha_pregunta;
    private $_fecha_respuesta;

    public function __construct($id_producto, $id_usuario, $pregunta, $respuesta,
                                $fecha_pregunta, $fecha_respuesta) {
        //
        $this->setIdProducto($id_producto);
        $this->setIdUsuario($id_usuario);
        $this->setPregunta($pregunta);
        $this->setRespuesta($respuesta);
        $this->setFechaPregunta($fecha_pregunta);
        $this->setFechaRespuesta($fecha_respuesta);
    }

    public function setIdProducto($id_producto) {
        $this->_id_producto = $id_producto;
    }
    public function getIdProducto() {
        return $this->_id_producto;
    }

    public function setIdUsuario($id_usuario) {
        $this->_id_usuario = $id_usuario;
    }

    public function getIdUsuario() {
        return $this->_id_usuario;
    }

    public function setPregunta($pregunta) {
        $this->_pregunta = $pregunta;
    }

    public function getPregunta() {
        return $this->_pregunta;
    }

    public function setRespuesta($respuesta) {
        $this->_respuesta = $respuesta;
    }

    public function getRespuesta() {
        return $this->_respuesta;
    }

    public function setFechaPregunta($fecha_pregunta) {
        $this->_fecha_pregunta = $fecha_pregunta;
    }

    public function getFechaPregunta() {
        return $this->_fecha_respuesta;
    }

    public function setFechaRespuesta($fecha_respuesta) {
        $this->_fecha_respuesta = $fecha_respuesta;
    }

    public function getFechaRespuesta() {
        return $this->_fecha_respuesta;
    }
}

?>