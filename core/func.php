<?php
// TRAWO CORE v. 1.0.7
// Build by Marcus Knoph

function logOut($text) {
    $PRINT_LOG = FALSE;
    if($PRINT_LOG == TRUE) {
        echo($text);
    }
}

function generateRandomString($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getIPdata($ip) {
    $url = "http://ip-api.com/json/".$ip;
    $json = file_get_contents($url);
    $json_data = json_decode($json, true);

    return $json_data;
}

function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
   
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
   
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
   
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
   
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
   
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

function getFileType($id, $filelist) {
    logOut("- File type id:".$id."<br />");

    if((int)($id) == TRUE) {
        $id = str_replace("-", "", $id);
        if ((int)$id <= count($filelist)) { return $filelist[$id]; }
        else { return "N/A"; }
        
    } else { return "N/A"; } 
}

function cors() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');  
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

function updateLog($conn, $id, $db_stat, $LOGERROR) {
    $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $current = $_SERVER['REQUEST_URI'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
    $user_agent_data = getBrowser();
    $data_s = $user_agent_data['platform'].":".$user_agent_data['name'].":".htmlspecialchars($user_agent_data['userAgent']);
    
    $sql = "INSERT INTO `".$db_stat['db_prefix']."openlog` (`idlog`, `ip`, `date`, `lang`, `url`, `user_agent`) VALUES('".$id."', '".$ip."', '".$date."', '".$lang."', '".$current."', '".$data_s."')";
    if ($conn -> query($sql) === FALSE) {
        if($LOGERROR == TRUE) {
            file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
            file_put_contents('log.txt', $sql.PHP_EOL , FILE_APPEND);
        }
    }
}

function renderImage(){
	// Set image header
	header("Content-Type: image/png");
	$sign = imagecreatefrompng("an.png");

	// Transparent
	imagealphablending($sign, false);
	imagesavealpha($sign, true);
	
	// Display the image
	imagepng($sign);
    imagedestroy($sign);
}

function addEmp($string) {
    if (empty($string)) { return "N/A";} else {return htmlspecialchars($string);}
}

function checkCon($id, $db_stat, $conn, $firstname, $lastname, $LOGERROR) {
    $sql = "SELECT EXISTS(SELECT * FROM `".$db_stat['db_prefix']."contact` WHERE `email` = '".$id."')";
    $result = $conn -> query($sql);
    $sanity = mysqli_fetch_array($result);
    if($sanity[0] ==  1) {
        //Contact Exist
        $sql = "SELECT `shortid` FROM `".$db_stat['db_prefix']."contact` WHERE `email` = '".$id."'";
        $result = $conn -> query($sql);
        while($row = $result->fetch_assoc()) {
            $id = $row["shortid"];
        }
        return $id;
    } else {
        //Contact dosent exist Add it to list
        $sql = "INSERT INTO `".$db_stat['db_prefix']."contact` (`email`, `firstname`, `lastname`, `phone`, `adress`, `company`, `image`) VALUES ('".$id."', '".$firstname."', '".$lastname."', 'N/A', 'N/A', 'N/A', 'images/blank.png')";
        if ($conn -> query($sql) === FALSE) {
            if($LOGERROR == TRUE) {
                file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
                file_put_contents('log.txt', $sql.PHP_EOL , FILE_APPEND);
            }

            $conn->close();
            exit();
        }
		return $conn->insert_id;
    }
}

function existStringDB($value, $tabel, $row, $conn, $db_stat)
{
    $sql = "SELECT EXISTS(SELECT * FROM `".$db_stat['db_prefix'].$tabel."` WHERE `".$row."` = '".$value."')";
    logOut("- SQL: ".$sql."<br />");
    $result = $conn -> query($sql);
    $sanity = mysqli_fetch_array($result);
    if($sanity[0] ==  1) {
        //Exist
        logOut("- SQL Respons: Exist<br />");
        return TRUE;
    } else { return FALSE; }
}

function countTracker($toid, $conn, $db_stat) {
    $out = "";
    $sql = "SELECT COUNT(id) FROM `".$db_stat['db_prefix']."main` WHERE `to` = '".$toid."'";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) { 
        $out = $row['id'];
    }

    return $out;
}

function generateToken($conn, $db_stat)
{
    $uniqe = FALSE;
    $token = "";
    while($uniqe == FALSE) {
        $token = md5(uniqid(rand(), true));
        if(existStringDB($token, "user", "token", $conn, $db_stat) == FALSE) { $uniqe = TRUE; }
    }

    return $token;
}

function test_input($data) 
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function user_delete_check($post, $user, $conn, $db_stat) 
{
    //True = OK, False = Nope
    $userSQL = "SELECT contactid, access FROM ".$db_stat['db_prefix']."user WHERE token = '".$user."'";
    $postSQL = "SELECT `from` FROM ".$db_stat['db_prefix']."main WHERE id = '".$post."'";

    $userCon = 0;
    $userAcc = 4;
    $postCon = 5;


    $result = $conn->prepare($userSQL); 
    $result->execute();   
    $result->bind_result($contact, $access);
    while($result->fetch()) {
        $userCon = $contact;
        $userAcc = $access;
    }

    $result = $conn->prepare($postSQL); 
    $result->execute();   
    $result->bind_result($post);
    while($result->fetch()) {
        $postCon = $post;
    }

    //User able to write
    if($postCon == 5) {
        return "Tracker ID dosent Exist";
    } else if($userAcc < 3) {
        //User own the post
        if($userCon == $postCon) {
            return "TRUE";
        } else if ($userAcc == 1) {
            //User have all access
            return "TRUE";
        } else {
            //USer cant delete
            return "Tracking ID are not owned by user.";
        }
    } else {
        //User dont have writing permission.
        return "User dont have writing permission.";
    }

}

function getUserData($token, $conn, $db_stat) {
    $temp = array();
    $sql = "SELECT * FROM `".$db_stat['db_prefix']."user` WHERE token = '".$token."'";
    $result = $conn->prepare($sql);
    $result->execute();
    $result->bind_result($userid, $username, $token, $contactid, $access);
    while($result->fetch()) {
        $temp['userid'] = $userid;
        $temp['username'] = $username;
        $temp['token'] = $token;
        $temp['contactid'] = $contactid;
        $temp['access'] = $access;
    }

    return $temp;
}

function getTrackerData($trackerid, $conn, $db_stat) {
    $temp = array();
    $sql = "SELECT * FROM `".$db_stat['db_prefix']."main` WHERE id = '".$trackerid."'";
    $result = $conn->prepare($sql);
    $result->execute();
    $result->bind_result($id, $from, $to, $subject, $send, $type);
    while($result->fetch()) {
        $temp['id'] = $id;
        $temp['from'] = $from;
        $temp['to'] = $to;
        $temp['subject'] = $subject;
        $temp['send'] = $type;
    }

    return $temp;
}

function getUIDData($uid, $conn, $db_stat) {
    $temp = array();
    $sql = "SELECT * FROM `".$db_stat['db_prefix']."openlog` WHERE uid_log = '".$uid."'";
    $result = $conn->prepare($sql);
    $result->execute();
    $result->bind_result($idlog, $ip, $date, $lang, $url, $user_agent, $uid_log);
    while($result->fetch()) {
        $temp['idlog'] = $idlog;
        $temp['ip'] = $ip;
        $temp['date'] = $date;
        $temp['lang'] = $lang;
        $temp['user_agent'] = $user_agent;
        $temp['uid_log'] = $uid_log;
    }

    return $temp;
}

function getContactData($conid, $conn, $db_stat) {
    $temp = array();
    $sql = "SELECT * FROM `".$db_stat['db_prefix']."contact` WHERE shortid = '".$conid."'";
    $result = $conn->prepare($sql);
    $result->execute();
    $result->bind_result($shortid, $email, $firstname, $lastname, $phone, $adress, $company, $image);
    while($result->fetch()) {
        $temp['shortid'] = $shortid; 
        $temp['email'] = $email;
        $temp['firstname'] = $firstname; 
        $temp['lastname'] = $lastname; 
        $temp['phone'] = $phone;
        $temp['adress'] = $adress;
        $temp['company'] = $company; 
        $temp['image'] = $image;
    }

    return $temp;
}

function validToken($token, $handling, $conn, $db_stat) {
    logOut("- Valid token start<br />");
    if(existStringDB($token, "user", "token", $conn, $db_stat) == TRUE) { 
        logOut("- Token Exist<br />");
        $userdata = getUserData($token, $conn, $db_stat);
        if(intval($userdata['access']) <= $handling) {
            logOut("- Returing contactid: ".$userdata['contactid']."<br />");
            return $userdata['contactid'];
        } else {return FALSE;}

    } else {
        //TOKEN DOSENT EXIST
        logOut("- Token dosent exist<br />");
        return FALSE;
    }
}

function getUID($conn, $db_stat) {
    $uniqe = FALSE;
    $token = "";
    while($uniqe == FALSE) {
        $token = generateRandomString();
        if(existStringDB($token, "main", "id", $conn, $db_stat) == FALSE) { $uniqe = TRUE; }
    }

    return $token;
}

function getIMGUID($conn, $db_stat) {
    $uniqe = FALSE;
    $token = "";
    while($uniqe == FALSE) {
        $token = generateRandomString();
        if(existStringDB($token, "images", "idimg", $conn, $db_stat) == FALSE) { $uniqe = TRUE; }
    }

    return $token;
}

function getJSONfromPost($string) {
    $jsonArray = array();
    $pieces = explode("&", $string);
    foreach ($pieces as $key) {
        print_r($key);
        $keyplit = explode("<!>=", $key);
        $jsonArray[$keyplit[0]] = $keyplit[1];
    }

    return $jsonArray;
}

function giveResult($string) {
    echo(json_encode(array("action" => $string)));
}

function giveResultArray($string) {
    $org = array("action" => $string);
    $next = array();
    array_push($next, $org);

    echo(json_encode($next));
}

function getUserStat($token, $conn, $db_stat) {
    $mainData = array();
    $trackers = array();
    $totalCount = 0;

    $sqlContactID = "SELECT contactid FROM ".$db_stat['db_prefix']."user where token = '".$token."'";
    $result = $conn->prepare($sqlContactID); 
    $result->execute();   
    $result->bind_result($con);
    while($result->fetch()) {
        $mainData['conid'] = $con;
    }

    $sqlCountTracker = "SELECT COUNT(main.from) FROM ".$db_stat['db_prefix']."main as main WHERE main.from = '".$mainData['conid']."'";
    $result = $conn->prepare($sqlCountTracker); 
    $result->execute();   
    $result->bind_result($con);
    while($result->fetch()) {
        $mainData['tracker_count'] = $con;
    }

    $sqlSelectID = "SELECT id FROM ".$db_stat['db_prefix']."main AS main WHERE main.from = '".$mainData['conid']."'";
    $result = $conn->prepare($sqlSelectID); 
    $result->execute();   
    $result->bind_result($con);
    $counter = 0;
    while($result->fetch()) {
        $trackers[$counter] = $con;
        $counter = $counter + 1;
    }

    if(!empty($trackers)) {
        foreach ($trackers as $idlog) {
            $sqlCountEvent = "SELECT COUNT(idlog) FROM ".$db_stat['db_prefix']."openlog WHERE idlog =  '".$idlog."'";
            $result = $conn->prepare($sqlCountEvent); 
            $result->execute();   
            $result->bind_result($con);
            while($result->fetch()) {
                $totalCount = $totalCount + $con;
            }
        }
    }


    $mainData['current_ip'] = $_SERVER['REMOTE_ADDR'];
    $mainData['tracker_event'] = $totalCount;

    return $mainData;
}

function isUser($conid, $conn, $db_stat) {
    if(existStringDB($conid, "user", "contactid", $conn, $db_stat)) { return TRUE;}
    else{return FALSE;}
}

function geConStat($conid, $conn, $db_stat) {
    $mainData = array();
    $trackers = array();
    $totalCount = 0;

    $mainData['conid'] = $conid;
    $mainData['last_ip'] = "";

    $sqlCountTracker = "SELECT COUNT(main.to) FROM ".$db_stat['db_prefix']."main as main WHERE main.to = '".$mainData['conid']."'";
    $result = $conn->prepare($sqlCountTracker); 
    $result->execute();   
    $result->bind_result($con);
    while($result->fetch()) {
        $mainData['tracker_count'] = $con;
    }

    $sqlSelectID = "SELECT id FROM ".$db_stat['db_prefix']."main AS main WHERE main.to = '".$mainData['conid']."'";
    $result = $conn->prepare($sqlSelectID); 
    $result->execute();   
    $result->bind_result($con);
    $counter = 0;
    while($result->fetch()) {
        $trackers[$counter] = $con;
        $counter = $counter + 1;
    }

    if(!empty($trackers)) {
        foreach ($trackers as $idlog) {
            $sqlCountEvent = "SELECT COUNT(ip), IP FROM ".$db_stat['db_prefix']."openlog WHERE idlog = '".$idlog."'";
            $result = $conn->prepare($sqlCountEvent); 
            $result->execute();   
            $result->bind_result($con, $ip);
            while($result->fetch()) {
                $totalCount = $totalCount + $con;
                $mainData['last_ip'] = $ip;
            }
        }
    }

    if(!empty($trackers)) {
        $mainData['del'] = "false";
    } else { $mainData['del'] = "true"; }

    $mainData['tracker_event'] = $totalCount;
    return $mainData;
}

function canDeleteEvent($uid, $token, $conn, $db_stat) {
    $userData = getUserData($token, $conn, $db_stat);
    $uidData = getUIDData($uid, $conn, $db_stat);
    $trackerData = getTrackerData($uidData['idlog'], $conn, $db_stat);

    if($trackerData['from'] == $userData['contactid']) { return TRUE; }
    else { return FALSE; }
}

?>