<?php
// TRAWO CORE v. 1.0.7
// Build by Marcus Knoph

include 'func.php';
include 'def.php';

//DB Connect
$conn = new mysqli($db_stat['db_addr'], $db_stat['db_user'], $db_stat['db_password'], $db_stat['db_data']);
if ($conn->connect_error) {
    if($LOGERROR == TRUE) {
        $myfile = file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
    }

    echo("<br />YOU NEED TO RUN SETUP!<br />Please visit: <a href='setup.php'>SETUP PAGE</a><br />When finnish please delete <b>setup.php</b>");
    exit;
}

if(isset($_GET['token'])) {
    $userconid = validToken(htmlspecialchars($_GET["token"]), 2, $conn, $db_stat);
    if($userconid != FALSE){
        cors();

        if(isset($_GET['addr']) == false) {$to_addr = "N/A";} else {$to_addr = addEmp(strtolower($_GET["addr"]));}
        if(isset($_GET['subject']) == false) {$to_subject  = "N/A";} else {$to_subject  = addEmp(strtolower($_GET["subject"]));}
        if(isset($_GET['file']) == false) {$file  = "Unknown";} else {$file  = getFileType(addEmp(strtolower($_GET["file"])), $file_list);}
        $date = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

        //Run contact check
        $to_addr = checkCon($to_addr,$db_stat, $conn, "N/A", "N/A", $LOGERROR);

        //Get uniqe id
        $UID = getUID($conn, $db_stat);

        //Insert into table
        $sql = "INSERT INTO `".$db_stat['db_prefix']."main` (`id`, `to`, `from`, `subject`, `send`, `type`) VALUES ('".$UID."', '".$to_addr."', '".$userconid."', '".$to_subject."', '".$date."', '".$file."')";
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
        $conn->close();
        exit(); 
    }
    else{ renderImage(); }
} else {
    if(isset($_GET['image'])) {
        $main_id = htmlspecialchars($_GET["image"]);
        logOut("- Main id: ".$main_id."<br />");
        $sql = "SELECT EXISTS(SELECT * FROM `".$db_stat['db_prefix']."main` WHERE `id` = '".$main_id."')";
        logOut("- SQL: ".$sql."<br />");
        $result = $conn -> query($sql);
        $sanity = mysqli_fetch_array($result);
        if($sanity[0] ==  1) {
            //ADD OPEN STATUS TO TABLE
            updateLog($conn, $main_id, $db_stat, $LOGERROR);

            //Render image - Link <img src="" width="1" height="1" style="display:none!important;">
            renderImage();

        } else { 
            //echo ("404 - ERROR BASE ID DO NOT EXIST");
            renderImage(); }
    } else {
        //Render Image
        renderImage();
    } 
}

//exit gracefully
$conn->close();
exit();

?>

