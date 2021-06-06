<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// files needed to connect to database
include_once '../app/config/db.php';
require_once '../app/libs/php/myClasses/maaktable.php';
 

$data = json_decode(file_get_contents("php://input"));
/*$data->email=$_POST["email"];
$data->pass=$_POST["pass"];*/

$email=$data->email;
$upassword=$data->password;
//$userFields=[];
//$userFields['userPassword']=password_hash(,PASSWORD_DEFAULT);
 

include_once '../app/config/core.php';
include_once '../app/libs/php/php-jwt-master/src/BeforeValidException.php';
include_once '../app/libs/php/php-jwt-master/src/ExpiredException.php';
include_once '../app/libs/php/php-jwt-master/src/SignatureInvalidException.php';
include_once '../app/libs/php/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

$myConn=new myConn("users");
$userConds=[];
if($myConn->connectToDB($servername,$dbname,$username,$password)){
  $userConds=["userEmail="=>$email];
    if($myConn->prepareSelect(['*'],$userConds)){
      if($myConn->run(null,$userConds)){
        $result=$myConn->getSelectResult();
        if($myConn->getSelectRowNumbers()){
          $userPass=$result[0]['userPassword'];
          if (password_verify($upassword,$userPass)){      
            $token = array(
              "iat" => $issued_at,
              "exp" => $expiration_time,
              "iss" => $issuer,
              "data" => array(
                  "id" =>$result[0]['userID'],
                  "email" => $result[0]['userEmail']
              )
            );
            $jwt = JWT::encode($token, $key);
            $updateToken["AppToken"]=htmlspecialchars($jwt);
            $updateTokenConds=["userID="=>$result[0]['userID']];
            if($myConn->prepareUpdate($updateToken,$updateTokenConds)){
              if($myConn->run($updateToken,$updateTokenConds)){
                // set response code
                http_response_code(200);
                // generate jwt                
                echo  $jwt;
              }
            }
            
        
          }else{ 
              // set response code
              http_response_code(401);
              // tell the user login failed
              echo json_encode(array("message" => "Login failed."));
          }
        }else{
          echo json_encode(array("message" => "Bad Email."));
        }
      }else{
        echo json_encode(array("message" => "DB runSelect Error."));
      }
    }else{
      echo json_encode(array("message" => "DB prepareSelect Error."));
    }
}else{
  echo json_encode(array("message" => "DB Connection Error."));
    
}
              

?>