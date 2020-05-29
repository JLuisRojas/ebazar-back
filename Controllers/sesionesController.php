<?php
    require_once('../Models/DB.php');
    require_once('../Models/Response.php');
    
    try {
        $connection = DB::init();
    }
    catch (PDOException $e){
        error_log("Error de conexión - " . $e);
    
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error en conexión a Base de datos");
        $response->send();
        exit();
    }
    if (array_key_exists('id_sesion', $_GET)) 
    {
        Echo 'algo';
    }
    elseif (empty($_GET)) 
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') 
        {
            $response = new Response();
            $response->setHttpStatusCode(405);
            $response->setSuccess(false);
            $response->addMessage("Método no permitido");
            $response->send();
            exit();
        }

        if (!isset($jsonData->nombre_usuario) || !isset($jsonData->contrasena)) 
        {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->nombre_usuario) ? $response->addMessage("El nombre de usuario es obligatorio") : false);
            (!isset($jsonData->contrasena) ? $response->addMessage("La contraseña es obligatoria") : false);
            $response->send();
            exit();
        }      
        try 
        {
            $nombre_usuario = $jsonData->nombre_usuario;
            $contrasena = $jsonData->contrasena;
        
            $query = $connection->prepare('SELECT id, nombre_completo, contrasena, activo FROM usuarios WHERE nombre_usuario = :nombre_usuario');
            $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
            $query->execute();
    
            $rowCount = $query->rowCount();
        }
    
    }

    
?>