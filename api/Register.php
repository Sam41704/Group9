<?php
header('Content-Type: application/json');

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "POST"){
    $data = [
        "status" => "error",
        "errType" => "InvalidRequest",
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
        "status" => "error",
        "errType" => "InvalidJson",
        "desc" => "Invalid payload sent"
    ]);
    exit();
}

if (isset($payload["firstName"],
        $payload["lastName"],
        $payload["username"],
        $payload["passwordHash"]) === false){
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "errType" => "InvalidSchema",
        "desc" => "Invalid request schema"
    ]);
    exit();
}

try{
    // make mysqli throw exceptions v.s. silent failures
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $dbUser = getenv("CONTACTS_APP_DB_USER");
    $dbPassword = getenv("CONTACTS_APP_DB_PASS");
    $dbName = getenv("CONTACTS_APP_DB_NAME");
    $db = new mysqli("localhost", $dbUser, $dbPassword, $dbName);

} catch(Exception $e){
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "errType" => "ServerError",
        "desc" => "Failed to make DB connection"
    ]);
    exit();
}

if (userExists($db, $payload["username"])){
    http_response_code(200);

    echo json_encode([
       "status" => "success",
       "userCreated" => false,
       "reason" => "UserAlreadyExists"
    ]);
    exit();
}

$query = $db->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)");
$query->bind_param(
    "ssss",
    $payload["firstName"],
    $payload["lastName"],
    $payload["username"],
    $payload["passwordHash"]);


try {
    $query->execute();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "userCreated" => true
    ]);

} catch (mysqli_sql_exception $e){
    http_response_code(500);

    $err = $e->getTraceAsString();
    error_log("SQL query execution error: $err");

    echo json_encode([
        "status" => "error",
        "userCreated" => false,
        "errType" => "UserCreationError",
        "desc" => "Failed to create user"
    ]);
}

$query->close();
$db->close();

function userExists(mysqli $conn, string $user) {
    $query = $conn->prepare("SELECT id FROM Users WHERE Login=?");
    $query->bind_param("s", $user);

    $query->execute();
    $result = $query->get_result();

    if ($result->fetch_assoc())
        $returnValue = true;
    else
        $returnValue = false;

    $query->close();
    return $returnValue;
}

function getRequestPayload(): array{
    return json_decode(file_get_contents("php://input"), true) ?? [];
}
