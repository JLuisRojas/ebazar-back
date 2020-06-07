<?php


class DepartamentoException extends Exception{

}

class Departamento
{
    private $_id;
    private $_nombre;

    public function __construct($id,$nombre)
    {
        $this->setID($id);
        $this->setNombre($nombre);
    }

    public function getID(){
        return $this->_id;
    }

    public function getNombre(){
        return $this->_nombre;
    }

    public function setID($id)
    {
        if ($id !== null && (!is_numeric($id) || $id <= 0 || $id >= 2147483647 || $this->_id !== null))
        {
            throw new DepartamentoException("Error de ID en departamento");
        }
        $this->_id = $id;
    }

    public function setNombre($nombre){
        if($nombre === null || strlen($nombre) > 50 || strlen($nombre) < 1){
            throw new DepartamentoException("Error de Nombre de Departamento en departamentos");
        }
        $this->_nombre = $nombre;
    }

    public function getArray()
    {
        $departamento = array();

        $departamento['id'] = $this->getID();
        $departamento['nombre'] = $this->getNombre();

        return $departamento;
    }
}






?>