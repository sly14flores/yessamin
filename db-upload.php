<?php

$dir = "db/";
$dfile = "boutique.sql";

move_uploaded_file($_FILES["db-file"]["tmp_name"], $dir . $dfile);

?>

