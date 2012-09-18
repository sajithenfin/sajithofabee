<pre>
<?php require_once("../includes/initialize.php"); ?>

<?php

/*$result = $db->find_all("sample",3);
while($row = $db->fetch_array($result))
  {
 print_r($row);
  echo "<br />";
  }*/
  
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
	$db->join_table('ofabee_unique_device',$data);
?>
</pre>