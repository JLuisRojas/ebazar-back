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
?>