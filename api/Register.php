<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function inData(){ $j=file_get_contents('php://input'); return $j?json_decode($j,true):[]; }
function out($o){ echo json_encode($o, JSON_UNESCAPED_UNICODE); }

try{
  $in=inData();
  $first=trim($in['firstName']??''); $last=trim($in['lastName']??'');
  $login=trim($in['login']??''); $password=trim($in['password']??'');
  if($first===''||$last===''||$login===''||$password===''){ out(["id"=>0,"error"=>"Missing fields"]); exit; }

  $conn=new mysqli("localhost","TheBeast","WeLoveCOP4331","COP4331");
  $conn->set_charset('utf8mb4');

  // prevent duplicate login
  $ck=$conn->prepare("SELECT ID FROM Users WHERE Login=? LIMIT 1");
  $ck->bind_param("s",$login); $ck->execute(); $r=$ck->get_result();
  if($r->num_rows){ out(["id"=>0,"error"=>"Login already exists"]); exit; }

  $stmt=$conn->prepare("INSERT INTO Users (FirstName,LastName,Login,Password) VALUES (?,?,?,?)");
  $stmt->bind_param("ssss",$first,$last,$login,$password);
  $stmt->execute();
  out(["id"=>$conn->insert_id,"error"=>""]);
}catch(Throwable $e){ out(["id"=>0,"error"=>"Server error"]); }
