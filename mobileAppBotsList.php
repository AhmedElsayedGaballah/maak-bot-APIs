<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once '../../config/db.php';
require_once '../../libs/php/myClasses/maaktable.php';
include_once '../../config/core.php';
include_once '../../libs/php/php-jwt-master/src/BeforeValidException.php';
include_once '../../libs/php/php-jwt-master/src/ExpiredException.php';
include_once '../../libs/php/php-jwt-master/src/SignatureInvalidException.php';
include_once '../../libs/php/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
$data = json_decode(file_get_contents("php://input"));
$jwt=$data->token;

if($jwt){
 
  // if decode succeed, show user details
  try {
    // decode jwt
    $decoded = JWT::decode($jwt, $key, array('HS256'));
    $uid=$decoded->data->id;
    $myConn=new myConn("users");
    if($myConn->connectToDB($servername,$dbname,$username,$password)){
      ////check token
      $userConds=["userID="=>$uid];
      if($myConn->prepareSelect(["AppToken"],$userConds)){        
        if($myConn->run(null,$userConds)){
          $userResult=$myConn->getSelectResult();
          if(htmlspecialchars($jwt)!==$userResult[0]["AppToken"]){
            http_response_code(401);
            die("Bad Token");
          }
        }
      }
      ////end check token
      $myConn->table("bots");
      $botConds=[];
      $botConds=["userID="=>$uid,
                 "and isDeleted="=>0];
      if ($myConn->prepareSelect(['botID','botName','botIcon'],$botConds)){
        if($myConn->run(null,$botConds)){
          $result=$myConn->getSelectResult();
          echo json_encode($result);
        }else{
          echo "prepare Select error";
      }
      }else{
          echo "run Select error";
      }
    }        
  } 
  // if decode fails, it means jwt is invalid
  catch (Exception $e){
   
      // set response code
      http_response_code(401);
   
      // tell the user access denied  & show error message
      /*echo json_encode(array(
          "message" => "Access denied.",
          "error" => $e->getMessage()
      ));*/
      //header('Location: ../../'); 
  }
}else{ // show error message if jwt is empty 
  // set response code
  http_response_code(401);

  // tell the user access denied
  /*echo json_encode(array("message" => "Access denied."));*/
  //header('Location: ../../'); 
}


