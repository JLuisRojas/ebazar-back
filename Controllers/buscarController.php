<?php
// Metodo que realiza la busqueda de productos en la base de datos
// FALTA TRY DE CREAR PRODUCTO

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
    if (strtotime($consulta_cadTokenAcceso) + 6001 + 1200 < time()) 
    //if (strtotime($consulta_cadTokenAcceso) < time()) 
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


//GET buscar/id_depa=
if(array_key_exists("id_depa", $_GET))
{
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    {
        $id_depa = $_GET['id_depa'];
    
        if ($id_depa === '' || !is_numeric($id_depa)) 
        {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El id del Departamento no puede estar vacío y debe ser numérico");
            $response->send();
            exit();
        }

        try 
        {
                
            $query = $connection->prepare('SELECT id, id_usuario, id_departamento, titulo, ubicacion,
            descripcion_corta, descripcion_larga, precio, vendidos, disponibles, caracteristicas, 
            habilitado, img, comentarios FROM productos WHERE id_departamento = :id_depa');
            $query->bindParam(':id_depa', $id_depa, PDO::PARAM_INT);
            $query->execute();
        
            $rowCount = $query->rowCount();
        
            if($rowCount === 0) 
            {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No hay productos en esta categoría");
                $response->send();
                exit();
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC))
            {
                $producto = new Producto($row ['id'], $row['id_usuario'], $row['id_departamento'], $row['titulo'], 
                $row['ubicacion'], $row['descripcion_corta'], $row['descripcion_larga'], $row['precio'], $row['vendidos'],
                $row['disponibles'], $row['caracteristicas'], $row['habilitado'], $row['img'], $row['comentarios'] );
            
                $productos[] = $producto->getArray();
            }

                $returnData = array();
                $returnData['total_productos'] = $rowCount;
                $returnData['productos'] = $productos;
    
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->setToCache(true);
                $response->setData($returnData);
                $response->send();
                exit();
        }
        catch(ProductoException $e)
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
            $response->addMessage("Error en consulta de productos");
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
// GET server/buscar?titulo=Fulano
else if($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(array_key_exists("titulo", $_GET)) {
        $titulo = $_GET["titulo"];
        if($titulo == '' || !is_string($titulo)){
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El campo de titulo de producto no puede estar vacio o ser diferente a una cadena");
            $response->send();
            exit();
        }

        // Parametros opcionales
        $pag = 1;           // Pagina actual
        $max = 2;           // Maximo numero de elementos por pagina

        if(array_key_exists("pag", $_GET)) {
            $pag = $_GET['pag'];
        }

        /*
        if(array_key_exists("max", $_GET)) {
            $pag = $_GET['max'];
        }*/

        try {
            // Consulta de los productos
            $sql = "SELECT * FROM productos WHERE MATCH(titulo) AGAINST('$titulo')";
            $query = $connection->prepare($sql);
            $query->execute();

            $totalResultados = $query->rowCount();
            $totalPag = ceil($totalResultados / $max); 
            if($pag * $max > $totalResultados) {
                $resultados = $totalResultados - ($pag - 1) * $max;
            } else {
                $resultados = $max;
            }

            $pagG = $pag - 1;
            $inicio = $pagG * $max;

            $numProd = 0;
            $contador = 1;
            $productos = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                if($numProd < $max && $contador > $inicio) {
                    $numProd = $numProd + 1;
                    $producto = Producto::fromArray($row);
                    $productos[] = $producto->getArray();
                }

                $contador = $contador + 1;
            }

            // Formato de los datos del producto
            $busquedaData = [
                'pagina' => $pag,
                'totalPaginas' => $totalPag,
                'totalResultados' => $totalResultados,
                'resultados' => count($productos),
                'consulta' => $titulo,
                'productos' => array_map(function($producto) {
                    return [
                        'id' => $producto['id'],
                        'titulo' => $producto['titulo'],
                        'precio' => $producto['precio'],
                        'disponibles' => $producto['disponibles'],
                        'ubicacion' => $producto['ubicacion'],
                        'img' =>$producto['img']
                    ];
                }, $productos)
            ];

            // Response todo bien
            $returnData['busqueda'] = $busquedaData;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit(); 
        } catch(ProductoException $e)
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
            $response->addMessage("Error en consulta de productos");
            $response->send();
            exit();
        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de titulo a buscar");
        $response->send();
        exit();
    }
}
?>