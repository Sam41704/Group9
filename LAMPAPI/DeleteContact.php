<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try{
  $in=inData();
  $userId=(int)($in['userId']??0);
  $contactId=(int)($in['contactId']??0);
  if($userId<=0||$contactId<=0){ out(["error"=>"Missing userId or contactId"]); exit; }

  $conn=new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  // only delete if contact belongs to user
  $stmt=$conn->prepare("DELETE FROM Contacts WHERE ID=? AND UserID=?");
  $stmt->bind_param("ii",$contactId,$userId);
  $stmt->execute();

  out(["error"=>""]);
}catch(Throwable $e){ out(["error"=>"Server error"]); }
