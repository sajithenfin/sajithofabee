<?php

// Define the core paths
// Define them as absolute paths to make sure that require_once works as expected

// DIRECTORY_SEPARATOR is a PHP pre-defined constant
// (\ for Windows, / for Unix)
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

//SITE_ROOT is the path to root folder, here it is C:\wamp\www\ofabee\src 
defined('SITE_ROOT') ? null : 
	define('SITE_ROOT', 'C:'.DS.'wamp'.DS.'www'.DS.'ofabee'.DS.'src');
	
defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'includes');

// load config file first
require_once(LIB_PATH.DS.'config.php');

//load database class file
// New Connection
$db = new mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);

// Check for errors
if(mysqli_connect_errno()){
 echo mysqli_connect_error();
}


//loadig restrictions
require_once(LIB_PATH.DS.'ofabee_restrictions.php');

?>