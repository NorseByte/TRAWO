<?php 

/* Run Setup then delete or move it. I will create users */
// TRAWO CORE v. 1.0.7
// Build by Marcus Knoph

include 'def.php';
include 'func.php';


$output = "This will create a user account and setup the database. Create as many users you need and save the tokens for them. When you are finnish delete setup.php&nbsp;";
$firstname = "";
$lastname = "";
$username = "";
$email = "";

function updateDB($conn, $sql_array, $LOGERROR) {
	if ($conn -> query($sql_array["sql_create_main"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
	if ($conn -> query($sql_array["sql_create_log"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
	if ($conn -> query($sql_array["sql_create_contact"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
	if ($conn -> query($sql_array["sql_create_user"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
	if ($conn -> query($sql_array["sql_create_options"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
	if ($conn -> query($sql_array["sql_create_image"]) == FALSE) { if($LOGERROR == TRUE) { file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND); } }
}

//Run setup
if($_SERVER["REQUEST_METHOD"] == "POST") {

	if(isset($_POST["dbupdate"])) {
		# Add new database values to file and load them.
		$db_stat = array(
			'db_user' => $_POST["db_user"],
			'db_password' => $_POST["db_password"],
			'db_data' => $_POST["db_data"],
			'db_addr' => $_POST["db_addr"],
			'db_prefix' => $_POST["db_prefix"]
		);

		$myfile = fopen("db_config.php", "w") or die("Unable to open file!");
  		fwrite($myfile, dbInfo($db_stat['db_user'], $db_stat['db_password'], $db_stat['db_data'], $db_stat['db_addr'], $db_stat['db_prefix']));
  		fclose($myfile);
	}

	//DB Connect
	$conn = new mysqli($db_stat['db_addr'], $db_stat['db_user'], $db_stat['db_password'], $db_stat['db_data']);
	if ($conn->connect_error) {
		if($LOGERROR == TRUE) {
			$myfile = file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
		}

		$output = "<b>ERROR DATABASE</b><br />Could not connect to DB. Update values in <b>Setup Database</b>, and click <b>Create database</b>";
	}

	else {
		if(isset($_POST["dbupdate"])) {
			updateDB($conn, $sql_array, $LOGERROR);
			$output = "DB Update Complete";
		}

		else {
			$username = test_input($_POST["username"]);
			$firstname = test_input($_POST["firstname"]);
			$lastname = test_input($_POST["lastname"]);
			$email = test_input($_POST["email"]);

			//TODO: ADD CHECK IF DB IS SETUP
			//IS DB UP AND RUNNING?
			
			if(!empty($_POST["username"])) {
				if(!empty($_POST["firstname"])) {
					if(!empty($_POST["lastname"])) {
						if(!empty($_POST["email"])) {
						

							if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
								if(strlen($username) >= 5) {
									if(existStringDB($username, "user", "username", $conn, $db_stat) == FALSE) {
										$contactID =  checkCon($email, $db_stat, $conn, $firstname, $lastname, $LOGERROR);
										if(existStringDB($contactID, "user", "contactid", $conn, $db_stat) == FALSE) {
											//Add user and generatetoken
											$user_token = generateToken($conn, $db_stat);

											$sql = "INSERT INTO `".$db_stat['db_prefix']."user` (`username`, `token`, `contactid`, `access`) VALUES ('".$username."', '".$user_token."', '".$contactID."', '1')";
											if ($conn -> query($sql) === FALSE) {
												if($LOGERROR == TRUE) {
													file_put_contents('log.txt', $conn->error.PHP_EOL , FILE_APPEND);
													file_put_contents('log.txt', $sql.PHP_EOL , FILE_APPEND);
												}

												$conn->close();
												exit();
											}

											$output = "User token: ".$user_token;

										} else { $output = "Email allready in use"; }
									} else { $output = "Username exist"; }
								} else { $output = "Username need to be more than 5 character"; }
							} else { $output = "Invalid E-Mail"; }
						} else { $output = "Email can not be empty"; }
					} else { $output = "Lastname can not be empty"; }
				} else { $output = "Firstname can not be empty"; }
			} else { $output = "Username can not be empty"; }
		}

		$conn -> close();
	}
} 

?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/TRAWOBG.png">

    <title>Setup TRAWO</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>

<body>

	<div class="container" style="margin-top: 20px;">
		<div class="header clearfix">
		</div>

	

		<div class="row marketing">
			<div class="col-lg-6">

				<div class="card" style="">		  		  
					<div class="card-body" style="opacity: 1;">	
						<h4 class="card-title" style="opacity: 1;">Create User</h4>	
						<form style="" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST"><div class="form-group">
						<label>Firstname</label><input type="text"  name="firstname" value="<?php echo($firstname); ?>" class="form-control">
						<label>Lastname</label><input type="text"  name="lastname" value="<?php echo($lastname); ?>" class="form-control">
						<label>Username</label><input type="text"  name="username" value="<?php echo($username); ?>" class="form-control">
						<label>Email</label><input type="text"  name="email" value="<?php echo($email); ?>" class="form-control">
						<br />
						<button type="submit" class="btn btn-primary" style="">Create User</button>
						</div></form>
					</div>
				</div>
				<br />

				<div class="card" style="">	
					<div class="card-body" style="opacity: 1;">			
						<h4 class="card-title" style="opacity: 1;">Setup Database</h4>	
						<form style="" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST"><div class="form-group">
						<label>DB User</label><input type="text"  name="db_user" value="<?php echo($db_stat['db_user']); ?>" class="form-control">
						<label>DB Password</label><input type="text"  name="db_password" value="<?php echo($db_stat['db_password']); ?>" class="form-control">
						<label>DB Database</label><input type="text"  name="db_data" value="<?php echo($db_stat['db_data']); ?>" class="form-control">
						<label>DB Adress</label><input type="text"  name="db_addr" value="<?php echo($db_stat['db_addr']); ?>" class="form-control">
						<label>DB Prefix</label><input type="text"  name="db_prefix" value="<?php echo($db_stat['db_prefix']); ?>" class="form-control">
						<br />
						<button type="submit" class="btn btn-primary" name="dbupdate" style="">Create database</button>
						</div></form>
					</div>
				</div>

				<br />
			</div>


			<div class="col-lg-6">
			
				<img src="images/TRAWOBG.png" height="500" width="500" style="">

				<div class="card" style="">		  		  
					<div class="card-body" style="opacity: 1;">			
						<h4 class="card-title" style="opacity: 1;">Output</h4>			
						<p class="card-text"><hr style=""><?php echo($output); ?></p>					  
					</div>		
				</div>
				
			</div>

		</div>


		<footer class="footer">
			
      	</footer>
	

	</div> <!-- /container -->


</body>  
</html>

