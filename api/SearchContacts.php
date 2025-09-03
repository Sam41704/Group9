<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try{
  $in=inData();
  $userId=(int)($in['userId']??0);
  $term=trim($in['search']??'');

  if($userId<=0){ out(["results"=>[],"error"=>"Missing or invalid userId"]); exit; }

  $conn=new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  if($term===''){
    $stmt=$conn->prepare("SELECT ID,FirstName,LastName,Phone,Email FROM Contacts WHERE UserID=? ORDER BY LastName,FirstName");
    $stmt->bind_param("i",$userId);
  }else{
    $like="%$term%";
    $stmt=$conn->prepare(
      "SELECT ID,FirstName,LastName,Phone,Email
       FROM Contacts
       WHERE UserID=? AND (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?)
       ORDER BY LastName,FirstName");
    $stmt->bind_param("issss",$userId,$like,$like,$like,$like);
  }
  $stmt->execute(); $res=$stmt->get_result();
  $rows=[];
  while($r=$res->fetch_assoc()){
    $rows[]=["id"=>(int)$r['ID'],"firstName"=>$r['FirstName'],"lastName"=>$r['LastName'],"phone"=>$r['Phone'],"email"=>$r['Email']];
  }
  out(["results"=>$rows,"error"=>""]);
}catch(Throwable $e){ out(["results"=>[],"error"=>"Server error"]); }
