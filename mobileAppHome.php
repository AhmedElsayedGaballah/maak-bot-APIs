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
        
      $myConn->table("bots");
      $botConds=[];      
      $botConds=["userID="=>$uid,
                 "and isActive="=>1];
      if ($myConn->prepareSelect(['*'],$botConds)){
        if($myConn->run(null,$botConds)){
          $activeBots=$myConn->getSelectResult();
          $activeBotsCount=$myConn->getSelectRowNumbers();
          ////////////////////////
          $myConn->table("visitor_session_conversations");
          $allConversationConds=[];
          $allConversationConds=["userID="=>$uid];
          if ($myConn->prepareSelect(['*'],$allConversationConds)){
            if($myConn->run(null,$allConversationConds)){
              $allConversations=$myConn->getSelectResult();
              $allConversationsCount=$myConn->getSelectRowNumbers();
              //////////////////////
              $myConn->table("user_contacts");
              $contactConds=[];
              $contactConds=["userID="=>$uid];
              if ($myConn->prepareSelect(['*'],$contactConds)){
                if($myConn->run(null,$contactConds)){
                  $contacts=$myConn->getSelectResult();
                  $contactsCount=$myConn->getSelectRowNumbers();
                  /////////////////////////
                  $myConn->table("visitor_session_conversations");
                  $newConversationConds=[];
                  $newConversationConds=["userID="=>$uid,
                                      "and conversationStatusID="=>1];
                  if ($myConn->prepareSelect(['*'],$newConversationConds)){
                    if($myConn->run(null,$newConversationConds)){
                      $newConverstaions=$myConn->getSelectResult();
                      $newConverstaionsCount=$myConn->getSelectRowNumbers();
                      /////////////////////
                      $myConn->table("user_profile");
                      $usersConds=[];
                      $usersConds=["userID="=>$uid];
                      if ($myConn->prepareSelect(['*'],$usersConds)){
                        if($myConn->run(null,$usersConds)){
                          $users=$myConn->getSelectResult();
                          //echo json_encode($users);
                          // set response code
                          http_response_code(200);
                          echo json_encode(array( "firstName"         =>$users[0]["firstName"],
                                                  "lastName"          =>$users[0]["lastName"],
                                                  "activeBots"        => $activeBotsCount,
                                                  "allConversations"  => $allConversationsCount,
                                                  "newConverstaions"  => $newConverstaionsCount,
                                                  "contacts"          =>$contactsCount));
                        }else{
                          echo "userProfile run Select error";
                      }
                      }else{
                          echo "userProfile prepare Select error";
                      }
                      /////////////////////                              
                    }else{
                      echo "newConverstaionsCount prepare Select error";
                  }
                  }else{
                      echo "newConverstaionsCount run Select error";
                  }
                  /////////////////////////
                }else{
                  echo "contacts prepare Select error";
              }
              }else{
                  echo "contacts run Select error";
              }
              //////////////////////
            }else{
              echo "allConversations prepare Select error";
          }
          }else{
              echo "allConversations run Select error";
          }
          ////////////////////////
        }else{
          echo "activeBots prepare Select error";
      }
      }else{
          echo "activeBots run Select error";
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

?>
