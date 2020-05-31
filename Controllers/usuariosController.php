<?php
    require_once('../Models/DB.php');
    require_once('../Models/Usuario.php');
    require_once('../Models/Response.php');

    try{
        $connection = DB::init();
    }
    catch (PDOException $e){
        $response = new Response ();

        error_log("Error de conexion -" . $e);
        $response->setHttpCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error en conexión a base de datos");
        $response->send();
        exit();
    }

    //GET

    //PATCH

    //POST
    if($_SERVER['REQUEST_METHOD'] !== 'POST')
    {
        $response = new Response ();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }

    //Verificamos que tenga el formato de JSON.
    if($_SERVER['CONTENT_TYPE'] !== 'application/json')
    {
    $response = new Response ();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Encabezado content type no es un JSON");
    $response->send();
    exit();
    }

    //Obtenemos la informacióon
    $postData = file_get_contents('php://input');

    //Se verifica que la información sea un JSON.
    if(!$json_data = json_decode($postData))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El cuerpo de la solicitud no es un JSON válido");
        $response->send();
        exit();
    }

    //Si el JSON no contiene ninguna de las cosas necesarias, es porque hay un error y no viene toda la información.
    if(!isset($json_data->num_telefono) || !isset($json_data->domicilio) || !isset($json_data->nombre) 
    || !isset($json_data->email) || !isset($json_data->contrasena) || !isset($json_data->tipo_usuario))
    {
        $response = new Response ();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($json_data->num_telefono) ? $response->addMessage("El número de telefono es obligatorio") : false);
        (!isset($json_data->domicilio) ? $response->addMessage("El domicilio es obligatorio") : false);
        (!isset($json_data->nombre) ? $response->addMessage("El nombre es obligatorio") : false);
        (!isset($json_data->email) ? $response->addMessage("El correo electrónico es obligatorio") : false);
        (!isset($json_data->contrasena) ? $response->addMessage("El contrasena es obligatorio") : false);
        (!isset($json_data->tipo_usuario) ? $response->addMessage("El tipo de usuario es obligatorio") : false);
        $response->send();
        exit();
    }

    //Se crea el nuevo Usuario y se verifican que todos los campos estén de acuerdo a las validaciones.
    $usuario = new Usuario(
        null,
        $json_data->num_telefono,
        $json_data->domicilio,
        $json_data->nombre,
        (isset($json_data->direccion) ? $json_data->direccion : null),
        $json_data->email,
        $json_data->contrasena,
        $json_data->tipo_usuario
    );

    $num_telefono = trim($usuario->getTelefono());
    $domicilio = trim($usuario->getDomicilio());
    $nombre = trim($usuario->getNombre());
    $direccion = trim($usuario->getDireccion());
    $email = trim($usuario->getEmail());
    $contrasena = trim($usuario->getContrasena());
    $tipo_usuario = trim($usuario->getTipoUsuario());
    

    try
    {
        $query = $connection->prepare('SELECT id_usuario FROM usuarios WHERE nombre = :nombre');
        $query->bindParam(':nombre', $nombre_usuario, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount !== 0)
        {
            $response = new Response ();
            $response->setHttpStatusCode(409);
            $response->setSuccess(false);
            $response->addMessage("Nombre de usuario ya existe");
            $response->send();
            exit();
        }

        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $query = $connection->prepare('INSERT INTO usuarios(num_telefono, domicilio, nombre, direccion,
        email, contrasena, tipo_usuario) VALUES (:num_telefono, :domicilio, :nombre, :direccion, :email, 
        :contrasena, :tipo_usuario)');
        $query->bindParam(':num_telefono',$num_telefono,PDO::PARAM_STR);
        $query->bindParam(':domicilio',$domicilio,PDO::PARAM_STR);
        $query->bindParam(':nombre',$nombre,PDO::PARAM_STR);
        $query->bindParam(':direccion',$direccion,PDO::PARAM_STR);
        $query->bindParam(':email',$email,PDO::PARAM_STR);
        $query->bindParam(':contrasena',$contrasena_hash,PDO::PARAM_STR);
        $query->bindParam(':tipo_usuario',$contrasena_hash,PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if($rowCount === 0)
        {
            $response = new Response ();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al crear usuario - Trata de nuevo");
            $response->send();
            exit();
        }
    
        $ultimoID = $connection->lastInsertId();

        $returnData = array();
        $returnData['id_usuario'] = $ultimoID;
        $returnData['num_telefono'] = $num_telefono;
        $returnData['domicilio'] = $domicilio;
        $returnData['nombre'] = $nombre;
        $returnData['direccion'] = $direccion;
        $returnData['email'] = $email;
        $returnData['contrasena'] = $contrasena;
        $returnData['tipo_usuario'] = $tipo_usuario;

        $response = new Response ();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Usuario creado");
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
        $response->addMessage("Error al crear usuario");
        $response->send();
        exit();
    }

?>