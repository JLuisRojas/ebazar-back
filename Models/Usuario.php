<?php

class UsuarioException extends Exception{}

class Usuario{
    private $_id_usuario;
    private $_num_telefono;
    private $_domicilio;
    private $_nombre;
    private $_direccion;
    private $_email;
    private $_contrasena;
    private $_tipo_usuario;

    public function __construct($id_usuario,$num_telefono,$domicilio,$nombre,$direccion,$email,$contrasena,$tipo_usuario)
    {
        $this->setID($id_usuario);
        $this->setTelefono($num_telefono);
        $this->setDomicilio($domicilio);
        $this->setNombre($nombre);
        $this->setDireccion($direccion);
        $this->setEmail($email);
        $this->setContrasena($contrasena);
        $this->setTipoUsuario($tipo_usuario);
    }

    public function getID(){
        return $this->_id_usuario;
    }

    public function getTelefono(){
        return $this->_num_telefono;
    }

    public function getDomicilio(){
        return $this->_domicilio;
    }

    public function getNombre(){
        return $this->_nombre;
    }

    public function getDireccion(){
        return $this->_direccion;
    }

    public function getEmail(){
        return $this->_email;
    }

    public function getContrasena(){
        return $this->_contrasena;
    }

    public function getTipoUsuario(){
        return $this->_tipo_usuario;
    }

    public function setID($id_usuario)
    {
        if ($id_usuario !== null && (!is_numeric($id_usuario) || $id_usuario <= 0 || $id_usuario >= 2147483647 || $this->_id_usuario !== null))
        {
            throw new TareaException("Error de ID en usuario");
        }
        $this->_id_usuario = $id_usuario;
    }

    public function setTelefono($num_telefono){
        if($num_telefono === null || strlen($num_telefono) > 50 || strlen($num_telefono) < 1){
            throw new TareaException("Error de Número telefónico en usuario");
        }
        $this->_num_telefono = $num_telefono;
    }

    public function setDomicilio($domicilio)
    {
        if ($domicilio !== null || strlen($domicilio) > 200 || strlen($domicilio) < 1)
        {
            throw new TareaException("Error de domicilio en usuario");
        }
        $this->_domicilio = $domicilio;
    }

    public function setNombre($nombre){
        if($nombre === null || strlen($nombre) > 100 || strlen($nombre) < 1){
            throw new TareaException("Error de Nombre en usuario");
        }
        $this->_nombre = $nombre;
    }

    public function setDireccion($direccion)
    {
        if (strlen($direccion) > 200 || strlen($direccion) < 1)
        {
            throw new TareaException("Error de direccion en usuario");
        }
        $this->_direccion = $direccion;
    }

    public function setEmail($email){
        if($email === null || strlen($email) > 100 || strlen($nombre) < 1){
            throw new TareaException("Error de e-mail en usuario");
        }
        $this->_email = $email;
    }

    public function setContrasena($contrasena)
    {
        if ($contrasena === null || strlen($contrasena) > 50 || strlen($contrasena) < 1)
        {
            throw new TareaException("Error de contraseña en usuario");
        }
        $this->_contrasena = $contrasena;
    }

    public function setTipoUsuario($tipo_usuario){
        if ($tipo_usuario !== null && (!is_numeric($tipo_usuario) || 
        $tipo_usuario <= 0 || $tipo_usuario >= 2147483647 || $this->_tipo_usuario !== null) || $tipo_usuario >=4)
        {
            throw new TareaException("Error de tipo de Usuario en usuario");
        }
        $this->_tipo_usuario = $tipo_usuario;
    }

    public function getArray()
    {
        $usuario = array();

        $usuario['id_usuario'] = $this->getID();
        $usuario['num_telefono'] = $this->getTelefono();
        $usuario['domicilio'] = $this->getDomicilio();
        $usuario['nombre'] = $this->getNombre();
        $usuario['direccion'] = $this->getDireccion();
        $usuario['email'] = $this->getEmail();
        $usuario['contrasena'] = $this->getContrasena();
        $usuario['tipo_usuario'] = $this->getTipoUsuario();

        return $usuario;
    }
}

?>