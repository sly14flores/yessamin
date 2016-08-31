<?php

/* Database Configuration */

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PWD	 = "sly";
$DB_FILE = "boutique";
$DB_PORT = 3306;

function db_connect() {
	global $db_con, $DB_HOST, $DB_USER, $DB_PWD, $DB_FILE, $DB_PORT;
	$db_con = new mysqli($DB_HOST, $DB_USER, $DB_PWD, $DB_FILE, $DB_PORT);
}

function db_close() {
	global $db_con;
	$db_con->close();
}

?>