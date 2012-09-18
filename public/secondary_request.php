<?php
/**
* secondary_request.php
*
* This file is responsible for doing the second http request.
* This gets age, gender, Name, Image, Version, Device type, SDK Key, model, country code and Bundle ID.
* Saves the datas into databse and generates an User code.
* Then returns the User code as acknowledgement.
*/
 
//including basic codes
require_once("../includes/initialize.php"); 
include "SimpleImage.php";

//create image object for saving the image form base64 string
$img_obj = new SimpleImage();
$FolderName= "deviceIcons";
$ImageName = $_POST['Image'];
$Base64Image = $_POST['Base64Image'];

//prepare the fields to save
	$name = $db->real_escape_string($_POST['Name']);
	$model = $db->real_escape_string($_POST['model']);
	$version = $db->real_escape_string($_POST['Version']);
	$key = $db->real_escape_string($_POST['SDK_Key']);
	$icon = $db->real_escape_string($_POST['Image']);
	
	//age
	$age = $db->real_escape_string($_POST['age']);
	//1 under 14
	if($age<14){ $AGID = 1;}
	//2 	15 - 24
	else if($age>=15 && $age<=24){ $AGID = 2;}
	//3 	25-44
	else if($age>=25 && $age<=45){ $AGID = 3;}
	//4 	45+
	else { $AGID = 4;}
	
	//Gender
	$gneder = $db->real_escape_string($_POST['gender']);
	//1 	Male
	if($gneder == 'male' || $gneder == 'Male'){ $GID = 1; }
	//2 	Female
	else { $GID = 2; }
	
	//getting country id
	$country = $db->real_escape_string($_POST['country']);
	$conntry_res = $db->query("CALL get_country_id_by_name('".$country."')");
	while($country = $conntry_res->fetch_array())
		$COID = $country['COID'];
		
	//getting device id
	$device_type = $db->real_escape_string($_POST['Device_type']);
	//Prepare next result from multi_query
	$db->next_result();
	$device_res = $db->query("CALL indiaget_device_support_id_by_type('".$device_type."')");
	while($device = $device_res->fetch_array())
		$DSID = $device['DSID'];
		
	//saving to db table ofabee_unique_device
	if($icon){
		$db->next_result();
		$inserted = $db->query("CALL create_device('".$DSID."','".$name."','".$AGID."','".$GID."','".$model."','".$version."','".$key."','".$COID."','".$icon."')");
	}
	else{
		$db->next_result();
		$inserted = $db->query("CALL create_device_without_icon('".$DSID."','".$name."','".$AGID."','".$GID."','".$model."','".$version."','".$key."','".$COID."')");
	}
	if($inserted)
	{
		
		//saving the image
		if(!file_exists($FolderName))
		{
			mkdir($FolderName); 
		}
		
		$icon_image = imagecreatefromstring(base64_decode($Base64Image));
		if ($icon_image != false) {
			imagejpeg($icon_image, $FolderName.'/'.$ImageName.'.jpg');
		}
		
		//generating the user code
		$user_code = $data['DID']."_".$data['device_id']."_".$data['name']."_".$data['model']."_".$data['version']."_".$data['key']."_".$data['age_range']."_".$data['type']."_".$user['country_name'];
		echo $user_code;
	}
	else{
		echo "error";	
	}

?>