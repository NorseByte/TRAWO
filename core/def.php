<?php 
// TRAWO CORE v. 1.0.7
// Build by Marcus Knoph

function dbInfo($user, $password, $data, $addr, $prefix) {
  $info = "<?php\n \$db_stat = array( 'db_user' => \"".$user."\", 'db_password' => \"".$password."\", 'db_data' => \"".$data."\", 'db_addr' => \"".$addr."\", 'db_prefix' => \"".$prefix."\" ); \n?>";
  return $info;
}

if (file_exists('db_config.php') == FALSE) {
  //CREATE TEMP db_config
  $myfile = fopen("db_config.php", "w") or die("Unable to open file!");
  fwrite($myfile, dbInfo("dbuser", "a-great-db-password", "database-name", "exampel.com", "tracker_"));
  fclose($myfile);
} 

include 'db_config.php';

$LOGERROR = FALSE;
date_default_timezone_set('Europe/Berlin');

$file_list = array(
  '1' => "email",
  '2' => "pdf",
  '3' => "docx",
  '4' => "other"
);

$access_list = array(
  1 => "Full Access",
  2 => "Write",
  3 => "Read", 
  4 => "Denied"
);

$sql_array = array(
"sql_create_main" => "CREATE TABLE IF NOT EXISTS `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."main` (
    `id` VARCHAR(45) NOT NULL DEFAULT '',
    `from` VARCHAR(255) NOT NULL DEFAULT '',
    `to` VARCHAR(255) NOT NULL DEFAULT '',
    `subject` VARCHAR(255) NOT NULL DEFAULT '',
    `send` VARCHAR(255) NOT NULL DEFAULT '',
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY(`id`)
  )
  ENGINE = InnoDB;",

"sql_create_log" => "CREATE TABLE IF NOT EXISTS `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."openlog` (
    `idlog` VARCHAR(45) NOT NULL DEFAULT '',
    `ip` VARCHAR(255) NOT NULL DEFAULT '',
    `date` VARCHAR(255) NOT NULL DEFAULT '',
    `lang` VARCHAR(255) NOT NULL DEFAULT '',
    `url` VARCHAR(255) NOT NULL DEFAULT '',
    `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
    `uid_log` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(`uid_log`)
  )
  ENGINE = InnoDB;",

"sql_create_contact" => "CREATE TABLE IF NOT EXISTS `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."contact` (
  `shortid` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `firstname` VARCHAR(255) NOT NULL DEFAULT '',
  `lastname` VARCHAR(255) NOT NULL DEFAULT '',
  `phone` VARCHAR(255) NOT NULL DEFAULT '',
  `adress` VARCHAR(255) NOT NULL DEFAULT '',
  `company` VARCHAR(255) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY(`shortid`)
)
ENGINE = InnoDB;",

"sql_create_user" => "CREATE TABLE IF NOT EXISTS `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."user` (
  `userID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL DEFAULT '',
  `token` VARCHAR(255) NOT NULL DEFAULT '',
  `contactid` VARCHAR(255) NOT NULL DEFAULT '',
  `access` INTEGER UNSIGNED NOT NULL DEFAULT 4,
  PRIMARY KEY(`userID`)
)
ENGINE = InnoDB;",

"sql_create_options" => "CREATE TABLE `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."default` (
  `what` VARCHAR(45) NOT NULL DEFAULT '',
  `value` VARCHAR(45) NOT NULL DEFAULT '',
  `options` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY(`what`)
)
ENGINE = InnoDB;",

"sql_create_image" => "CREATE TABLE `".$db_stat['db_data']."`.`".$db_stat['db_prefix']."images` (
  `idimg` VARCHAR(45) NOT NULL DEFAULT '',
  `path` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `date` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY(`idimg`)
)
ENGINE = InnoDB;"

);

?>