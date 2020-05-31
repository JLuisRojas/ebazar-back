<?php
/*class DB {
  private static $connection;

  public static function init(){
    if (!self::$connection) {
      try {
        $servername = "localhost";
        $database = "ebazar";
        $port = "80";

        $username = "root";
        $password = "";

        $dns = "mysql:host=$servername;dbname=$database;port=$port;";

        $connection = new PDO($dns, $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        self::$connection = $connection;
      } catch (PDOException $e) {
        die('Connection error: ' . $e->getMessage());
      }
    }   
    return self::$db;
  }
}*/
class DB{
  private static $connection;

  public static function init()
  {
      if(self::$connection === null)
      {
          self::$connection = new PDO('mysql:host=localhost;dbname=e;charset=utf8','root','');
          //La linea de abajo ya funciona :D
          //self::$connection = new PDO('mysql:host=localhost;dbname=id13861275_ebazar;charset=utf8','id13861275_root','Fundamentos$$$99');
          self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      }

      //Para hacer consultas con Postman es: https://ebazarumx.000webhostapp.com/...[Link]

      return self::$connection;
  }

}
?>
