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
	$result = $db->find_by_condition('ofabee_unique_device','device_id',$dev_id);
	if($db->num_rows($result) == 1){
		/*device is present, hence a user code should be generated
		for that we need the details from the tabel ofabee_unique_device*/
		//create an array for join query
		$data = array(
			'tables'=>array(
				'ofabee_unique_device'=>array('DID','device_id','name','model','version','key'),
				'ofabee_age'=>array('age_range'),
				'ofabee_gendar'=>array('type'),
				'ofabee_country'=>array('country_name')
			),
			'join'=>array(
				'ofabee_age'=>array('ofabee_unique_device.AGID','ofabee_age.AGID'),
				'ofabee_gendar'=>array('ofabee_unique_device.GID','ofabee_gendar.GID'),
				'ofabee_country'=>array('ofabee_unique_device.COID','ofabee_country.COID')
			)
		);
		$user_result = $db->join_table_where('ofabee_unique_device',$data,'device_id',$dev_id);
		
		//generate a user code and send it as reply
		while($user = $db->fetch_array($user_result))
		$user_code = $user['DID']."_".$user['device_id']."_".$user['name']."_".$user['model']."_".$user['version']."_".$user['key']."_".$user['age_range']."_".$user['type']."_".$user['country_name'];
		echo $user_code;
		
	}
	else{
		//device not present
		echo "error: no device";
	}
}
?>