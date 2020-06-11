<?php

require_once('../Models/Faq.php');
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

if (empty($_GET))
{
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    {
        try 
        {                
            $query = $connection->prepare('SELECT id, titulo, descripcion FROM faq');
            $query->execute();  
    
            $rowCount = $query->rowCount();

            if($rowCount === 0)
            {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Aún no hay preguntas");
                $response->send();
                exit();
            }
    
            $faqs = array();
    
            while($row = $query->fetch(PDO::FETCH_ASSOC)) 
            {
                $faq = new Faq($row['id'], $row['titulo'], $row['descripcion']);
            
                    $faqs[] = $faq->getArray();
            }
    
            $returnData = array();
            $returnData['total_registros'] = $rowCount;
            $returnData['preguntas'] = $faqs;
    
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch(DepartamentoException $e)
        {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch(PDOException $e) 
        {
            error_log("Error en BD - " . $e);

            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en consulta de preguntas en FAQ");
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
        if(!isset($json_data->titulo) || !isset($json_data->descripcion))
        {
            $response = new Response ();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($json_data->titulo) ? $response->addMessage("El titulo de la pregunta es obligatorio") : false);
            (!isset($json_data->descripcion) ? $response->addMessage("La descripción de la pregunta es obligatorio") : false);
            $response->send();
            exit();
        }

         //Se crea el nuevo Usuario y se verifican que todos los campos estén de acuerdo a las validaciones.
         $faq = new Faq(
            null,
            $json_data->titulo,
            $json_data->descripcion
        );

        $titulo = $faq->getTitulo();
        $descripcion = $faq->getDescripcion();

        try
        {
            $query = $connection->prepare('SELECT id, titulo, descripcion FROM faq WHERE titulo = :titulo');
            $query->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount !== 0)
            {
                $response = new Response ();
                $response->setHttpStatusCode(409);
                $response->setSuccess(false);
                $response->addMessage("La pregunta ya existe");
                $response->send();
                exit();
            }

            $query = $connection->prepare('INSERT INTO faq(titulo,descripcion) VALUES (:titulo,:descripcion)');
            $query->bindParam(':titulo',$titulo,PDO::PARAM_STR);
            $query->bindParam(':descripcion',$descripcion,PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0)
            {
                $response = new Response ();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al crear la pregunta - Trata de nuevo");
                $response->send();
                exit();
            }

            $ultimoID = $connection->lastInsertId();

            $returnData = array();
            $returnData['id'] = $ultimoID;
            $returnData['titulo'] = $titulo;
            $returnData['descripcion'] = $descripcion;

            $response = new Response ();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("FAQ creada");
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
            $response->addMessage("Error al crear FAQ");
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