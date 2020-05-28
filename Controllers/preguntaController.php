<?php
/**
 * Controlador de preguntas
 * tiene los siguientes metodos
 *      GET server/preguntas?id_producto=#
 *      Obtiene las preguntas del producto para que el vendedor conteste
 * 
 *      POST server/preguntas
 *      Cuando un usuario hace una pregunta al producto
 * 
 *      PATCH server/preguntas?id_pregunta=#
 *      Cuando el vendedor contesta una pregunta
 * 
 *      DELETE server/preguntas?id_pregunta=#
 *      Cuando el vendedor borra una pregunta
 */

require_once('../Models/Pregunta.php');
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

// GET server/preguntas?id_producto=#
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(array_key_exists("id_producto", $_GET)) {
        $id_producto = $_GET["id_producto"];
        if($id_producto == '' || !is_numeric($id_producto)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de producto id no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        
        }

        // Consulta las preguntas
        $sql = "SELECT id, id_producto, id_usuario, pregunta, respuesta, DATE_FORMAT(fecha_pregunta, '%Y-%m-%d %H:%i') fecha_pregunta, DATE_FORMAT(fecha_respuesta, '%Y-%m-%d %H:%i') fecha_respuesta FROM Preguntas WHERE id_producto = $id_producto";
        $query = $connection->prepare($sql);
        $query->execute();

        $preguntas = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $pregunta = Pregunta::fromArray($row);
            $preguntas[] = $pregunta->getArray();
        }

        // Formato de las preguntas
        $preguntas = array_map(function($pregunta) {
            $res = array();
            $res['pregunta'] = $pregunta['pregunta']; 
            $res['fechaPregunta'] = $pregunta['fecha_pregunta'];

            if($pregunta['respuesta'] != null) {
                $res['tieneRespuesta'] = true;
                $res['respuesta'] = $pregunta['respuesta'];
            } else {
                $res['tieneRespuesta'] = false;
            }

            return $res;
            
        }, $preguntas);

        // Response todo bien
        $returnData['preguntas'] = $preguntas;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit();
    } else {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id de producto");
        $response->send();
        exit();
    }
} elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST server/preguntas
    // Formato de JSON.
    if($_SERVER['CONTENT_TYPE'] !== 'application/json')
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Encabezado content type no es un JSON");
        $response->send();
        exit();
    }

    // Obtenemos la informacióon
    $postData = file_get_contents('php://input');

    // Se verifica que la información sea un JSON.
    if(!$json_data = json_decode($postData))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El cuerpo de la solicitud no es un JSON válido");
        $response->send();
        exit();
    }

    // El formato del JSON es el siguiente
    // id_producto: number
    // id_usuario: number
    // pregunta: string
    // fecha: string

    // Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información.
    if(!isset($json_data->id_producto) || !isset($json_data->id_usuario) || !isset($json_data->pregunta) ||
       !isset($json_data->fecha))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($json_data->id_producto) ? $response->addMessage("El id de producto es obligatorio") : false);
        (!isset($json_data->id_usuario) ? $response->addMessage("El id de usuario es obligatorio") : false);
        (!isset($json_data->pregunta) ? $response->addMessage("La pregunta es obligatorio") : false);
        (!isset($json_data->fecha) ? $response->addMessage("La fecha es obligatoria") : false);
        $response->send();
        exit();
    }
    $id_producto = trim($json_data->id_producto);
    $id_usuario = trim($json_data->id_usuario);
    $pregunta = trim($json_data->pregunta);
    $fecha = trim($json_data->fecha);

    // Crea el producto en la BD
    try
    {
        $query = $connection->prepare('INSERT INTO Preguntas (id_producto, id_usuario, pregunta, respuesta, fecha_pregunta, fecha_respuesta) VALUES (:id_producto, :id_usuario, :pregunta, null, STR_TO_DATE(:fecha_pregunta, \'%Y-%m-%d %H:%i\'), null)');
        $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->bindParam(':pregunta', $pregunta, PDO::PARAM_STR);
        $query->bindParam(':fecha_pregunta', $fecha, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al crear la pregunta");
            $response->send();
            exit();
        }

        $ultimo_ID = $connection->lastInsertId();

        $sql = "SELECT id, id_producto, id_usuario, pregunta, respuesta, DATE_FORMAT(fecha_pregunta, '%Y-%m-%d %H:%i') fecha_pregunta, DATE_FORMAT(fecha_respuesta, '%Y-%m-%d %H:%i') fecha_respuesta FROM Preguntas WHERE id = $ultimo_ID";
        $query = $connection->prepare($sql);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al obtener la pregunta después de crearla");
            $response->send();
            exit();
        }

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            //$preguntaObj = Pregunta::fromArray($row);
            $pregunta = $row;
        }

        $returnData['pregunta'] = $pregunta;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Pregunta creada");
        $response->setData($returnData);
        $response->send();
        exit();
      
    }
    catch(PDOException $e)
    {
        error_log("Error de conexion -" . $e);
        $response = new Response ();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al crear la pregunta $e");
        $response->send();
        exit();
    }

}
// Contestar pregunta
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
    if(!array_key_exists("id_pregunta", $_GET)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id de pregunta");
        $response->send();
        exit();
    }

    $id_pregunta = $_GET["id_pregunta"];
    if($id_pregunta == '' || !is_numeric($id_pregunta)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de id de pregunta no puede estar vacio o ser diferente de un número");
        $response->send();
        exit();
    }

    try {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json'){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Encabezado "Content type" no es JSON');
            $response->send();
            exit();
        }

        $patchData = file_get_contents('php://input');

        if (!$json_data = json_decode($patchData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('El cuerpo de la solicitud no es un JSON válido');
            $response->send();
            exit();
        }

        /**
         * FORMATO DEL JSON
         * respuesta: string
         * fecha: string
         */

         // Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información.
        if(!isset($json_data->respuesta) || !isset($json_data->fecha))
        {
            $response = new Response ();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($json_data->id_producto) ? $response->addMessage("La respuesta es obligatorio") : false);
            (!isset($json_data->id_usuario) ? $response->addMessage("La fecha es obligatoria") : false);
            $response->send();
            exit();
        }

        $respuesta = trim($json_data->respuesta);
        $fecha = trim($json_data->fecha);

        $campos_query = "";

        if (isset($json_data->habilitado)) {
            $actualiza_habilitado = true;
            $campos_query .= "habilitado = :habilitado, ";
        }

        $campos_query = rtrim($campos_query, ", ");


        $query = $connection->prepare("SELECT id, id_producto, id_usuario, pregunta, respuesta, DATE_FORMAT(fecha_pregunta, '%Y-%m-%d %H:%i') fecha_pregunta, DATE_FORMAT(fecha_respuesta, '%Y-%m-%d %H:%i') fecha_respuesta FROM Preguntas WHERE id = :id");
        $query->bindParam(':id', $id_pregunta, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No se encontró la respuesta");
            $response->send();
            exit();
        }

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $pregunta = Pregunta::fromArray($row);
        }

        $cadena_query = 'UPDATE Preguntas SET respuesta = :respuesta, fecha_respuesta = STR_TO_DATE(:fecha_respuesta, \'%Y-%m-%d %H:%i\') WHERE id = :id';
        $query = $connection->prepare($cadena_query);

        $pregunta->setRespuesta($respuesta);
        $up_respuesta = $pregunta->getRespuesta();

        $pregunta->setFechaRespuesta($fecha);
        $up_fecha = $pregunta->getFechaRespuesta();

        $query->bindParam(':respuesta', $up_respuesta, PDO::PARAM_STR);
        $query->bindParam(':fecha_respuesta', $up_fecha, PDO::PARAM_STR);
        $query->bindParam(':id', $id_pregunta, PDO::PARAM_INT);

        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al actualizar el producto");
            $response->send();
            exit();
        }

        $sql = "SELECT id, id_producto, id_usuario, pregunta, respuesta, DATE_FORMAT(fecha_pregunta, '%Y-%m-%d %H:%i') fecha_pregunta, DATE_FORMAT(fecha_respuesta, '%Y-%m-%d %H:%i') fecha_respuesta FROM Preguntas WHERE id = $id_pregunta";
        $query = $connection->prepare($sql);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No se encontró la pregunta después de actulizar");
            $response->send();
            exit();
        }

        $preguntas = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $pregunta = Pregunta::fromArray($row);
            $preguntas[] = $pregunta->getArray();
        }

        $returnData = array();
        $returnData['total_registros'] = $rowCount;
        $returnData['preguntas'] = $preguntas;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Pregunta actualizada");
        $response->setData($returnData);
        $response->send();
        exit();
    }
    catch(PreguntaException $e) {
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
        $response->addMessage("Error en BD al actualizar la pregunta $e");
        $response->send();
        exit();
    }
}
elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    if(!array_key_exists("id_pregunta", $_GET)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id de pregunta");
        $response->send();
        exit();
    }

    $id_pregunta = $_GET["id_pregunta"];
    if($id_pregunta == '' || !is_numeric($id_pregunta)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de id de pregunta no puede estar vacio o ser diferente de un número");
        $response->send();
        exit();
    }

    try {
        $query = $connection->prepare('DELETE FROM preguntas WHERE id = :id');
        $query->bindParam(':id', $id_pregunta, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
    
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Pregunta no encontrada");
            $response->send();
            exit();
        }

        $response = new Response();
    
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Pregunta eliminada");
        $response->send();
        exit();
    }
    catch (PDOException $e) {
        error_log("Error en DB - ".$e, 0);
    
        $response = new Response();
    
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al eliminar la pregunta");
        $response->send();
        exit();
    }


}  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método no permitido");
    $response->send();
    exit();
}
?>