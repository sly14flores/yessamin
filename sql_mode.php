<?php

require 'config.php';

// $sql = "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));";
$sql = "SET GLOBAL sql_mode='';";

db_connect();
$rs = $db_con->query($sql);
db_close();

?>