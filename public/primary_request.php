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
	$dev_id = $db->real_escape_string($_POST['dev_id']);
	$app_key =  $db->real_escape_string($_POST['app_key']);
	$app_secret =  $db->real_escape_string($_POST['app_secret']);
	
	//check Device ID is present in the database table developer_device_list
	$result = $db->query('CALL find_device_by_id('.$dev_id.')');
	
	if($result->num_rows == 1){
		/*device is present, hence a user code should be generated
		for that we need the details from the tabel ofabee_unique_device*/
		//Prepare next result from multi_query
		$db->next_result();

		$user_result = $db->query('CALL get_device_details('.$dev_id.')');
		
		
		//generate a user code and send it as reply
		while($user = $user_result->fetch_array())
		$user_code = $user['DID']."_".$user['device_id']."_".$user['name']."_".$user['model']."_".$user['version']."_".$user['key']."_".$user['age_range']."_".$user['type']."_".$user['country_name'];
		echo $user_code;
		
	}
	else{
		//device not present
		echo "error: no device";
	}
}
?>