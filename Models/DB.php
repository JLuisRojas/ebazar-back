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
          self::$connection = new PDO('mysql:host=localhost;dbname=ebazar;charset=utf8','root','');
          self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      }
      
      return self::$connection;
  }
}
?>
