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

// GET server/buscar?titulo=Fulano
if($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        $max = 10;           // Maximo numero de elementos por pagina

        if(array_key_exists("pag", $_GET)) {
            $pag = $_GET['pag'];
        }

        /*
        if(array_key_exists("max", $_GET)) {
            $pag = $_GET['max'];
        }*/

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

        $productos = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $producto = Producto::fromArray($row);
            $productos[] = $producto->getArray();
        }

        // Formato de los datos del producto
        $busquedaData = [
            'pagina' => $pag,
            'totalPaginas' => $totalPag,
            'totalResultados' => $totalResultados,
            'resultados' => $resultados,
            'consulta' => $titulo,
            'productos' => array_map(function($producto) {
                return [
                    'id' => $producto['id'],
                    'titulo' => $producto['titulo'],
                    'precio' => $producto['precio'],
                    'disponibles' => $producto['disponibles'],
                    'ubicacion' => $producto['ubicacion']
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

    } else {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El metodo no tiene campo de id");
        $response->send();
        exit();
    }
}
?>