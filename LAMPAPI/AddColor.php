<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try {
  $in = inData();
  $name = trim($in['name'] ?? '');
  if ($name===''){ out(["id"=>0,"error"=>"Missing color name"]); exit; }

  $conn = new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  // UserID column exists; use 0 as global bucket
  $uid = 0;
  $stmt = $conn->prepare("INSERT INTO Colors (Name, UserID) VALUES (?, ?)");
  $stmt->bind_param("si", $name, $uid);
  $stmt->execute();

  out(["id"=>$conn->insert_id,"name"=>$name,"error"=>""]);
} catch(Throwable $e) {
  out(["id"=>0,"error"=>"Server error"]);
}
?>
