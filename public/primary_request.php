<?php
/**
* primary_request.php
*
* This file is responsible for doing the first http request.
* Initially this will get the Device ID, SDK key, App secret key from the request.
* Check Device ID is present in the database table developer_device_list.
* If Device ID is present user codes is generated and echo it.
* Otherwise echo an error.
*/
 
//including basic codes
require_once("../includes/initialize.php"); 

//get the Device ID, App key, App secret key from the request
if(isset($_POST['dev_id'])){
	//$dev_id = 2323;
	$dev_id = $db->escape_value($_POST['dev_id']);
	$app_key = $db->escape_value($_POST['app_key']);
	$app_secret = $db->escape_value($_POST['app_secret']);
	
	//check Device ID is present in the database table developer_device_list
	$result = $db->find_by_condition('ofabee_developer_device_list','device_id',$dev_id);
	if($db->num_rows($result) == 1){
		/*device is present, hence a user code should be generated
		for that we need the user details from the tabel ofabee_user*/
		while($device = $db->fetch_array($result))
		$user_result = $db->find_by_condition('ofabee_user','UID',$device['UID']);
		
		//generate a user code and send it as reply
		while($user = $db->fetch_array($user_result))
		$user_code = $user['name']."|".$user['email']."|".$user['company_name']."|".$user['phone']."|".$user['date'];
		echo $user_code;
		
	}
	else{
		//device not present
		echo "error: no user";
	}
}
?>