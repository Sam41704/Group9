<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try {
  $in = inData();
  $search = trim($in['search'] ?? '');

  $conn = new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  if ($search==='') {
    $stmt = $conn->prepare("SELECT ID, Name FROM Colors ORDER BY Name");
  } else {
    $like = "%$search%";
    $stmt = $conn->prepare("SELECT ID, Name FROM Colors WHERE Name LIKE ? ORDER BY Name");
    $stmt->bind_param("s", $like);
  }

  $stmt->execute(); $res = $stmt->get_result();
  $rows = [];
  while($r = $res->fetch_assoc()){
    $rows[] = ["id"=>(int)$r['ID'], "name"=>$r['Name']];
  }
  out(["results"=>$rows,"error"=>""]);
} catch(Throwable $e) {
  out(["results"=>[],"error"=>"Server error"]);
}
?>
