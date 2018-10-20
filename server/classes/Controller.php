<?php

require_once("App.php");

class Controller
{
    // App Object, hold the connection and table information
    private $app;

    // Expecting array of field names AS THEY APPEAR IN THE DB!

    public function __construct($app)
    {
        $this->app = $app;
        $this->app->connectToDB();
    }

    /**
     * Create a record. Recives a raw HTTP request in json forms, and validates that all the fields are ok.
     */
    public function create($json){
        $data = json_decode($json, true);
        $active_fields = $this->getActiveFields($data);
        
        // We check to make sure the HTML form hasn't been changed, and to prevent certain mySQL errors.
        if(sizeof($active_fields) === 0){
            return '{"status": 0, "desc": "Error: The field names in the HTTP request were invalid."}';
        }

        $sql = "INSERT INTO ".$this->app->tableName." (";

        // Construct the field name part of the SQL statment.
        foreach ($active_fields as $field) {
            $sql .= $field[0].", "; 
        }
        $sql = rtrim($sql, ", ");
        $sql .= ")";

         // Construct the VALUES part of the SQL statment, in prepared statment syntax (:fieldName).
        $sql .= " VALUES (";
        foreach ($active_fields as $field) {
            $sql .= ":".$field[0].", ";
        }
        $sql = rtrim($sql, ", ");
        $sql .= ")";

        // Prepare the SQL statment and bind the parameters (which are indexed because of the array).
        $stmt = $this->app->db->prepare($sql);
        foreach ($active_fields as $field) {
            $stmt->bindParam(":".$field[0], $field[1]);
        }
        if($stmt->execute()){
            return '{"status": 1, "desc": "Row Created!"}';
        }
        return '{"status": 0, "desc": "SQL Error!"}';
        
    }



    /**
     *  Currently supports reading the entire table only.
     * We check to make sure the HTML form hasn't been changed, and to prevent certain mySQL errors.
     */
    public function read(){
        $stmt = $this->app->db->prepare("SELECT * FROM ".$this->app->tableName);
        if($stmt->execute()){
            return json_encode($stmt->fetchAll());
        }
        return '{"status": 0, "desc": "SQL Error!"}';
    }

    /**
     * Updates records in database.
     */
    public function update($json){
        $data = json_decode($json, true);
        $rowID = $this->getID($data);
        if($rowID === null){
            return '{"status": 0, desc: "Error: No row ID given."}';
        }
        unset($data["id"]);

        $active_fields = $this->getActiveFields($data);

        // We check to make sure the HTML form hasn't been changed, and to prevent certain mySQL errors.
        if(sizeof($active_fields) === 0){
            return '{"status": 0, "desc": "Error: The field names in the HTTP request were invalid."}';
        }

        $sql = "UPDATE ".$this->app->tableName." SET ";

        foreach($active_fields as $field){
            $sql .= $field[0]." = :".$field[0].", ";
        }

        $sql = rtrim($sql, ", ");

        $sql .= " WHERE id = :id";

        // Prepare the SQL statment and bind the parameters (which are indexed because of the array).
        $stmt = $this->app->db->prepare($sql);
        foreach ($active_fields as $field) {
            $stmt->bindParam(":".$field[0], $field[1]);
        }
        $stmt->bindParam(":id", $rowID);
        if($stmt->execute()){
            return '{"status": 1, "desc": "Row with ID '.$rowID.' was updated!"}';
        }
        return '{"status": 0, "desc": "SQL Error!"}';
    }


    /**
     * Delete multiple records.
     */
    public function delete($json){
        $data = json_decode($json);
        $sql = "DELETE FROM ".$this->app->tableName." WHERE id in (";
        foreach($data as $index => $idNum){
            $sql .= "?, ";
        }
        $sql = rtrim($sql, ", ");
        $sql .= ")";

        $stmt = $this->app->db->prepare($sql);
        if($stmt->execute($data)){
            return '{"status": 1, "desc": "'.sizeof($data).' rows deleted succesfully"}';
        }
        return '{"status": 0, "desc": "SQL Error!"}';
    }
    
    /**
     *  Gets a PHP array with the request data. Validates that tCreatedhe keys match the field names. 
     *  If at least 1 of the keys is incorrect, the request is deemed invalid and an empty array is returned.
     */
    public function getActiveFields($data){
        $allFields = $this->app->tableFields;
        $activeFields = [];
        foreach ($data as $name => $value) {
            $matchFound = false;
            foreach ($allFields as $DBName) {
                $DBName = $DBName["nameInTable"];
                if($name === $DBName){
                    array_push($activeFields, [$name, $value]);
                    $matchFound = true;
                    break;
                }
            }
        }

        if (sizeof($activeFields) === 0 || sizeof($activeFields) < sizeof($data)) {
            return [];
        }
        return $activeFields;
    }

    public function getID($data){
        if(!array_key_exists("id", $data)){
            return null;
        }
        $id = $data["id"];
        return $id;
    }
}


?>