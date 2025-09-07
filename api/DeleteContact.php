<?php
declare(strict_types=1);

header('Content-Type: application/json');

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "DELETE"){
    $data = [
        "status" => "error",
        "errType" => "InvalidRequest",
        "desc" => "Method $method is invalid"
    ];

    http_response_code(400);
    echo json_encode($data);
    exit();
}


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === null){
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "errType" => "InvalidSchema",
        "desc" => "Missing Contact ID"
    ]);
    exit();
}elseif ($id === false){
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "errType" => "InvalidInputData",
        "desc" => "Invalid Contact ID"
    ]);
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

$query = $db->prepare("DELETE FROM Contacts WHERE ID = ?");
$query->bind_param("i", $id);

try{
    $query->execute();

    if ($query->affected_rows > 0){
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "contactDeleted" => true
        ]);
    }
    else{
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "contactDeleted" => false,
            "errType" => "NonExistentContactError",
            "desc" => "Contact not found"
        ]);
    }
} catch (Exception $e){
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "contactDeleted" => false,
        "errType" => "ContactDeletionError",
        "desc" => "Failed to delete contact"
    ]);
} finally {
    $query->close();
    $db->close();
}
