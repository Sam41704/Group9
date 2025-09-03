<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try{
  $in = inData();
  $userId    = (int)($in['userId'] ?? 0);
  $contactId = (int)($in['contactId'] ?? 0);
  $first     = trim($in['firstName'] ?? '');
  $last      = trim($in['lastName'] ?? '');
  $phone     = trim($in['phone'] ?? '');
  $email     = trim($in['email'] ?? '');

  if($userId<=0 || $contactId<=0 || $first==='' || $last===''){
    out(["error"=>"Missing required fields (userId, contactId, firstName, lastName)"]);
    exit;
  }

  $conn = new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  $stmt = $conn->prepare(
    "UPDATE Contacts
     SET FirstName=?, LastName=?, Phone=?, Email=?
     WHERE ID=? AND UserID=?"
  );
  $stmt->bind_param("ssssii", $first, $last, $phone, $email, $contactId, $userId);
  $stmt->execute();

  out(["error"=>""]);
}catch(Throwable $e){
  out(["error"=>"Server error"]);
}
?>
