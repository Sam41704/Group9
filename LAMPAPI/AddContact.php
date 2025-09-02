<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try{
  $in=inData();
  $userId=(int)($in['userId']??0);
  $first=trim($in['firstName']??''); $last=trim($in['lastName']??'');
  $phone=trim($in['phone']??''); $email=trim($in['email']??'');

  if($userId<=0||$first===''||$last===''){ out(["id"=>0,"error"=>"Missing required fields (userId, firstName, lastName)"]); exit; }

  $conn=new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  $stmt=$conn->prepare("INSERT INTO Contacts (FirstName,LastName,Phone,Email,UserID) VALUES (?,?,?,?,?)");
  $stmt->bind_param("ssssi",$first,$last,$phone,$email,$userId);
  $stmt->execute();
  $newId=$conn->insert_id;

  out(["id"=>(int)$newId,"firstName"=>$first,"lastName"=>$last,"phone"=>$phone,"email"=>$email,"userId"=>$userId,"error"=>""]);
}catch(Throwable $e){ out(["id"=>0,"error"=>"Server error"]); }
