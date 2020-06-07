<?php

    require_once('../Models/DB.php');
    require_once('../Models/Producto.php');
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

//localhost/productos
if(empty($_GET))
{
    if($_SERVER['REQUEST_METHOD'] !== 'GET') 
    {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }
    try 
    {
        $sql = 'SELECT id, id_usuario, id_departamento, titulo, ubicacion, descripcion_corta, descripcion_larga, precio, vendidos, disponibles,
        caracteristicas, habilitado, img, comentarios FROM productos';
        $query = $connection->prepare($sql);
        $query->execute();

        // Si no existe producto resulta en un error
        $rowCount = $query->rowCount();

        if($rowCount === 0) 
        {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No existen productos aún");
            $response->send();
            exit();
        }

        $productos = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC))
        {
            $producto = new Producto($row['id'], $row['id_usuario'], $row['id_departamento'],$row['titulo'], $row['ubicacion'], 
            $row['descripcion_corta'], $row['descripcion_larga'], $row['precio'], $row['vendidos'], $row['disponibles'],
            $row['caracteristicas'], $row['habilitado'], $row['img'], $row['comentarios']);

            $productos[] = $producto->getArray();
        }

        $returnData = array();
        $returnData['total_registros'] = $rowCount;
        $returnData['productos'] = $productos;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setToCache(true);
        $response->setData($returnData);
        $response->send();
        exit();
    }
    catch(ProductoException $e){
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($e->getMessage());
        $response->send();
        exit();
    }
    catch(PDOException $e) {
        error_log("Error en BD - " . $e);

        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error en consulta de productos");
        $response->send();
        exit();
    }
}



?>