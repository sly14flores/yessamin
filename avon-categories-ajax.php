<?php

session_start();
$uid = $_SESSION['user_id'];
$tbranch = $_SESSION['branch'];

require 'config.php';
require 'globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "add":
$pmc = (isset($_POST['pmc'])) ? addslashes($_POST['pmc']) : 0;
$pcn = (isset($_POST['pcn'])) ? addslashes($_POST['pcn']) : "";

$sql = "alter table avon_categories AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into avon_categories (main_cat, avon_cat_name, avon_cat_uid) ";
$sql .= "values ($pmc, '$pcn', $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Category successfully added.";

echo $str_response;
break;

case "edit":
$cid = (isset($_GET['cid'])) ? $_GET['cid'] : 0;

$json = '{ "editavoncat": [{';
$sql = "select main_cat, avon_cat_name from avon_categories where avon_cat_id = $cid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '"jmc":' . $rec['main_cat'] . ',';
$json .= '"jav":"' . stripslashes($rec['avon_cat_name']) . '"';
}
db_close();

$json .= '}] }';
echo $json;
break;

case "update":
$pmc = (isset($_POST['pmc'])) ? addslashes($_POST['pmc']) : 0;
$pcn = (isset($_POST['pcn'])) ? addslashes($_POST['pcn']) : "";
$pcid = (isset($_POST['pcid'])) ? addslashes($_POST['pcid']) : 0;

$sql = "update avon_categories set main_cat = $pmc, avon_cat_name = '$pcn' where avon_cat_id = $pcid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Category info updated.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from avon_categories where avon_cat_id != 0";
$pql = "select avon_cat_id, main_cat, avon_cat_name, avon_cat_uid from avon_categories where avon_cat_id != 0";

// filter
$cn = (isset($_GET['cn'])) ? $_GET['cn'] : "";

$c1 = " and avon_cat_name like '$cn%'";

if ($cn == "") $c1 = "";

$sql .= $c1;
$pql .= $c1;
//

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$max_count = $rec[0];
db_close();

$perPage = 20;
$max_p = ceil($max_count / $perPage);

if ($dir == 3) $pageNum = $max_p;

$offset = ($pageNum - 1) * $perPage;
$pageParam = " LIMIT $offset, $perPage";

$lpage = "|$max_p";	

$sql = $pql . $pageParam;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td>ID</td><td>Main Category</td><td>Name</td><td style="text-align: center">Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$main_cat = 'Undefine';
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '">';
$str_response .= '<td>' . $rec['avon_cat_id'] . '</td>';
switch ($rec['main_cat']) {

case 1:
$main_cat = "CFT";
break;

case 2:
$main_cat = "NCFT";
break;

case 3:
$main_cat = "Homestyle";
break;

case 4:
$main_cat = "Health Care";
break;

}
$str_response .= '<td>' . $main_cat . '</td>';
$str_response .= '<td>' . $rec['avon_cat_name'] . '</td>';
$str_response .= '<td style="text-align: center">';
$str_response .= '<a href="javascript: avonCat(2,' . $rec['avon_cat_id'] . ');" class="tooltip-min" style="padding-right: 3px;"><img src="images/edit.png" /><span>Edit Category</span></a>';
$str_response .= '<a href="javascript: delCat(' . $rec['avon_cat_id'] . ');" class="tooltip-min"><img src="images/delete.png" /><span>Delete Category</span></a>';
$str_response .= '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(12,'rAvonCatF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;
}
db_close();

echo $str_response;
break;

case "delete":
$cid = $_POST['cid'];

$sql = "delete from avon_categories where avon_cat_id in ($cid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Category deleted.";

echo $str_response;
break;

}

?>