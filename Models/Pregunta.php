<?php
class PreguntaException extends Exception { }

class Pregunta { 
    private $_id;
    private $_id_producto;
    private $_id_usuario;
    private $_pregunta;
    private $_respuesta;
    private $_fecha_pregunta;
    private $_fecha_respuesta;

    public function __construct($id, $id_producto, $id_usuario, $pregunta, $respuesta,
                                $fecha_pregunta, $fecha_respuesta) {
        //
        $this->setId($id);
        $this->setIdProducto($id_producto);
        $this->setIdUsuario($id_usuario);
        $this->setPregunta($pregunta);
        $this->setRespuesta($respuesta);
        $this->setFechaPregunta($fecha_pregunta);
        $this->setFechaRespuesta($fecha_respuesta);
    }

    public function getArray() {
        $pregunta = array();
        
        $pregunta['id'] = $this->getId();
        $pregunta['id_producto'] = $this->getIdProducto();
        $pregunta['id_usuario'] = $this->getIdUsuario();
        $pregunta['pregunta'] = $this->getPregunta();
        $pregunta['respuesta'] = $this->getRespuesta();
        $pregunta['fecha_pregunta'] = $this->getFechaPregunta();
        $pregunta['fecha_respuesta'] = $this->getFechaRespuesta();
    
        return $pregunta;
    }

    public static function fromArray($arr) {
        return new Pregunta($arr['id'], $arr['id_producto'], $arr['id_usuario'], $arr['pregunta'], $arr['respuesta'], 
                            $arr['fecha_pregunta'], $arr['fecha_respuesta']);
    }

    public function setId($id) {
        if($id === null || !is_numeric($id) || !is_integer($id) || $id <= 0 ||  $id >= 2137483647){
            throw new PreguntaException("Error en el id de la preguta");
        }
        $this->_id = $id;
    }
    public function getId() {
        return $this->_id;
    }

    public function setIdProducto($id_producto) {
        if($id_producto === null || !is_numeric($id_producto) || !is_integer($id_producto) || $id_producto <= 0 ||  $id_producto >= 2137483647){
            throw new PreguntaException("Error en el id del producto");
        }
        $this->_id_producto = $id_producto;
    }
    public function getIdProducto() {
        return $this->_id_producto;
    }

    public function setIdUsuario($id_usuario) {
        if($id_usuario === null || !is_numeric($id_usuario) || !is_integer($id_usuario) || $id_usuario <= 0 ||  $id_usuario >= 2137483647){
            throw new PreguntaException("Error en el id del usuario");
        }
        $this->_id_usuario = $id_usuario;
    }

    public function getIdUsuario() {
        return $this->_id_usuario;
    }

    public function setPregunta($pregunta) {
        if($pregunta === null || strlen($pregunta) > 300) {
            throw new PreguntaException("Error en la pregunta");
        }
        $this->_pregunta = $pregunta;
    }

    public function getPregunta() {
        return $this->_pregunta;
    }

    public function setRespuesta($respuesta) {
        if($respuesta !== null && strlen($respuesta) > 300) {
            throw new PreguntaException("Error en la respuesta de la pregunta");
        }
        $this->_respuesta = $respuesta;
    }

    public function getRespuesta() {
        return $this->_respuesta;
    }

    public function setFechaPregunta($fecha_pregunta) {
        //if ($fecha_pregunta === null || date_format(date_create_from_format('Y-m-d H:i', $fecha_pregunta), 'Y-m-d H:i') !== $fecha_pregunta) {
        //    throw new PreguntaException("Error en la fecha en la cual fue realizada la pregunta");
        //}
        $this->_fecha_pregunta = $fecha_pregunta;
    }

    public function getFechaPregunta() {
        return $this->_fecha_pregunta;
    }

    public function setFechaRespuesta($fecha_respuesta) {
        /*
        if ($fecha_respuesta !== null && date_format(date_create_from_format('Y-m-d H:i', $fecha_respuesta), 'Y-m-d H:i') !== $fecha_respuesta) {
            throw new PreguntaException("Error en la fecha en la cual fue contestada la pregunta");
        }*/
        $this->_fecha_respuesta = $fecha_respuesta;
    }

    public function getFechaRespuesta() {
        return $this->_fecha_respuesta;
    }
}

?>