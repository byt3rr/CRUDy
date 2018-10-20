<?php

require_once("classes/App.php");
require_once("classes/Controller.php");

    // ==== EDIT THIS ROW ROWS ==== //
    
    
    define("INI_FILE_PATH", "config.ini");
    define("TABLE_NAME", "TestTable1");


    // ===== EVERYTHING BELOW HERE SHOULD STAY AS IT IS ======
    $jsonFile = fopen("columns.json", "r");
    $fields = json_decode(fread($jsonFile, filesize("columns.json")), true);
    $app = new App(TABLE_NAME, $fields,  INI_FILE_PATH);

    header("Content-Type: application/json");
    $request_type = $_SERVER["REQUEST_METHOD"];
    $controller = new Controller($app);

    switch ($request_type) {
        case 'POST':
            $json = file_get_contents('php://input');
            if(json_decode($json) == null){
                echo '{"status": 4, "desc": "Invalid JSON!"}';
            } else {
                echo $controller->create($json);
            }
            break;
        
        case 'GET':
            echo $controller->read();
            break;
        
        case 'PUT':
        $json = file_get_contents('php://input');
            if(json_decode($json) == null){
                echo '{"status": 4, "desc": "Invalid JSON!"}';
            } else {
                echo $controller->update($json);
            }
            break;
        
        case 'DELETE':
            $json = file_get_contents('php://input');
            if(json_decode($json) == null){
                echo '{"status": 4, "desc": "Invalid JSON!"}';
            } else {
                echo $controller->delete($json);
            }
            break;
        
        default:
            break;
    }

?>