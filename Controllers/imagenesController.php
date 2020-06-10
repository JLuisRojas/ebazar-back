<?php
require_once('../Models/DB.php');
require_once('../Models/Response.php');


try {
  $connection = DB::init();
}
catch(PDOException $e){
  error_log('Error de conexión: '. $e);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Error en la conexión a Base de datos");
  $response->send();
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['img']) && isset($_POST['id_producto'])) {
        try {
            $file_name = $_FILES['img']['name'];
            $file_tmp = $_FILES['img']['tmp_name'];
            $file_type = $_FILES['img']['type'];
            $file_size = $_FILES['img']['size'];
            $file = file_get_contents($file_tmp);

            $id_producto = $_POST['id_producto'];

            $sql = "INSERT INTO imagenes (imagen) VALUES(:img)";
            $query = $connection->prepare($sql);
            $query->bindParam(':img', $file, PDO::PARAM_LOB);
            $query->execute();

            $id_imagen = $connection->lastInsertId();
            //echo $id_producto;
            if($id_producto != -1) {
                $sql = "INSERT INTO imagenes_producto (id_imagen, id_producto) VALUES(:id_imagen, :id_producto)";
                $query = $connection->prepare($sql);
                $query->bindParam(':id_imagen', $id_imagen, PDO::PARAM_INT);
                $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                $query->execute();
            } 

            $returnData = array();
            $returnData['id'] = $id_imagen;
    
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Imagen publicada");
            $response->setData($returnData);
            $response->send();
            exit();

        } catch(PDOException $e) {
            error_log("Error en BD - " . $e);
    
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en BD al publicar la imagen $e");
            $response->send();
            exit();
        }
    } else {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de imagen y id_producto es obligatorio");
        $response->send();
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(!isset($_GET['id'])) {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de id no esta presente");
        $response->send();
        exit();
    }

    $id = $_GET["id"];

    $sql = "SELECT * FROM imagenes WHERE id = :id";
    $query = $connection->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    $row = $query->fetch(PDO::FETCH_ASSOC);
    echo $row['imagen'];
}
?>