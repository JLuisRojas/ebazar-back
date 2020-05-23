<?php
// Controlador del producto
// el cual se encarga de las siguientes rutas


require_once('../Models/Producto.php');
require_once('../Models/DB.php');
require_once('../Models/Response.php');


try {
  $connection = DB::init();
}
catch(PDOException $e){
  error_log('Error de conexión: '. $e);
  $response = new Response();
  $response->setHttpCode(500);
  $response->setSuccess(false);
  $response->addMessage("Error en la conexión a Base de datos");
  $response->send();
  exit();
}

// GET server/producto?id=#
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(array_key_exists("producto_id", $_GET)) {
        $producto_id = $_GET["producto_id"];
        if($producto_id == '' || !is_numeric($producto_id)){
            $response = new Response();
            $response->setHttpCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de producto id no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        
        }

        $sql = 'SELECT * FROM productos WHERE id = 1';
        $query = $connection->prepare($sql);
        $query->execute();

        $rowCount = $query->rowCount();
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $producto = new Producto($row['id'], $row['id_usuario'], $row['id_departamento'], $row['titulo'], 
                                    $row['ubicacion'], $row['descripcion_corta'], $row['descripcion_larga'],
                                    $row['precio'], $row['vendidos'], $row['disponibles'], $row['caracteristicas'],
                                    $row['habilitado'], $row['img'], $row['comentarios']);
        }

        // Obtener comentarios

        $returnData['producto'] = $producto->getArray();
        $response = new Response();
        $response->setHttpCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        //$response->setToCache(true);
        $response->send();
        exit();
    } else {
        $response = new Response();
        $response->setHttpCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id");
        $response->send();
        exit();
    }
} elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST server/producto
    echo "Metodo post...";

} else {
    $response = new Response();
    $response->setHttpCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método no permitido");
    $response->send();
    exit();
}

?>