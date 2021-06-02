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

    // set response code
    http_response_code(200);

    // show user details
    /*echo json_encode(array(
        "message" => "Access granted.",
        "data" => $decoded->data["id"]);*/
    $uid=$decoded->data->id;
    $myConn=new myConn("bots");
    $botConds=[];
    if($myConn->connectToDB($servername,$dbname,$username,$password)){
      $botConds=["userID="=>$uid,
                 "and isDeleted="=>0];
      if ($myConn->prepareSelect(['*'],$botConds)){
        if($myConn->run(null,$botConds)){
          $result=$myConn->getSelectResult();
          echo json_encode(array("message"  => "Success",
                                 "botsList" => $result));
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
      echo json_encode(array(
          "message" => "Access denied.",
          "error" => $e->getMessage()
      ));
      //header('Location: ../../'); 
  }
}else{ // show error message if jwt is empty 
  // set response code
  http_response_code(401);

  // tell the user access denied
  echo json_encode(array("message" => "Access denied."));
  //header('Location: ../../'); 
}


