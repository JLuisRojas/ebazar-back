<?php

class FaqException extends Exception{

}

class Faq
{
    private $_id;
    private $_titulo;
    private $_descripcion;

    public function __construct($id,$titulo,$descripcion)
    {
        $this->setID($id);
        $this->setTitulo($titulo);
        $this->setDescripcion($descripcion);
    }

    public function getID(){
        return $this->_id;
    }

    public function getTitulo(){
        return $this->_titulo;
    }

    public function getDescripcion(){
        return $this->_descripcion;
    }

    public function setID($id)
    {
        if ($id !== null && (!is_numeric($id) || $id <= 0 || $id >= 2147483647 || $this->_id !== null))
        {
            throw new FaqException("Error de ID en FAQ");
        }
        $this->_id = $id;
    }

    public function setTitulo($titulo){
        if($titulo === null || strlen($titulo) > 100 || strlen($titulo) < 1){
            throw new FaqException("Error de Titulo de pregunta en FAQ");
        }
        $this->_titulo = $titulo;
    }

    public function setDescripcion($descripcion){
        if($descripcion === null || strlen($descripcion) > 500 || strlen($descripcion) < 1){
            throw new FaqException("Error de Descripción de pregunta en FAQ");
        }
        $this->_descripcion = $descripcion;
    }

    public function getArray()
    {
        $faq = array();

        $faq['id'] = $this->getID();
        $faq['titulo'] = $this->getTitulo();
        $faq['descripcion'] = $this->getDescripcion();

        return $faq;
    }
}