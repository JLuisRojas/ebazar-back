<?php
class DB {
  private static $db;

  public static function init(){
    if (!self::$db) {
      try {
        $servername = "localhost";
        $database = "ebazar";
        $port = "80";

        $username = "root";
        $password = "";

        $dns = "mysql:host=$servername;dbname=$database;port=$port;";

        $db = new PDO($dns, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        self::$db = $db;
      } catch (PDOException $e) {
        die('Connection error: ' . $e->getMessage());
      }
    }   
    return self::$db;
  }

}
?>
