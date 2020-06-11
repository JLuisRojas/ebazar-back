<?php
    require_once('../Models/DB.php');
    require_once('../Models/Departamento.php');
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

    if (empty($_GET))
    {
        if($_SERVER['REQUEST_METHOD'] === 'GET') 
        {
            try 
            {
                    
                $query = $connection->prepare('SELECT id, nombre FROM departamentos');
                $query->execute();  
    
                $rowCount = $query->rowCount();
    
                $usuarios = array();
        
                while($row = $query->fetch(PDO::FETCH_ASSOC)) 
                {
                    $departamento = new Departamento($row['id'], $row['nombre']);
                
                    $departamentos[] = $departamento->getArray();
                }
    
                $returnData = array();
                $returnData['total_registros'] = $rowCount;
                $returnData['departamentos'] = $departamentos;
    
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->setToCache(true);
                $response->setData($returnData);
                $response->send();
                exit();
            }
            catch(DepartamentoException $e){
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
                $response->addMessage("Error en consulta de departamentos");
                $response->send();
                exit();
            }
        }
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
            if(!isset($json_data->nombre))
            {
                $response = new Response ();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($json_data->nombre) ? $response->addMessage("El nombre del Departamento es obligatorio") : false);
                $response->send();
                exit();
            }

             //Se crea el nuevo Usuario y se verifican que todos los campos estén de acuerdo a las validaciones.
             $departamento = new Departamento(
                null,
                $json_data->nombre
            );

            $nombre = $departamento->getNombre();

            try
            {
                $query = $connection->prepare('SELECT id FROM departamentos WHERE nombre = :nombre');
                $query->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $query->execute();

                $rowCount = $query->rowCount();

                if($rowCount !== 0)
                {
                    $response = new Response ();
                    $response->setHttpStatusCode(409);
                    $response->setSuccess(false);
                    $response->addMessage("El departamento ya existe");
                    $response->send();
                    exit();
                }

                $query = $connection->prepare('INSERT INTO departamentos(nombre) VALUES (:nombre)');
                $query->bindParam(':nombre',$nombre,PDO::PARAM_STR);
                $query->execute();

                $rowCount = $query->rowCount();

                if($rowCount === 0)
                {
                    $response = new Response ();
                    $response->setHttpStatusCode(500);
                    $response->setSuccess(false);
                    $response->addMessage("Error al crear departamento - Trata de nuevo");
                    $response->send();
                    exit();
                }

                $ultimoID = $connection->lastInsertId();

                $returnData = array();
                $returnData['id'] = $ultimoID;
                $returnData['nombre'] = $nombre;

                $response = new Response ();
                $response->setHttpStatusCode(201);
                $response->setSuccess(true);
                $response->addMessage("Departamento creado");
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
                $response->addMessage("Error al crear departamento");
                $response->send();
                exit();
            }
        }
        else
        {
            $response = new Response();
            $response->setHttpStatusCode(405);
            $response->setSuccess(false);
            $response->addMessage("Método no permitido");
            $response->send();
            exit();
        }
    }
?>