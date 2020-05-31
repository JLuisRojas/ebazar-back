<?php

<<<<<<< HEAD
class UsuarioException extends Exception{}
=======
class UsuarioException extends Exception{

}
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0

class Usuario{
    private $_id_usuario;
    private $_num_telefono;
    private $_domicilio;
    private $_nombre;
<<<<<<< HEAD
    private $_direccion;
=======
    private $_nombre_usuario;
    private $_foto_usuario;
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
    private $_email;
    private $_contrasena;
    private $_tipo_usuario;

<<<<<<< HEAD
    public function __construct($id_usuario,$num_telefono,$domicilio,$nombre,$direccion,$email,$contrasena,$tipo_usuario)
=======
    public function __construct($id_usuario,$num_telefono,$domicilio,$nombre,$nombre_usuario,$foto_usuario,$email,$contrasena,$tipo_usuario)
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
    {
        $this->setID($id_usuario);
        $this->setTelefono($num_telefono);
        $this->setDomicilio($domicilio);
        $this->setNombre($nombre);
<<<<<<< HEAD
        $this->setDireccion($direccion);
=======
        $this->setNombreUsuario($nombre_usuario);
        $this->setFoto($foto_usuario);
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
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

<<<<<<< HEAD
    public function getDireccion(){
        return $this->_direccion;
=======
    public function getNombreUsuario(){
        return $this->_nombre_usuario;
    }

    public function getFoto(){
        return $this->_foto_usuario;
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
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
<<<<<<< HEAD
            throw new TareaException("Error de ID en usuario");
=======
            throw new UsuarioException("Error de ID en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_id_usuario = $id_usuario;
    }

    public function setTelefono($num_telefono){
        if($num_telefono === null || strlen($num_telefono) > 50 || strlen($num_telefono) < 1){
<<<<<<< HEAD
            throw new TareaException("Error de Número telefónico en usuario");
=======
            throw new UsuarioException("Error de Número telefónico en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_num_telefono = $num_telefono;
    }

    public function setDomicilio($domicilio)
    {
<<<<<<< HEAD
        if ($domicilio !== null || strlen($domicilio) > 200 || strlen($domicilio) < 1)
        {
            throw new TareaException("Error de domicilio en usuario");
=======
        if ($domicilio === null || strlen($domicilio) > 200 || strlen($domicilio) < 1)
        {
            throw new UsuarioException("Error de domicilio en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_domicilio = $domicilio;
    }

    public function setNombre($nombre){
        if($nombre === null || strlen($nombre) > 100 || strlen($nombre) < 1){
<<<<<<< HEAD
            throw new TareaException("Error de Nombre en usuario");
=======
            throw new UsuarioException("Error de Nombre en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_nombre = $nombre;
    }

<<<<<<< HEAD
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
=======
    public function setNombreUsuario($nombre_usuario){
        if($nombre_usuario === null || strlen($nombre_usuario) > 50 || strlen($nombre_usuario) < 1){
            throw new UsuarioException("Error de Nombre Usuario en usuario");
        }
        $this->_nombre_usuario = $nombre_usuario;
    }

    public function setFoto($foto_usuario)
    {
        $this->_foto_usuario = $foto_usuario;
    }

    public function setEmail($email){
        if($email === null || strlen($email) > 100 || strlen($email) < 1){
            throw new UsuarioException("Error de e-mail en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_email = $email;
    }

    public function setContrasena($contrasena)
    {
        if ($contrasena === null || strlen($contrasena) > 50 || strlen($contrasena) < 1)
        {
<<<<<<< HEAD
            throw new TareaException("Error de contraseña en usuario");
=======
            throw new UsuarioException("Error de contraseña en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        }
        $this->_contrasena = $contrasena;
    }

    public function setTipoUsuario($tipo_usuario){
<<<<<<< HEAD
        if ($tipo_usuario !== null && (!is_numeric($tipo_usuario) || 
        $tipo_usuario <= 0 || $tipo_usuario >= 2147483647 || $this->_tipo_usuario !== null) || $tipo_usuario >=4)
        {
            throw new TareaException("Error de tipo de Usuario en usuario");
=======
        if ($tipo_usuario === null && (!is_numeric($tipo_usuario) || 
        $tipo_usuario <= 0 || $tipo_usuario >= 2147483647 || $this->_tipo_usuario === null) || $tipo_usuario >=4)
        {
            throw new UsuarioException("Error de tipo de Usuario en usuario");
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
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
<<<<<<< HEAD
        $usuario['direccion'] = $this->getDireccion();
=======
        $usuario['nombre_usuario'] = $this->getNombreUsuario();
        $usuario['foto_usuario'] = $this->getFoto();
>>>>>>> e8b9f5ec562604198d4c16de4cf20a840e7c77a0
        $usuario['email'] = $this->getEmail();
        $usuario['contrasena'] = $this->getContrasena();
        $usuario['tipo_usuario'] = $this->getTipoUsuario();

        return $usuario;
    }
}

?>