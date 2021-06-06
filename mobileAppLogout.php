<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// files needed to connect to database
include_once '../app/config/db.php';
require_once '../app/libs/php/myClasses/maaktable.php';
include_once '../app/config/core.php';
include_once '../app/libs/php/php-jwt-master/src/BeforeValidException.php';
include_once '../app/libs/php/php-jwt-master/src/ExpiredException.php';
include_once '../app/libs/php/php-jwt-master/src/SignatureInvalidException.php';
include_once '../app/libs/php/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
$data = json_decode(file_get_contents("php://input"));
////////////////////////////////
/////////////////////////////////
$jwt=$data->token;
//echo $jwt;
// if jwt is not empty
if($jwt){
 
  // if decode succeed, show user details
  try {
    // decode jwt
    $decoded = JWT::decode($jwt, $key, array('HS256'));
    //echo json_encode($decoded); 
    
    // show user details
    /*echo json_encode(array(
        "message" => "Access granted.",
        "data" => $decoded->data["id"]);*/
    $uid=$decoded->data->id;
    //echo $uid;
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
        
      $updateToken["AppToken"]=null;
      $updateTokenConds=["userID="=>$uid];
      if($myConn->prepareUpdate($updateToken,$updateTokenConds)){
        if($myConn->run($updateToken,$updateTokenConds)){
          // set response code
          http_response_code(200);
          // generate jwt                
          echo  1;
        }
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
////////////////////////////////
////////////////////////////////

?>