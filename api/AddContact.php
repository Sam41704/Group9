<?php
declare(strict_types=1);

header('Content-Type: application/json');

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

if (isset($payload["firstName"],
        $payload["lastName"],
        $payload["phone"],
        $payload["email"],
        $payload["userId"]) === false){
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "errType" => "InvalidSchema",
        "desc" => "Invalid request schema"
    ]);
    exit();
}

try{
    // setting up db connection
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


function contactExists(mysqli $conn, array $payload): bool{
    # gathers count of rows of contacts that meet the given conditions
    $query = $conn->prepare("SELECT COUNT(*) FROM Contacts WHERE (Email = ? AND UserId = ?)");
    $query->bind_param("si", $payload["email"], $payload["userId"]); // TODO need to check if this is proper way to describe int

    $query->execute(); // TODO cont here
    return true;
}

function getRequestPayload(): array{
    return json_decode(file_get_contents("php://input"), true);
}