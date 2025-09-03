<?php

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "POST"){
    $data = [
        "status" => "ERROR",
        "errType" => "INVALID_REQUEST",
        "desc" => "Method $method is invalid"
    ];

    http_response_code(400);
    echo json_encode($data);
    exit();
}

// validating payload
$payload = getRequestPayload();

if (json_last_error() != JSON_ERROR_NONE){
    http_response_code(400);

    echo json_encode([
        "status" => "ERROR",
        "errType" => "InvalidJson",
        "desc" => "Invalid payload sent"
    ]);
    exit();
}

if (isset($payload["username"], $payload["passwordHash"]) === false){
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "errType" => "InvalidSchema",
        "desc" => "Invalid request schema"
    ]);
    exit();
}

try{
//     setting up db connection
    $dbUser = getenv("CONTACTS_APP_DB_USER");
    $dbPassword = getenv("CONTACTS_APP_DB_PASS");
    $dbName = getenv("CONTACTS_APP_DB_NAME");
    $db = new mysqli("localhost", $dbUser, $dbPassword, $dbName);

} catch (Exception $e){
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "errType" => "ServerError",
        "desc" => "Failed to make DB connection"
    ]);
    exit();
}

$query = $db->prepare("SELECT ID, FirstName, LastName FROM Users WHERE (Login=? AND Password=?)");
$query->bind_param("ss", $payload["username"], $payload["passwordHash"]);

// TODO wrap in try-catch
$query->execute();
$result = $query->get_result();

processQueryResult($result);

$query->close();
$db->close();


function getRequestPayload(){
    return json_decode(file_get_contents("php://input"), true);
}

function processQueryResult($result){

    if ($row = $result->fetch_assoc()) {
        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "isAuthenticated" => true,
            "firstName" => $row["FirstName"],
            "lastName" => $row["LastName"],
            "userId" => $row["ID"]
        ]);
    }else{
        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "isAuthenticated" => false
        ]);
    }
}