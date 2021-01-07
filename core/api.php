<?php 
// TRAWO CORE v. 1.0.7
// Build by Marcus Knoph

include 'func.php';
include 'def.php';

//Create connection to DB
$conn = new mysqli($db_stat['db_addr'], $db_stat['db_user'], $db_stat['db_password'], $db_stat['db_data']);
if ($conn->connect_error) {
    if($LOGERROR == TRUE) {
        $myfile = file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
    }
    exit;
}

//Task List
$upload_image = "uploadimage";

$update_profile = "updateprofile";

$get_contactlist = "getcontactlist";
$get_contactdetail = "getcontactdetail";
$get_trackerlist = "maillist";
$get_log = "log";
$get_validation_tracker = "valid";
$get_user = "getuser";

$set_contactdetail = "setcontactdetail";

$del_contact_no_restric = "delcon";
$del_tracker = "del";
$del_event = "delevent";

$create_tracker = "createtracker";
$validAPI = "validapi";


//Check if correct access is being used
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if(strpos($contentType, 'application/json') !== false) {

        //Get JSON POST
        $content = trim(file_get_contents('php://input'));
        $decoded = json_decode($content, true);

        //Fail safe, sometimes decoded send empty packed from android and ios
        if(empty($decoded['token'])) {
            foreach($decoded as $new) {
                $decoded = $new;
            }
        }

        //Get Current Task/Token
        $currentTask = htmlspecialchars(isset($decoded['task']) ? trim($decoded['task']) : '');
        $currentToken = htmlspecialchars(isset($decoded['token']) ? trim($decoded['token']) : '');

        //Set userLevels
        $userLevel1 = validToken($currentToken, 1, $conn, $db_stat);
        $userLevel2 = validToken($currentToken, 2, $conn, $db_stat);
        $userLevel3 = validToken($currentToken, 3, $conn, $db_stat);
        $userLevel4 = validToken($currentToken, 4, $conn, $db_stat);

        //Check if token have access.
        if($userLevel4 != FALSE) {

            //Task for Image Upload
            if($currentTask == $upload_image) {
                if($userLevel3 != FALSE) {
                    if(!empty($decoded['image_data']) && !empty($decoded['image_name']) && !empty($decoded['con_id'])) {
                        $ImageData = $decoded['image_data'];
                        $ImageName = htmlspecialchars($decoded['image_name']);
                        $ImageCon = htmlspecialchars($decoded['con_id']);
                        $DefaultId = getIMGUID($conn, $db_stat);
                        $dato_stempel = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
                        $ImagePath = "images/$DefaultId.png";
                        
                        $InsertSQL = "INSERT INTO ".$db_stat['db_prefix']."images (idimg, path, name, date) value ('".$DefaultId."', '".$ImagePath."', '".$ImageName."', '".$dato_stempel."');";
                        $UpdateSQL = "UPDATE ".$db_stat['db_prefix']."contact SET image = '".$ImagePath."' WHERE shortid = '".$ImageCon."'";
                        
                        if(mysqli_query($conn, $InsertSQL)){
                            if(mysqli_query($conn, $UpdateSQL)) { 
                                giveResult("true");
                                file_put_contents($ImagePath, base64_decode($ImageData)); 
                            } else { giveResult("Error Update DB."); }
                        } else { giveResult("Error insert to db."); }
                    }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$upload_image);
                }
            }

            //Task for upgarde of profileinfo
            else if($currentTask == $update_profile) {
                if($userLevel3 != FALSE) {
                    //ADD UPDATE PROFILE FUNCTION
                    $conid = htmlspecialchars($decoded['con_id']);
                    $oldData = getContactData($conid, $conn, $db_stat);

                    $newfirstName = $oldData['firstname'];
                    $newlastname = $oldData['lastname'];
                    $newemail = $oldData['email'];
                    $newphone = $oldData['phone'];
                    $newadr = $oldData['adress'];
                    $newcompany = $oldData['company'];

                    if(array_key_exists('firstname', $decoded)) { 
                        if(!empty($decoded["firstname"])) {
                            $newfirstName = $decoded["firstname"];
                        }
                    }

                    if(array_key_exists('lastname', $decoded)) { 
                        if(!empty($decoded["lastname"])) {
                            $newlastname = $decoded["lastname"];
                        }
                    }

                    if(array_key_exists("phone", $decoded)) { 
                        if(!empty($decoded["phone"])) {
                            $newphone = $decoded["phone"];
                        }
                    }

                    if(array_key_exists("email", $decoded)) { 
                        if(!empty($decoded["email"])) {
                            $newemail = $decoded["email"];
                        }
                    }

                    if(array_key_exists("adr", $decoded)) { 
                        if(!empty($decoded["adr"])) {
                            $newadr = $decoded["adr"];
                        }
                    }

                    if(array_key_exists("company", $decoded)) { 
                        if(!empty($decoded["company"])) {
                            $newcompany = $decoded["company"];
                        }
                    }

                    $updateSQL = "UPDATE ".$db_stat['db_prefix']."contact SET firstname='".$newfirstName."', lastname='".$newlastname."', phone='".$newphone."', adress='".$newadr."', company='".$newcompany."' WHERE shortid = '".$conid."'";

                    if(mysqli_query($conn, $updateSQL)){ 
                            giveResult("true");
                    } else { 
                        giveResult("Error updating db.");
                    }

                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$update_profile);
                }
            }

            //Task for getting contact list
            else if($currentTask == $get_contactlist) {
                if($userLevel3 != FALSE) {
                    //Get contact list
                    $userData = getUserData($decoded['token'], $conn, $db_stat);

                    $contactlist = array();
                    $contactListLog = array();

                    $count = 0;
                    $sqlSelectCon = "SELECT shortid FROM ".$db_stat['db_prefix']."contact WHERE shortid != '".$userData['contactid']."' ORDER BY email ASC";
                    $usersql = $conn->prepare($sqlSelectCon);
                    $usersql->execute();
                    $usersql->bind_result($shortid);
                    while($usersql->fetch()) {
                        $contactlist[$count] = $shortid;
                        $count = $count + 1;
                    }

                    foreach ($contactlist as $conid) {
                        $userstat = geConStat($conid, $conn, $db_stat);
                        $sqlSelectCon = "SELECT * FROM ".$db_stat['db_prefix']."contact WHERE shortid = '".$conid."'";
                        $usersql = $conn->prepare($sqlSelectCon);
                        $usersql->execute();
                        $usersql->bind_result($shortid, $email, $firstname, $lastname, $phone, $adr, $company, $image);
                        while($usersql->fetch()) {
                            $temp = array(
                                "action" => "true",
                                "shortid" => $shortid,
                                "email" => $email,
                                "firstname" => $firstname,
                                "lastname" => $lastname,
                                "phone" => $phone, 
                                "adr" => $adr, 
                                "company" => $company,
                                "del" => $userstat['del'],
                                "tracker_count" => $userstat['tracker_count'],
                                "image" => "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$image
                            );

                            array_push($contactListLog, $temp);
                        }
                    }

                    if(empty($contactListLog)) {
                        giveResultArray("No Contacts! Create a tracker to get started.");
                    } else { echo (json_encode($contactListLog)); }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$get_contactlist);
                }
            }

            //Task for getting conntact Details.
            else if($currentTask == $get_contactdetail) {
                if($userLevel3 != FALSE) {
                    $userstat = geConStat($decoded['con_id'], $conn, $db_stat);

                    $sql = "SELECT * FROM `".$db_stat['db_prefix']."contact` WHERE shortid = '".$decoded['con_id']."'";
                    $usersql = $conn->prepare($sql);
                    $usersql->execute();
                    $usersql->bind_result($shortid, $email, $firstname, $lastname, $phone, $adress, $company, $image);
                    while($usersql->fetch()) {
                        $arrayUID = array(
                            "action" => "true",
                            "conid" => $shortid,
                            "email" => $email,
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "phone" => $phone, 
                            "adress" => $adress, 
                            "company" => $company,
                            "lastip" => $userstat['last_ip'],
                            "trackercount" => $userstat['tracker_count'],
                            "trackerevent" => $userstat['tracker_event'],
                            "image" => "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$image
                        );

                        echo json_encode($arrayUID); 
                    }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$get_contactdetail);
                }
            }

            //Task for setting contact detail
            else if($currentTask == $set_contactdetail) {
                if($userLevel3 != FALSE) {
                    $conid = htmlspecialchars($decoded['con_id']);
                    $oldData = getContactData($conid, $conn, $db_stat);

                    $newfirstName = $oldData['firstname'];
                    $newlastname = $oldData['lastname'];
                    $newemail = $oldData['email'];
                    $newphone = $oldData['phone'];
                    $newadr = $oldData['adress'];
                    $newcompany = $oldData['company'];

                    if(array_key_exists('firstname', $decoded)) { 
                        if(!empty($decoded["firstname"])) {
                            $newfirstName = $decoded["firstname"];
                        }
                    }

                    if(array_key_exists('lastname', $decoded)) { 
                        if(!empty($decoded["lastname"])) {
                            $newlastname = $decoded["lastname"];
                        }
                    }

                    if(array_key_exists("phone", $decoded)) { 
                        if(!empty($decoded["phone"])) {
                            $newphone = $decoded["phone"];
                        }
                    }

                    if(array_key_exists("email", $decoded)) { 
                        if(!empty($decoded["email"])) {
                            $newemail = $decoded["email"];
                        }
                    }

                    if(array_key_exists("adr", $decoded)) { 
                        if(!empty($decoded["adr"])) {
                            $newadr = $decoded["adr"];
                        }
                    }

                    if(array_key_exists("company", $decoded)) { 
                        if(!empty($decoded["company"])) {
                            $newcompany = $decoded["company"];
                        }
                    }

                    $updateSQL = "UPDATE ".$db_stat['db_prefix']."contact SET firstname='".$newfirstName."', lastname='".$newlastname."', phone='".$newphone."', adress='".$newadr."', company='".$newcompany."' WHERE shortid = '".$conid."'";

                    if(mysqli_query($conn, $updateSQL)){ 
                            giveResult("true");
                    } else { 
                        giveResult("Error updating db.");
                    }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$set_contactdetail);
                }
            }


            //Task for deletin contact unrestricted
            else if($currentTask == $del_contact_no_restric) {
                if($userLevel3 != FALSE) {
                    if(!isUser($decoded['con_id'], $conn, $db_stat)) {
                        //Delete Post with log
                        $sqlDELcon = "DELETE FROM ".$db_stat['db_prefix']."contact WHERE shortid = '".$decoded['con_id']."'"; 
                        $runDelMain = mysqli_query($conn, $sqlDELcon);
                        giveResult("true");

                    } else { giveResult("Contact is a user, cant delete."); }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$del_contact_no_restric);
                }
            }

            //Task for getting mail list
            else if($currentTask == $get_trackerlist) {
                if($userLevel3 != FALSE) {
                    $userData = getUserData($currentToken, $conn, $db_stat);

                    if($userLevel1 != FALSE) {
                        $sql = "SELECT main.id, main.from, main.subject, main.send, main.type, con.email, con.firstname, con.lastname, con.phone, con.adress, con.company, con.image FROM `".$db_stat['db_prefix']."main` AS main INNER JOIN `".$db_stat['db_prefix']."contact` AS con ON main.to = con.shortid ORDER BY main.send DESC;";
                    } else { 
                        $sql = "SELECT main.id, main.from, main.subject, main.send, main.type, con.email, con.firstname, con.lastname, con.phone, con.adress, con.company, con.image FROM `".$db_stat['db_prefix']."main` AS main INNER JOIN `".$db_stat['db_prefix']."contact` AS con ON main.to = con.shortid WHERE main.from = '".$userData['contactid']."' ORDER BY main.send DESC;";
                    }
                    
                    
                    $stmt = $conn->prepare($sql);
                    
                    //executing the query 
                    $stmt->execute();
                    
                    //binding results to the query 
                    $stmt->bind_result($id, $from, $subject, $dato, $type, $to_mail, $to_firstname, $to_lastname, $to_phone, $to_adress, $to_company, $to_image);
                    
                    $emaillog = array();
                    $resultarray = array(); 
                    
                    //traversing through all the result 
                    while($stmt->fetch()){
                        $temp = array();
                        $temp['id'] = $id;
                        $temp['from'] = $from; 
                        $temp['subject'] = $subject; 
                        $temp['dato'] = $dato; 
                        $temp['type'] = $type;
                        $temp['to_mail'] = $to_mail;
                        $temp['to_firstname'] = $to_firstname; 
                        $temp['to_lastname'] = $to_lastname;
                        $temp['to_phone'] = $to_phone;
                        $temp['to_adress'] = $to_adress;
                        $temp['to_company'] = $to_company;
                        $temp['to_image'] = $to_image;

                        array_push($emaillog, $temp);
                    }

                    foreach($emaillog as $tempz) {
                        $temp = array();
                        $temp['id'] = $tempz['id'];
                        $temp['from'] = $tempz['from'];
                        $temp['subject'] = $tempz['subject'];
                        $temp['dato'] = $tempz['dato'];
                        $temp['type'] = $tempz['type'];
                        $temp['to_mail'] = $tempz['to_mail'];
                        $temp['to_firstname'] = $tempz['to_firstname'];
                        $temp['to_lastname'] =  $tempz['to_lastname'];
                        $temp['to_phone'] = $tempz['to_phone'];
                        $temp['to_adress'] = $tempz['to_adress'];
                        $temp['to_company'] = $tempz['to_company'];
                        $temp['to_image'] = "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$tempz['to_image'];

                        $sql_from = "SELECT * FROM `".$db_stat['db_prefix']."contact` WHERE shortid = '".$tempz['from']."'";
                        $fromsql = $conn->prepare($sql_from);
                        $fromsql->execute();
                        $fromsql->bind_result($shortid, $from_email, $from_firstname, $from_lastname, $from_phone, $from_adress, $from_company, $from_image);
                        while($fromsql->fetch()) {
                            $temp['from_mail'] = $from_email;
                            $temp['from_firstname'] = $from_firstname;
                            $temp['from_lastname'] = $from_lastname;
                            $temp['from_phone'] = $from_phone;
                            $temp['from_adress'] = $from_adress;
                            $temp['from_company'] = $from_company;
                            $temp['from_image'] = "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$from_image;
                        }

                        $sql_log = "SELECT COUNT(idlog) as id FROM `".$db_stat['db_prefix']."openlog` WHERE idlog = '".$tempz['id']."'";
                        $result = mysqli_query($conn, $sql_log);
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_array($result)){ $temp['open_count'] = $row['id']; }
                        } else { $temp['open_count'] = 0; }

                        array_push($resultarray, $temp);
                    }
                    
                    //displaying the result in json format 
                    echo json_encode($resultarray);

                } else {
                    //Not Able to upload token dont have access.
                    giveResultArray("No Access, change token. Task: ".$get_trackerlist);
                }
            }

            //Task for getting log
            else if($currentTask == $get_log) {
                if($userLevel3 != FALSE) {
                    // #TODO: ADD LEVEL RESTRICTION HERE FOR TOKEN USERS
                    $currentID = isset($decoded['id']) ? trim($decoded['id']) : '';
                    $eventLog = array();
                    $ipLog = array();

                    //Run script to give log for entry
                    $sql = "SELECT * FROM `".$db_stat['db_prefix']."openlog` WHERE idlog = '".$currentID."' ORDER BY date DESC";
                    $logsql = $conn->prepare($sql);
                    $logsql->execute();
                    $logsql->bind_result($idlog, $ip, $date, $lang, $url, $useragent, $uid);
                    while($logsql->fetch()) {
                        $temp = array();
                        $temp['uid'] = $uid;
                        $temp['idlog'] = $idlog;
                        $temp['ip'] = $ip;
                        $temp['date'] = $date;
                        $temp['useragent'] = $useragent;

                        $ipjson = getIPdata($ip);
                        $temp['ip_country'] = $ipjson['country'];
                        $temp['ip_region_name'] =$ipjson['regionName'];
                        $temp['ip_city'] = $ipjson['city'];
                        $temp['ip_timezone'] = $ipjson['timezone'];
                        $temp['ip_isp'] = $ipjson['isp'];
                        $temp['ip_org'] = $ipjson['org'];
                        $temp['ip_as'] = $ipjson['as'];

                        array_push($eventLog, $temp);
                    }
                    
                    echo json_encode($eventLog);

                } else {
                    //Not Able to upload token dont have access.
                    giveResultArray("No Access, change token. Task: ".$get_log);
                }
            }

            //Task for validation of tracker
            else if($currentTask == $get_validation_tracker) {
                if($userLevel3 != FALSE) {
                    // #WOPS: Change of DET, now have fixed variabel on or off.
                    $tracker = isset($decoded['id']) ? trim($decoded['id']) : '';
                    $detail_on = isset($decoded['det']) ? trim($decoded['det']) : 'off'; 

                    $sql = "SELECT EXISTS(SELECT * FROM ".$db_stat['db_prefix']."main WHERE `id` = '".$tracker."')";
                    $result = $conn -> query($sql);
                    $sanity = mysqli_fetch_array($result);
                    if($sanity[0] ==  1) {
                        if($detail_on == "on") {
                            $sql = "SELECT main.id, con.email, main.subject FROM `".$db_stat['db_prefix']."main` AS main INNER JOIN `".$db_stat['db_prefix']."contact` AS con ON main.to = con.shortid WHERE main.id = '".$tracker."';";
                            $logsql = $conn->prepare($sql);
                            $logsql->execute();
                            $logsql->bind_result($id, $email, $subject);
                            while($logsql->fetch()) {
                                $arrayUID = array(
                                    "tracker_id" => $id,
                                    "tracker_to" => $email,
                                    "tracker_subject" => $subject
                                );
                        
                                echo json_encode($arrayUID); 
                            }
                        } else {
                            $arrayUID = array(
                                "tracker_valid" => "true"
                            );
                    
                            echo json_encode($arrayUID); 
                        }
                    } else {
                        $arrayUID = array(
                            "tracker_valid" => "false"
                        );
                
                        echo json_encode($arrayUID); 
                    }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$get_validation_tracker);
                }
            }

            //Task for upgarde of profileinfo
            else if($currentTask == $get_user) {
                if($userLevel3 != FALSE) {

                    $userstat = getUserStat($currentToken, $conn, $db_stat);

                    $sql = "SELECT * FROM `".$db_stat['db_prefix']."user` AS userdata INNER JOIN `".$db_stat['db_prefix']."contact` AS con ON userdata.contactid = con.shortid WHERE token = '".$currentToken."'";
                    $usersql = $conn->prepare($sql);
                    $usersql->execute();
                    $usersql->bind_result($userID, $username, $token, $contactid, $access, $shortid, $email, $firstname, $lastname, $phone, $adress, $company, $image);
                    while($usersql->fetch()) {
                        $arrayUID = array(
                            "userid" => $userID,
                            "username" => $username,
                            "access" => $access_list[$access],
                            "email" => $email,
                            "firstname" => $firstname,
                            "lastname" => $lastname,
                            "phone" => $phone, 
                            "adress" => $adress, 
                            "company" => $company,
                            "conid" => $shortid, 
                            "lastip" => $userstat['current_ip'],
                            "trackercount" => $userstat['tracker_count'],
                            "trackerevent" => $userstat['tracker_event'],
                            "image" => "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$image
                        );

                        echo json_encode($arrayUID); 
                    }


                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$get_user);
                }
            }

            //Task for upgarde of profileinfo
            else if($currentTask == $del_tracker) {
                if($userLevel3 != FALSE) {

                    //Get current ID
                    $currentID = isset($decoded['id']) ? trim($decoded['id']) : '';
                        
                    //Check if able to delete based on own ID and access
                    $step_1 = user_delete_check($currentID, $currentToken, $conn, $db_stat);
                    if($step_1 == "TRUE") { 
                        //Delete Post with log
                        $sqlDELmain = "DELETE FROM ".$db_stat['db_prefix']."main WHERE id = '".$currentID."'"; 
                        $sqlDELlog = "DELETE FROM ".$db_stat['db_prefix']."openlog WHERE idlog = '".$currentID."'";

                        $runDelMain = mysqli_query($conn, $sqlDELmain);
                        $runDellog = mysqli_query($conn, $sqlDELlog);

                        giveResult("true");
                    } else { giveResult($step_1); }
                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$del_tracker);
                }
            }

            //Create tracker
            else if($currentTask == $create_tracker) {
                //Set userlevel 1 to 4 for access for current task.
                if($userLevel3 != FALSE) {
                    $to_addr = htmlspecialchars(isset($decoded['addr']) ? trim($decoded['addr']) : 'N/A');
                    $to_subject = htmlspecialchars(isset($decoded['subject']) ? trim($decoded['subject']) : 'N/A');
                    $file = htmlspecialchars(isset($decoded['file']) ? getFileType(addEmp(strtolower(trim($decoded['file']))), $file_list) : 'Unknown');
                    $date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

                    //Run contact check
                    $to_addr = checkCon($to_addr,$db_stat, $conn, "N/A", "N/A", $LOGERROR);

                    //Get uniqe id
                    $UID = getUID($conn, $db_stat);

                    //Insert into table
                    $sql = "INSERT INTO `".$db_stat['db_prefix']."main` (`id`, `to`, `from`, `subject`, `send`, `type`) VALUES ('".$UID."', '".$to_addr."', '".$userLevel3."', '".$to_subject."', '".$date."', '".$file."')";
                    if ($conn -> query($sql) === FALSE) {
                        if($LOGERROR == TRUE) {
                            file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
                            file_put_contents('log.txt', $sql.PHP_EOL , FILE_APPEND);
                        }

                        $conn->close();
                        exit();
                    }

                    $arrayUID = array(
                        "tracker" => $UID
                    );

                    echo json_encode($arrayUID); 

                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$create_tracker);
                }
            }

            //Task for API Validation
            else if($currentTask == $validAPI) {
                //Set userlevel 1 to 4 for access for current task.
                giveResult("true");
            }

            //Task for deleting one event
            else if($currentTask == $del_event) {
                //Set userlevel 1 to 4 for access for current task.
                if($userLevel3 != FALSE) {
                    //SQL
                    $currentUID = htmlspecialchars(isset($decoded['uid']) ? trim($decoded['uid']) : '');
                    $sqlDELcon = "DELETE FROM ".$db_stat['db_prefix']."openlog WHERE uid_log = '".$currentUID."'";
                    
                    if(existStringDB($currentUID, "openlog", "uid_log", $conn, $db_stat) == TRUE) {
                        //IF STATEMENT TO FIND OUT IF USER OWNS THE TRACKER
                        if(canDeleteEvent($currentUID, $currentToken, $conn, $db_stat)) {
                            //Delete Event
                            $runDelMain = mysqli_query($conn, $sqlDELcon);
                            giveResult("true");

                        } else if ($userLevel1 != FALSE) { 
                            //Delete Event
                            $runDelMain = mysqli_query($conn, $sqlDELcon);
                            giveResult("true");
                        } else { giveResult("Event not owned by user cant delete."); }
                    } else { giveResult("Cant find event id: ".$currentUID); }

                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$del_event);
                }
            }

            /* TASK TEMPLATE 
            //Task for YOU TASK
            else if($currentTask == $TASK-TO-DO) {
                //Set userlevel 1 to 4 for access for current task.
                if($userLevel3 != FALSE) {

                    //CURRENT TASK CODE GOES HERE.

                } else {
                    //Not Able to upload token dont have access.
                    giveResult("No Access, change token. Task: ".$TASK-TO-DO);
                }
            }*/

            //No valid task found
            else { 
                giveResult("Please enter a valid task."); 
            }
        
        } else if($currentTask == $validAPI) {
            giveResult("token");
        } else {
            //POST did not contain token
            if($currentTask == $get_trackerlist || $currentTask == $get_log) {
                giveResultArray("Token dont have access. Token: ".$currentToken); 
            } else {
                giveResult("Token dont have access. Token: ".$currentToken); 
            }
        }
    } else {
        //POST dod not contain json. Print bad result. 
        giveResult("Content type must be: application/json"."\nContent was:".htmlspecialchars($contentType)); 
    }
} else {
    //Did not make a post, show pixel image 
    renderImage(); 
}


$conn->close();
exit();

?>