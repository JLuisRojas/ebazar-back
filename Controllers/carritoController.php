<?php
// Controlador del carrito
// Metodos: 

require_once('../Models/Descripcion.php');
require_once('../Models/Producto.php');
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
// Obtener el carrito del usuario
// GET server/carrito?id_usuario=# 
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(array_key_exists("id_usuario", $_GET)) {
        $id_usuario = $_GET["id_usuario"];
        if($id_usuario == '' || !is_numeric($id_usuario)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de id de usuario no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        }

        // TODO: CHECAR SI EL USUARIO EXISTE

        // Obtine las descripciones del usuario
        $sql = "SELECT id_producto FROM descripciones WHERE id_usuario = $id_usuario AND pagado = 0";
        $query = $connection->prepare($sql);
        $query->execute();

        // Si no existe producto resulta en un error
        $rowCount = $query->rowCount();
        $productos = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            // obtener producto
            $id_producto = $row['id_producto'];
            $sqlProducto = "SELECT * FROM productos WHERE id = $id_producto";
            $queryProducto = $connection->prepare($sqlProducto);
            $queryProducto->execute();

            while($row = $queryProducto->fetch(PDO::FETCH_ASSOC)) {
                $producto = Producto::fromArray($row);
                $productos[] = $producto->getArray();
            }
        }
        $carritoData = [ 
            'envio' => 50,
            'productos' => array_map(function($producto) {
                return [
                    'id' => $producto['id'],
                    'titulo' => $producto['titulo'],
                    'precio' => $producto['precio']
                ];
            }, $productos)
        ];

        // Response todo bien
        $returnData['carrito'] = $carritoData;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit(); 
    }

    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("El metodo no tiene campo de id");
    $response->send();
    exit();
} 
// Agregar producto al carrito del usuario
// POST server/carrito
elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    // id_producto: number
    // cantidad: number

    // Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información.
    if(!isset($json_data->id_usuario) || !isset($json_data->id_producto) || !isset($json_data->cantidad))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($json_data->id_usuario) ? $response->addMessage("El id de usuario es obligatorio") : false);
        (!isset($json_data->id_producto) ? $response->addMessage("El id de producto es obligatorio") : false);
        (!isset($json_data->cantidad) ? $response->addMessage("La cantidad es obligatorio") : false);
        $response->send();
        exit();
    }

    // Descripcion
    $descripcion = new Descripcion($json_data->id_producto, $json_data->id_usuario, $json_data->cantidad);

    $id_producto = trim($descripcion->getIdProducto());
    $id_usuario = trim($descripcion->getIdUsuario());
    $cantidad = trim($descripcion->getCantidad());
    $pagado = 0;

    try
    {
        $query = $connection->prepare("INSERT INTO descripciones (id_usuario, cantidad, id_producto, pagado) VALUES ('$id_usuario', '$cantidad', '$id_producto', '$pagado')");
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

        $returnData = $descripcion->getArray();
        $returnData['id'] = $ultimoID;

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
        $response->addMessage("Error al crear la descripcion");
        $response->send();
        exit();
    }
}
// Finalizar compra / quitar del carrito
// Ahorita solo se va a borrar...
// PATCH server/carrito
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if(array_key_exists("id_usuario", $_GET)) {
        $id_usuario = $_GET["id_usuario"];
        if($id_usuario == '' || !is_numeric($id_usuario)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de id de usuario no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        }

        // TODO: CHECAR SI EL USUARIO EXISTE

        // Obtine las descripciones del usuario
        $sql = "SELECT id FROM descripciones WHERE id_usuario = $id_usuario AND pagado = 0";
        $query = $connection->prepare($sql);
        $query->execute();

        $rowCount = $query->rowCount();
        $descripciones = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $descripciones[] = $row['id'];
        }

        // borrar descripciones producto
        foreach ($descripciones as $id) {
            $sql = "DELETE FROM descripciones WHERE id = $id";
            $query = $connection->prepare($sql);
            $query->execute();
        }
        

        // Response todo bien
        $returnData['descripciones'] = $descripciones;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit(); 
    }

    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("El metodo no tiene campo de id");
    $response->send();
    exit();
}


?>