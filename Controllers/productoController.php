<?php
// Controlador del producto
// el cual se encarga de las siguientes rutas
// FALTA TRY DE CREAR PRODUCTO Y RESPUESTA


require_once('../Models/Producto.php');
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

// GET server/producto?id=#
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(array_key_exists("producto_id", $_GET)) {
        $producto_id = $_GET["producto_id"];
        if($producto_id == '' || !is_numeric($producto_id)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de producto id no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        
        }

        // Consulta el producto
        $sql = "SELECT * FROM productos WHERE id = $producto_id";
        $query = $connection->prepare($sql);
        $query->execute();

        // Si no existe producto resulta en un error
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $response = new Response();
            $response->setHttpCode(404);
            $response->setSuccess(false);
            $response->addMessage("No existe el producto con id: $producto_id");
            $response->send();
            exit();
        }

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $producto = Producto::fromArray($row);
        }

        // Obtener preguntas
        $sqlPreguntas = "SELECT * FROM Preguntas WHERE id_producto = $producto_id";
        $queryPreguntas = $connection->prepare($sqlPreguntas);
        $queryPreguntas->execute();

        $preguntas = array();
        while($row = $queryPreguntas->fetch(PDO::FETCH_ASSOC)){
            $pregunta = Pregunta::fromArray($row);
            $preguntas[] = $pregunta->getArray();
        }

        // Formato de los datos del producto
        $productoData = $producto->getArray();
        $productoData = [
            'titulo' => $productoData['titulo'],
            'precio' => $productoData['precio'],
            'disponibles' => $productoData['disponibles'],
            'ubicacion' => $productoData['ubicacion'],
            'descripcion_corta' => $productoData['descripcion_corta'],
            'descripcion_larga' => $productoData['descripcion_larga'],
            'caracteristicas' => $productoData['caracteristicas'],
            'preguntas' => array_map(function($pregunta) {
                return [
                    'pregunta' => $pregunta['pregunta'],
                    'fecha' => $pregunta['fecha_pregunta'],
                    'respuesta' => $pregunta['respuesta']
                ];
            }, $preguntas)
        ];

        // Response todo bien
        $returnData['producto'] = $productoData;
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
        $response->addMessage("El metodo no tiene campo de id");
        $response->send();
        exit();
    }
} elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST server/producto
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
    // id_usuario: number
    // id_departamento: number
    // titulo: string
    // ubicacion: string
    // descripcion_corta: string
    // descripcion_larga: string
    // precio: number
    // disponibles: number
    // caracteristicas : JSON

    // Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información.
    if(!isset($json_data->id_usuario) || !isset($json_data->id_departamento) || !isset($json_data->titulo) ||
       !isset($json_data->ubicacion) || !isset($json_data->descripcion_corta) || !isset($json_data->descripcion_larga) ||
       !isset($json_data->precio) || !isset($json_data->disponibles) || !isset($json_data->caracteristicas))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($json_data->id_usuario) ? $response->addMessage("El id de usuario es obligatorio") : false);
        (!isset($json_data->id_departamento) ? $response->addMessage("El id de departamento es obligatorio") : false);
        (!isset($json_data->titulo) ? $response->addMessage("El titulo es obligatorio") : false);
        (!isset($json_data->ubicacion) ? $response->addMessage("La ubicacion es obligatoria") : false);
        (!isset($json_data->descripcion_corta) ? $response->addMessage("La descripcion corta es obligatoria") : false);
        (!isset($json_data->descripcion_larga) ? $response->addMessage("La descripcion larga es obligatoria") : false);
        (!isset($json_data->precio) ? $response->addMessage("El precio es obligatorio") : false);
        (!isset($json_data->disponibles) ? $response->addMessage("El numero de disponibles es obligatorio") : false);
        (!isset($json_data->caracteristicas) ? $response->addMessage("Las caracteristicas son obligatorias") : false);
        $response->send();
        exit();
    }
    $id_usuario = trim($json_data->id_usuario);
    $id_departamento = trim($json_data->id_departamento);
    $titulo = trim($json_data->titulo);
    $ubicacion = trim($json_data->ubicacion);
    $descripcion_corta = trim($json_data->descripcion_corta);
    $descripcion_larga = trim($json_data->descripcion_larga);
    $precio = trim($json_data->precio);
    $vendidos = 0;
    $disponibles = trim($json_data->disponibles);
    $caracteristicas = trim(json_encode($json_data->caracteristicas));
    $habilitado = 1;
    $img = null;
    $comentarios = 0;

    // Crea el producto en la BD
    try
    {
        $query = $connection->prepare("INSERT INTO productos 
            (id_usuario, id_departamento, titulo, ubicacion, descripcion_corta, descripcion_larga,
            precio, vendidos, disponibles, caracteristicas, habilitado, img, comentarios) 
            VALUES ('$id_usuario', '$id_departamento', '$titulo', '$ubicacion', '$descripcion_corta',
            '$descripcion_larga', '$precio', '$vendidos', '$disponibles', '$caracteristicas',
            '$habilitado', '$img', '$comentarios')");
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al crear descripcion");
            $response->send();
            exit();
        }
    
        $ultimoID = $connection->lastInsertId();

        // Consulta el producto
        $sql = "SELECT * FROM productos WHERE id = $ultimoID";
        $query = $connection->prepare($sql);
        $query->execute();
 
         while($row = $query->fetch(PDO::FETCH_ASSOC)){
             $producto = Producto::fromArray($row);
         }

        $returnData['producto'] = $producto->getArray();

        $response = new Response ();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Descripcion creada");
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
        $response->addMessage("Error al crear el producto");
        $response->send();
        exit();
    }

} else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método no permitido");
    $response->send();
    exit();
}

?>