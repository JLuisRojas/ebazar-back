<?php

class ProductoException extends Exception { }

class Producto {
  private $_id;
  private $_id_usuario;
  private $_id_departamento;
  private $_titulo;
  private $_ubicacion;
  private $_descripcion_corta;
  private $_descripcion_larga;
  private $_precio;
  private $_vendidos;
  private $_disponibles;
  private $_caracteristicas;
  private $_habilitado;
  private $_img;
  private $_comentarios;  // Numero de comentarios que tiene el producto

  public function __construct ($id, $id_usuario, $id_departamento, $titulo, $ubicacion, $descripcion_corta,
                               $descripcion_larga, $precio, $vendidos, $disponibles, $caracteristicas,
                               $habilitado, $img, $comentarios){
    $this->setId($id);
    $this->setIdUsuario($id_usuario);
    $this->setIdDepartamento($id_departamento);
    $this->setTitulo($titulo);
    $this->setUbicacion($ubicacion);
    $this->setDescripcionCorta($descripcion_corta);
    $this->setDescripcionLarga($descripcion_larga);
    $this->setPrecio($precio);
    $this->setVendidos($vendidos);
    $this->setDisponibles($disponibles);
    $this->setCaracteristicas($caracteristicas);
    $this->setHabilitado($habilitado);
    $this->setImg($img);
    $this->setComentarios($comentarios);
  }

  public static function fromArray($arr) {
    return new Producto($arr['id'], $arr['id_usuario'], $arr['id_departamento'], $arr['titulo'], 
                        $arr['ubicacion'], $arr['descripcion_corta'], $arr['descripcion_larga'],
                        $arr['precio'], $arr['vendidos'], $arr['disponibles'], $arr['caracteristicas'],
                        $arr['habilitado'], $arr['img'], $arr['comentarios']);
  }

  public function setId($id) {
    if($id === null || !is_numeric($id) || !is_integer($id) || $id <= 0 ||  $id >= 2137483647 || $this->_id !== null ){
      throw new ProductoException("Error en ID del producto");
    }
    
    $this->_id = $id;
  }
  public function getId() {
    return $this->_id;
  }

  public function setIdUsuario($idUsuario) {
    if($idUsuario === null || !is_numeric($idUsuario) || !is_integer($idUsuario) || $idUsuario <= 0 ||  $idUsuario >= 2137483647 || $this->_id_usuario !== null ){
      throw new ProductoException("Error en ID del usuario");
    }
    
    $this->_id_usuario = $idUsuario;
  }
  public function getIdUsuario() {
    return $this->_id_usuario;
  }

  public function setIdDepartamento($idDepartamento) {
    if($idDepartamento === null || !is_numeric($idDepartamento) || !is_integer($idDepartamento) || $idDepartamento <= 0 ||  $idDepartamento >= 2137483647 || $this->_id_departamento !== null) {
      throw new ProductoException("Error en ID del departamento");
    }
    
    $this->_id_departamento = $idDepartamento;
  }
  public function getIdDepartamento() {
    return $this->_id_departamento;
  }
  
  public function setTitulo($titulo) {
    if($titulo === null || strlen($titulo) > 50 || strlen($titulo) < 1) {
      throw new ProductoException("Error en título del producto");
    }
    $this->_titulo = $titulo;
  }
  public function getTitulo() {
    return $this->_titulo;
  }

  public function setUbicacion($ubicacion) {
    if($ubicacion === null || strlen($ubicacion) > 70 || strlen($ubicacion) < 1) {
      throw new ProductoException("Error en la ubicacion del producto");
    }
    $this->_titulo = $ubicacion;
  }
  public function getUbicacion() {
    return $this->_ubicacion;
  }

  public function setDescripcionCorta($descripcion_corta) {
    if($descripcion_corta !== null && strlen($descripcion_corta) > 50) {
      throw new ProductoException("Error en descripción corta de la producto");
    }
    $this->_descripcion_corta = $descripcion_corta;
  }
  public function getDescripcionCorta() {
    return $this->_descripcion_corta;
  }

  public function setDescripcionLarga($descripcion_larga) {
    if($descripcion_larga !== null && strlen($descripcion_larga) > 150) {
      throw new ProductoException("Error en descripción larga del prodcuto");
    }
    $this->_descripcion_larga = $descripcion_larga;
  }
  public function getDescripcionLarga() {
    return $this->_descripcion_larga;
  }

  public function setPrecio($precio) {
    if($precio === null || !is_numeric($precio) || !is_float($precio) || $precio <= 0 ||  $precio >= 2137483647){
      throw new ProductoException("Error en el precio del producto");
    }
    
    $this->_precio = $precio;
  }
  public function getPrecio() {
    return $this->_precio;
  }

  public function setVendidos($vendidos) {
    if($vendidos === null || !is_numeric($vendidos) || !is_integer($vendidos) || $vendidos <= 0 ||  $vendidos >= 2137483647){
      throw new ProductoException("Error en el numero de vendidos del producto");
    }
    
    $this->_vendidos = $vendidos;
  }
  public function getVendidos() {
    return $this->_vendidos;
  }

  public function setDisponibles($disponibles) {
    if($disponibles === null || !is_numeric($disponibles) || !is_integer($disponibles) || $disponibles <= 0 ||  $disponibles >= 2137483647){
      throw new ProductoException("Error en el numero de vendidos del producto");
    }
    
    $this->_disponibles = $disponibles;
  }
  public function getDisponibles() {
    return $this->_disponibles;
  }

  public function setCaracteristicas($caracteristicas) {
    $this->_caracteristicas = json_decode($caracteristicas);
  }
  public function getCaracteristicas() {
    return $this->_caracteristicas;
  }

  public function setHabilitado($habilitado) {
    if($habilitado === null || !is_numeric($habilitado) || !is_integer($habilitado) || ($habilitado != 0 &&  $habilitado != 1)){
      throw new ProductoException("Error en habilitar el producto");
    }
    
    $this->_habilitado = $habilitado;
  }
  public function getHabilitado() {
    return $this->_habilitado;
  }

  public function setImg($img) {
    $this->_img = $img;
  }
  public function getImg() {
    return $this->_img;
  }

  public function setComentarios($comentarios) {
    $this->_comentarios = $comentarios;
  }
  public function getComentarios() {
    return $this->_comentarios;
  }

  public function getArray() {
    $producto = array();

    $producto['id'] = $this->getId();
    $producto['id_usuario'] = $this->getIdUsuario();
    $producto['id_departamento'] = $this->getIdDepartamento();
    $producto['titulo'] = $this->getTitulo();
    $producto['ubicacion'] = $this->getUbicacion();
    $producto['descripcion_corta'] = $this->getDescripcionCorta();
    $producto['descripcion_larga'] = $this->getDescripcionLarga();
    $producto['precio'] = $this->getPrecio();
    $producto['vendidos'] = $this->getVendidos();
    $producto['disponibles'] = $this->getDisponibles();
    $producto['caracteristicas'] = $this->getCaracteristicas();
    $producto['habilitado'] = $this->getHabilitado();
    //$producto['img'] = $this->getImg();
    $producto['comentarios'] = $this->getComentarios();

    return $producto;
  }
}

?>
