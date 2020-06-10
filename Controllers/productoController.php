<?php
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

//Verificación token
if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage("No se encontró el token de acceso");
    $response->send();
    exit();
}

$accesstoken = $_SERVER['HTTP_AUTHORIZATION']; 

/*
try
{
    //Se verifica que el token de acceso sea válido.
    $query = $connection->prepare('SELECT id_user, caducidad_token_acceso, activo FROM sesiones, usuarios 
    WHERE sesiones.id_user = usuarios.id_usuario AND token_acceso = :token_acceso');
    $query->bindParam(':token_acceso', $accesstoken, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if ($rowCount === 0)
    {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Token de acceso no válido");
        $response->send();
        exit();
    }

    $row = $query->fetch(PDO::FETCH_ASSOC);

    $consulta_idUsuario = $row['id_user'];
    $consulta_cadTokenAcceso = $row['caducidad_token_acceso'];
    $consulta_activo = $row['activo'];

    if($consulta_activo !== 'SI') 
    {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Cuenta de usuario no activa");
        $response->send();
        exit();
    }
    //if (strtotime($consulta_cadTokenAcceso) + 6001 + 1200 < time()) 
    if (strtotime($consulta_cadTokenAcceso) < time()) 
    {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Token de acceso ha caducado");
        $response->send();
        exit();
    }
}
catch (PDOException $e) 
{
    error_log('Error en DB - ' . $e);

    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Error al autenticar usuario");
    $response->send();
    exit();
}
*/

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
        try {
            // Consulta el producto
            $sql = "SELECT * FROM productos WHERE id = $producto_id";
            $query = $connection->prepare($sql);
            $query->execute();

            // Si no existe producto resulta en un error
            $rowCount = $query->rowCount();
            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No existe el producto con id: $producto_id");
                $response->send();
                exit();
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $producto = Producto::fromArray($row);
            }

            // Obtener preguntas
            $sqlPreguntas = "SELECT * FROM preguntas WHERE id_producto = $producto_id";// AND respuesta IS NOT NULL";
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
                'id' => $productoData['id'],
                'id_usuario' => $productoData['id_usuario'],
                'titulo' => $productoData['titulo'],
                'id_departamento' => $productoData['id_departamento'],
                'precio' => $productoData['precio'],
                'disponibles' => $productoData['disponibles'],
                'ubicacion' => $productoData['ubicacion'],
                'descripcion_corta' => $productoData['descripcion_corta'],
                'descripcion_larga' => $productoData['descripcion_larga'],
                'caracteristicas' => json_decode($productoData['caracteristicas']),
                'img' => $productoData['img'],
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
        } catch(ProductoException $e) {
            error_log("Error de conexion -" . $e);
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        } catch(PreguntaException $e) {
            error_log("Error de conexion -" . $e);
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        } catch(PDOException $e) {
            error_log("Error de conexion -" . $e);
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al obtener el producto en la BD");
            $response->send();
            exit();
        }
    }
    // Checa si se estan pidiendo productos del vendedor 
    elseif(array_key_exists('id_vendedor', $_GET)) {
        $id_vendedor = $_GET["id_vendedor"];
        if($id_vendedor == '' || !is_numeric($id_vendedor)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de id de vendedor no puede estar vacio o ser diferente de un número");
            $response->send();
            exit();
        
        }

        try {
            // Consulta el producto
            $sql = "SELECT * FROM productos WHERE id_usuario = $id_vendedor";
            $query = $connection->prepare($sql);
            $query->execute();

            // Si no existe producto resulta en un error
            $rowCount = $query->rowCount();
            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No existen el producto con id: $id_vendedor");
                $response->send();
                exit();
            }

            $productos = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $producto = Producto::fromArray($row);
                $productos[] = $producto->getArray();
            }

            $productos = array_map(function($producto) {
                return [
                    'id' => $producto['id'],
                    'titulo' => $producto['titulo'],
                    'precio' => $producto['precio'],
                    'vendidos' => $producto['vendidos'],
                    'comentarios' => $producto['comentarios'],
                    'img' => $producto['img']
                ];
            }, $productos);

            // Response todo bien
            $returnData['productos'] = $productos;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit();
        } catch(ProductoException $e) {
            error_log("Error de conexion -" . $e);
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        } catch(PDOException $e) {
            error_log("Error de conexion -" . $e);
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al obtener el producto en la BD");
            $response->send();
            exit();
        }
    }
     else {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id (vendedor o producto)");
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
    // img: string

    // Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información. !isset($json_data->id_usuario) ||
    if(!isset($json_data->id_departamento) || !isset($json_data->titulo) ||
       !isset($json_data->ubicacion) || !isset($json_data->descripcion_corta) || !isset($json_data->descripcion_larga) ||
       !isset($json_data->precio) || !isset($json_data->disponibles) || !isset($json_data->caracteristicas))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        //(!isset($json_data->id_usuario) ? $response->addMessage("El id de usuario es obligatorio") : false);
        (!isset($json_data->id_departamento) ? $response->addMessage("El id de departamento es obligatorio") : false);
        (!isset($json_data->titulo) ? $response->addMessage("El titulo es obligatorio") : false);
        (!isset($json_data->ubicacion) ? $response->addMessage("La ubicacion es obligatoria") : false);
        (!isset($json_data->descripcion_corta) ? $response->addMessage("La descripcion corta es obligatoria") : false);
        (!isset($json_data->descripcion_larga) ? $response->addMessage("La descripcion larga es obligatoria") : false);
        (!isset($json_data->precio) ? $response->addMessage("El precio es obligatorio") : false);
        (!isset($json_data->disponibles) ? $response->addMessage("El numero de disponibles es obligatorio") : false);
        (!isset($json_data->caracteristicas) ? $response->addMessage("Las caracteristicas son obligatorias") : false);
        (!isset($json_data->img) ? $response->addMessage("El campo imagen es obligatorio") : false);
        $response->send();
        exit();
    }
    //$id_usuario = trim($json_data->id_usuario);
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
    $img = trim($json_data->img);
    $comentarios = 0;

    // Crea el producto en la BD
    try
    {
        $query = $connection->prepare("INSERT INTO productos 
            (id_usuario, id_departamento, titulo, ubicacion, descripcion_corta, descripcion_larga,
            precio, vendidos, disponibles, caracteristicas, habilitado, img, comentarios) 
            VALUES ('$consulta_idUsuario', '$id_departamento', '$titulo', '$ubicacion', '$descripcion_corta',
            '$descripcion_larga', '$precio', '$vendidos', '$disponibles', '$caracteristicas',
            '$habilitado', '$img', '$comentarios')");
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al crear el producto");
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
        $response->addMessage("Producto creado");
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

}
// Actializar info del producto 
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
    if(!array_key_exists("producto_id", $_GET)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id de producto");
        $response->send();
        exit();
    }

    $producto_id = $_GET["producto_id"];
    if($producto_id == '' || !is_numeric($producto_id)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de producto id no puede estar vacio o ser diferente de un número");
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
         * 
         * __Todos estos campo son opcionales__
         * id_departamento: int
         * titulo: string
         * ubicacion: string
         * descripcion_corta: string
         * descripcion_larga: string
         * precio: float
         * disponibles: int
         * caracteristicas: JSON
         * habilitado: boolean
         * img: string
         */

        $actualiza_id_departamento = false;
        $actualiza_titulo = false;
        $actualiza_ubicacion = false;
        $actualiza_descripcion_corta = false;
        $actualiza_descripcion_larga = false;
        $actualiza_precio = false;
        $actualiza_disponibles = false;
        $actualiza_caracteristicas = false;
        $actualiza_habilitado = false;
        $actualiza_img = false;


        $campos_query = "";

        if (isset($json_data->id_departamento)) {
            $actualiza_id_departamento = true;
            $campos_query .= "id_departamento = :id_departamento, ";
        }

        if (isset($json_data->titulo)) {
            $actualiza_titulo = true;
            $campos_query .= "titulo = :titulo, ";
        }

        if (isset($json_data->ubicacion)) {
            $actualiza_ubicacion = true;
            $campos_query .= "ubicacion = :ubicacion, ";
        }

        if (isset($json_data->descripcion_corta)) {
            $actualiza_descripcion_corta = true;
            $campos_query .= 'descripcion_corta = :descripcion_corta, ';
        }

        if (isset($json_data->descripcion_larga)) {
            $actualiza_descripcion_larga = true;
            $campos_query .= "descripcion_larga = :descripcion_larga, ";
        }

        if (isset($json_data->precio)) {
            $actualiza_precio = true;
            $campos_query .= "precio = :precio, ";
        }

        if (isset($json_data->disponibles)) {
            $actualiza_disponibles = true;
            $campos_query .= "disponibles = :disponibles, ";
        }

        if (isset($json_data->caracteristicas)) {
            $actualiza_caracteristicas = true;
            $campos_query .= "caracteristicas = :caracteristicas, ";
        }

        if (isset($json_data->habilitado)) {
            $actualiza_habilitado = true;
            $campos_query .= "habilitado = :habilitado, ";
        }

        if (isset($json_data->img)) {
            $actualiza_img = true;
            $campos_query .= "img = :img, ";
        }

        $campos_query = rtrim($campos_query, ", ");

        if ($actualiza_id_departamento === false 
        && $actualiza_titulo === false 
        && $actualiza_ubicacion === false 
        && $actualiza_descripcion_corta === false 
        && $actualiza_descripcion_larga === false
        && $actualiza_precio === false 
        && $actualiza_disponibles === false 
        && $actualiza_caracteristicas === false 
        && $actualiza_habilitado === false
        && $actualiza_img === false) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("No hay campos para actualizar");
            $response->send();
            exit();
        }

        $query = $connection->prepare('SELECT * FROM productos WHERE id = :id');
        $query->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No se encontró el producto");
            $response->send();
            exit();
        }

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $producto = Producto::fromArray($row);
        }

        $cadena_query = 'UPDATE productos SET ' . $campos_query . ' WHERE id = :id';
        $query = $connection->prepare($cadena_query);

        if($actualiza_id_departamento === true) {
            $producto->setIdDepartamento($json_data->id_departamento);
            $up_id_departamento = $producto->getIdDepartamento();
            $query->bindParam(':id_departamento', $up_id_departamento, PDO::PARAM_INT);
        }

        if($actualiza_titulo === true) {
            $producto->setTitulo($json_data->titulo);
            $up_titulo = $producto->getTitulo();
            $query->bindParam(':titulo', $up_titulo, PDO::PARAM_STR);
        }

        if($actualiza_ubicacion === true) {
            $producto->setUbicacion($json_data->ubicacion);
            $up_ubicacion = $producto->getUbicacion();
            $query->bindParam(':ubicacion', $up_ubicacion, PDO::PARAM_STR);
        }

        if($actualiza_descripcion_corta === true) {
            $producto->setDescripcionCorta($json_data->descripcion_corta);
            $up_descripcion_corta = $producto->getDescripcionCorta();
            $query->bindParam(':descripcion_corta', $up_descripcion_corta, PDO::PARAM_STR);
        }

        if($actualiza_descripcion_larga === true) {
            $producto->setDescripcionLarga($json_data->descripcion_larga);
            $up_descripcion_larga = $producto->getDescripcionLarga();
            $query->bindParam(':descripcion_larga', $up_descripcion_larga, PDO::PARAM_STR);
        }

        if($actualiza_precio === true) {
            $producto->setPrecio(floatval($json_data->precio));
            $up_precio = $producto->getPrecio();
            $query->bindParam(':precio', $up_precio, PDO::PARAM_STR);
        }

        if($actualiza_disponibles === true) {
            $producto->setDisponibles($json_data->disponibles);
            $up_disponibles = $producto->getDisponibles();
            $query->bindParam(':disponibles', $up_disponibles, PDO::PARAM_INT);
        }

        if($actualiza_caracteristicas === true) {
            $producto->setCaracteristicas(json_encode($json_data->caracteristicas));
            $up_caracteristicas = $producto->getCaracteristicas();
            $query->bindParam(':caracteristicas', $up_caracteristicas, PDO::PARAM_STR);
        }

        if($actualiza_habilitado === true) {
            $producto->setHabilitado($json_data->habilitado);
            $up_habilitado = $producto->getHabilitado();
            $query->bindParam(':habilitado', $up_habilitado, PDO::PARAM_BOOL);
        }

        if($actualiza_img === true) {
            $producto->setImg($json_data->img);
            $up_img = $producto->getImg();
            $query->bindParam(':img', $up_img, PDO::PARAM_STR);
        }

        $query->bindParam(':id', $producto_id, PDO::PARAM_INT);
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

        $query = $connection->prepare('SELECT * FROM productos WHERE id = :id');
        $query->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No se encontró el producto después de actulizar");
            $response->send();
            exit();
        }

        $productos = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $producto = Producto::fromArray($row);
            $productos[] = $producto->getArray();
        }

        $returnData = array();
        $returnData['total_registros'] = $rowCount;
        $returnData['productos'] = $productos;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Producto actualizado");
        $response->setData($returnData);
        $response->send();
        exit();
    }
    catch(ProductoException $e) {
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
        $response->addMessage("Error en BD al actualizar la tarea");
        $response->send();
        exit();
    }
}
elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    if(!array_key_exists("producto_id", $_GET)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id de producto");
        $response->send();
        exit();
    }

    $producto_id = $_GET["producto_id"];
    if($producto_id == '' || !is_numeric($producto_id)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El campo de producto id no puede estar vacio o ser diferente de un número");
        $response->send();
        exit();
    }

    try {
        // Borra preguntas del producto
        // Consulta las preguntas
        $sql = "SELECT id FROM preguntas WHERE id_producto = $producto_id";
        $query = $connection->prepare($sql);
        $query->execute();

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $id_pregunta = $row['id'];

            $queryP = $connection->prepare('DELETE FROM preguntas WHERE id = :id');
            $queryP->bindParam(':id', $id_pregunta, PDO::PARAM_INT);
            $queryP->execute();
        }

        // Borra las descripciones
        // Consulta las descropciones
        $sql = "SELECT id FROM descripciones WHERE id_producto = $producto_id";
        $query = $connection->prepare($sql);
        $query->execute();

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $id_pregunta = $row['id'];

            $queryD = $connection->prepare('DELETE FROM descripciones WHERE id = :id');
            $queryD->bindParam(':id', $id_pregunta, PDO::PARAM_INT);
            $queryD->execute();
        }

        $query = $connection->prepare('DELETE FROM productos WHERE id = :id');
        $query->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
    
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Producto no encontrado");
            $response->send();
            exit();
        }


        $response = new Response();
    
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Producto eliminado");
        $response->send();
        exit();
    }
    catch (PDOException $e) {
        error_log("Error en DB - ".$e, 0);
    
        $response = new Response();
    
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al eliminar producto");
        $response->send();
        exit();
    }


} 
else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método no permitido");
    $response->send();
    exit();
}
?>