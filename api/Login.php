<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try {
  $in = inData();
  $login = $in['login'] ?? '';a
  $password = $in['password'] ?? '';

  $conn = new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  $stmt = $conn->prepare("SELECT ID, FirstName, LastName FROM Users WHERE Login=? AND Password=? LIMIT 1");
  $stmt->bind_param("ss",$login,$password);
  $stmt->execute(); $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    out(["id"=>(int)$row['ID'],"firstName"=>$row['FirstName'],"lastName"=>$row['LastName'],"error"=>""]);
  } else {
    out(["id"=>0,"firstName"=>"","lastName"=>"","error"=>"No Records Found"]);
  }
} catch(Throwable $e) {
  out(["id"=>0,"firstName"=>"","lastName"=>"","error"=>"Server error"]);
}
?>
