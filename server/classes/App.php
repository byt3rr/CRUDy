<?php
class App
{
    public $db;
    public $tableName;
    public $tableFields;
    public $ini;

    public function __construct($tableName, $tableFields,  $filePath){
        $this->tableName = $tableName;
        $this->tableFields = $tableFields;
        $this->ini = parse_ini_file($filePath);
    }

    public function connectToDB(){
        try {
            $host = $this->ini['host'];
            $dbname = $this->ini['db_name'];
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $this->ini['username'], $this->ini['password']);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db = $conn;
            }
        catch(PDOException $e)
            {
            echo "Connection failed: " . $e->getMessage();
            }
    }
}


?>
