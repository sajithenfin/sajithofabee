<pre>
<?php require_once("../includes/initialize.php"); ?>

<?php

$result = $db->find_all("sample",3);
while($row = $db->fetch_array($result))
  {
 print_r($row);
  echo "<br />";
  }
?>
</pre>