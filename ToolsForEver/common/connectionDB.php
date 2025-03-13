<?php 
$servername = "mysql";
$username = "ToolForEver";
$password = "fHFNh3A5g";

try{
    $conn = new mysqli($servername, $username, $password, 'ToolForEverV2');
    if ($conn->connect_error) {
        error_log($conn->connect_error);
        exit("Connection DB failed");
      }
}catch(Exception $e){
    error_log($e);
    exit("Connection DB failed");
}

return $conn;
?>