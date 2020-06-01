<?php
    require_once('../Models/DB.php');
    require_once('../Models/Usuario.php');
    require_once('../Models/Response.php');


    
    try
    {
        $connection = DB::init();
    }
    catch (PDOException $e)
    {
        $response = new Response ();

        error_log("Error de conexion -" . $e);
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error en conexión a base de datos");
        $response->send();
        exit();
    }


    //GET localhost/usuarios/id_usuario=(1-9)
    if(array_key_exists("id_usuario", $_GET))
    {
        $id_usuario = $_GET['id_usuario'];
    
        if ($id_usuario === '' || !is_numeric($id_usuario)) 
        {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El id del Usuario no puede estar vacío y debe ser numérico");
            $response->send();
            exit();
        }
        if($_SERVER['REQUEST_METHOD'] === 'GET') 
        {
            try 
            {
                
                $query = $connection->prepare('SELECT id_usuario, num_telefono, domicilio, nombre, nombre_usuario, 
                foto_usuario, email, contrasena, tipo_usuario FROM usuarios WHERE id_usuario = :id_usuario');
                $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query->execute();
        
                $rowCount = $query->rowCount();
        
                if($rowCount === 0) 
                {
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("No se encontró el usuario");
                    $response->send();
                    exit();
                }
        
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                    $usuario = new Usuario($row['id_usuario'], $row['num_telefono'], $row['domicilio'], $row['nombre'], $row['nombre_usuario'], 
                    $row['foto_usuario'], $row['email'], $row['contrasena'], $row['tipo_usuario']);
                
                    $usuarios[] = $usuario->getArray();
                }
        
                $returnData = array();
                $returnData['total_registros'] = $rowCount;
                $returnData['usuarios'] = $usuarios;
    
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->setToCache(true);
                $response->setData($returnData);
                $response->send();
                exit();
            }
            catch (UsuarioException $e) 
            {
                $response = new Response();
            
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage($e->getMessage());
                $response->send();
                exit();
            }
            catch (PDOException $e) 
            {
                error_log("Error en DB - " . $e, 0);
            
                $response = new Response();
            
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al obtener usuario");
                $response->send();
                exit();
            }
        }
        else if($_SERVER['REQUEST_METHOD'] === 'PATCH')
        {
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
    
                $actualiza_num_telefono = false;
                $actualiza_domicilio = false;
                $actualiza_nombre = false;
                $actualiza_nombre_usuario = false;
                $actualiza_foto_usuario = false;
                $actualiza_email = false;
                $actualiza_contrasena = false;
    
                $campos_query = "";
    
                if (isset($json_data->num_telefono)) {
                    $actualiza_num_telefono = true;
                    $campos_query .= "num_telefono = :num_telefono, ";
                }
    
                if (isset($json_data->domicilio)) {
                    $actualiza_domicilio = true;
                    $campos_query .= "domicilio = :domicilio, ";
                }
    
                if (isset($json_data->nombre)) {
                    $actualiza_nombre = true;
                    $campos_query .= 'nombre = :nombre, ';
                }

                if (isset($json_data->nombre_usuario)) {
                    $actualiza_nombre_usuario = true;
                    $campos_query .= 'nombre_usuario = :nombre_usuario, ';
                }
    
                if (isset($json_data->foto_usuario)) {
                    $actualiza_foto_usuario = true;
                    $campos_query .= "foto_usuario = :foto_usuario, ";
                }
    
                if (isset($json_data->email)) {
                    $actualiza_email = true;
                    $campos_query .= "email = :email, ";
                }

                if (isset($json_data->contrasena)) {
                    $actualiza_contrasena = true;
                    $campos_query .= "contrasena = :contrasena, ";
                }
    
                $campos_query = rtrim($campos_query, ", ");
    
                if ($actualiza_num_telefono === false && $actualiza_domicilio === false && $actualiza_nombre === false 
                && $actualiza_nombre_usuario === false &&$actualiza_foto_usuario === false && $actualiza_email === false 
                && $actualiza_contrasena === false) 
                {
                    $response = new Response();
                    $response->setHttpStatusCode(400);
                    $response->setSuccess(false);
                    $response->addMessage("No hay campos para actualizar");
                    $response->send();
                    exit();
                }
                
                $query = $connection->prepare('SELECT id_usuario, num_telefono, domicilio, nombre, nombre_usuario, foto_usuario, 
                email, contrasena, tipo_usuario FROM usuarios WHERE id_usuario = :id_usuario');
                $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query->execute();
    
                $rowCount = $query->rowCount();
    
                if($rowCount === 0) {
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("No se encontró el usuario");
                    $response->send();
                    exit();
                }
    
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $usuario = new Usuario($row['id_usuario'], $row['num_telefono'], $row['domicilio'], $row['nombre'], 
                    $row['nombre_usuario'], $row['foto_usuario'], $row['email'], $row['contrasena'], $row['tipo_usuario']);
                }
    
                $cadena_query = 'UPDATE usuarios SET ' . $campos_query . ' WHERE id_usuario = :id_usuario';
                $query = $connection->prepare($cadena_query);
    
                if($actualiza_num_telefono === true) {
                    $usuario->setTelefono($json_data->num_telefono);
                    $up_num_telefono = $usuario->getTelefono();
                    $query->bindParam(':num_telefono', $up_num_telefono, PDO::PARAM_STR);
                }
    
                if($actualiza_domicilio === true) {
                    $usuario->setDomicilio($json_data->domicilio);
                    $up_domicilio = $usuario->getDomicilio();
                    $query->bindParam(':domicilio', $up_domicilio, PDO::PARAM_STR);
                }
    
                if($actualiza_nombre === true) {
                    $usuario->setNombre($json_data->nombre);
                    $up_nombre = $usuario->getNombre();
                    $query->bindParam(':nombre', $up_nombre, PDO::PARAM_STR);
                }

                if($actualiza_nombre_usuario === true) {
                    $usuario->setNombre($json_data->nombre_usuario);
                    $up_nombre_usuario = $usuario->getNombreUsuario();
                    $query->bindParam(':nombre_usuario', $up_nombre_usuario, PDO::PARAM_STR);
                }
    
                if($actualiza_foto_usuario === true) {
                    $usuario->setDireccion($json_data->foto_usuario);
                    $up_foto = $usuario->getFoto();
                    $query->bindParam(':foto_usuario', $up_foto, PDO::PARAM_STR);
                }
    
                if($actualiza_email === true) {
                    $usuario->setEmail($json_data->email);
                    $up_email = $usuario->getEmail();
                    $query->bindParam(':email', $up_email, PDO::PARAM_STR);
                }
                
                
                if($actualiza_contrasena === true) {
                    $usuario->setContrasena($json_data->contrasena);
                    $up_contrasena = trim($usuario->getContrasena());
                    $contrasena_hash = password_hash($up_contrasena, PASSWORD_DEFAULT);
                    $query->bindParam(':contrasena', $contrasena_hash, PDO::PARAM_STR);
                }
    
                $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query->execute();
    
                $rowCount = $query->rowCount();
    
                if ($rowCount === 0) {
                    $response = new Response();
                    $response->setHttpStatusCode(500);
                    $response->setSuccess(false);
                    $response->addMessage("Error al actualizar información");
                    $response->send();
                    exit();
                }
    
                $query = $connection->prepare('SELECT id_usuario, num_telefono, domicilio, nombre, nombre_usuario, foto_usuario, 
                email, contrasena, tipo_usuario FROM usuarios WHERE id_usuario = :id_usuario');
                $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query->execute();
    
                $rowCount = $query->rowCount();
    
                if($rowCount === 0) {
                    $response = new Response();
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("No se encontró la información después de actulizar");
                    $response->send();
                    exit();
                }
    
                $usuario = array();
    
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                    $usuario = new Usuario($row['id_usuario'], $row['num_telefono'], $row['domicilio'], $row['nombre'], 
                    $row['nombre_usuario'], $row['foto_usuario'], $row['email'], $row['contrasena'], $row['tipo_usuario']);
                    
                    $usuarios[] = $usuario->getArray();
                }
    
                $returnData = array();
                $returnData['total_registros'] = $rowCount;
                $returnData['usuarios'] = $usuarios;
    
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("Datos actualizads");
                $response->setData($returnData);
                $response->send();
                exit();
            }
            catch(TareaException $e) {
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
                $response->addMessage("Error en BD al actualizar la información");
                $response->send();
                exit();
            }
        }
        else if($_SERVER['REQUEST_METHOD'] === 'DELETE') 
        {
            try 
            {
                $query = $connection->prepare('DELETE FROM usuarios WHERE id_usuario = :id_usuario');
                $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $query->execute();
    
                $rowCount = $query->rowCount();
    
                if ($rowCount === 0)
                {
                    $response = new Response();
            
                    $response->setHttpStatusCode(404);
                    $response->setSuccess(false);
                    $response->addMessage("Usuario no encontrado");
                    $response->send();
                    exit();
                }
    
                $response = new Response();
            
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("Usuario eliminado");
                $response->send();
                exit();
            }
            catch (PDOException $e) 
            {
                error_log("Error en DB - ".$e, 0);
            
                $response = new Response();
            
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al eliminar usuario");
                $response->send();
                exit();
            }
        }
        else
        {
            $response = new Response ();
            $response->setHttpStatusCode(405);
            $response->setSuccess(false);
            $response->addMessage("Método no permitido");
            $response->send();
            exit();
        }
    }
    
    elseif (empty($_GET))
    {
        //GET localhost/usuarios
        if($_SERVER['REQUEST_METHOD'] === 'GET')
        {
            try 
            {
                $query = $connection->prepare('SELECT id_usuario, num_telefono, domicilio, nombre, nombre_usuario, foto_usuario, 
                email, contrasena, tipo_usuario FROM usuarios');
                $query->execute();
    
                $rowCount = $query->rowCount();
    
                $usuarios = array();
    
                while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $usuario = new Usuario($row['id_usuario'], $row['num_telefono'], $row['domicilio'], $row['nombre'], 
                    $row['nombre_usuario'], $row['foto_usuario'], $row['email'], $row['contrasena'], $row['tipo_usuario']);
                
                    $usuarios[] = $usuario->getArray();
                }
    
                $returnData = array();
                $returnData['total_registros'] = $rowCount;
                $returnData['usuarios'] = $usuarios;
    
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->setToCache(true);
                $response->setData($returnData);
                $response->send();
                exit();
            }
            catch(UsuarioException $e){
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
                $response->addMessage("Error en consulta de tareas");
                $response->send();
                exit();
            }
        }
         //POST
        elseif($_SERVER['REQUEST_METHOD'] === 'POST')
        {
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
            if(!isset($json_data->num_telefono) || !isset($json_data->domicilio) || !isset($json_data->nombre) || !isset($json_data->nombre_usuario)
            || !isset($json_data->email) || !isset($json_data->contrasena) || !isset($json_data->tipo_usuario))
            {
                $response = new Response ();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($json_data->num_telefono) ? $response->addMessage("El número de telefono es obligatorio") : false);
                (!isset($json_data->domicilio) ? $response->addMessage("El domicilio es obligatorio") : false);
                (!isset($json_data->nombre) ? $response->addMessage("El nombre es obligatorio") : false);
                (!isset($json_data->nombre_usuario) ? $response->addMessage("El nombre de usuario es obligatorio") : false);
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
                $json_data->nombre_usuario,
                (isset($json_data->foto_usuario) ? $json_data->foto_usuario : null),
                $json_data->email,
                $json_data->contrasena,
                $json_data->tipo_usuario
            );

            $num_telefono = $usuario->getTelefono();
            $domicilio = $usuario->getDomicilio();
            $nombre = $usuario->getNombre();
            $nombre_usuario = $usuario->getNombreUsuario();
            $foto_usuario = $usuario->getFoto();
            $email = $usuario->getEmail();
            $contrasena = $usuario->getContrasena();
            $tipo_usuario = $usuario->getTipoUsuario();
            

            try
            {
                $query = $connection->prepare('SELECT id_usuario FROM usuarios WHERE nombre_usuario = :nombre_usuario');
                $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
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

                $query = $connection->prepare('INSERT INTO usuarios(num_telefono, domicilio, nombre, nombre_usuario, foto_usuario,
                email, contrasena, tipo_usuario) VALUES (:num_telefono, :domicilio, :nombre, :nombre_usuario, :foto_usuario, :email, 
                :contrasena, :tipo_usuario)');
                $query->bindParam(':num_telefono',$num_telefono,PDO::PARAM_STR);
                $query->bindParam(':domicilio',$domicilio,PDO::PARAM_STR);
                $query->bindParam(':nombre',$nombre,PDO::PARAM_STR);
                $query->bindParam(':nombre_usuario',$nombre_usuario,PDO::PARAM_STR);
                $query->bindParam(':foto_usuario',$foto_usuario,PDO::PARAM_STR);
                $query->bindParam(':email',$email,PDO::PARAM_STR);
                $query->bindParam(':contrasena',$contrasena_hash,PDO::PARAM_STR);
                $query->bindParam(':tipo_usuario',$tipo_usuario,PDO::PARAM_INT);
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
                $returnData['nombre_usuario'] = $nombre_usuario;
                $returnData['foto_usuario'] = $foto_usuario;
                $returnData['email'] = $email;
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
        }
        else
        {
            echo "hola";
        }
    }

?>