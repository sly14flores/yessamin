<?php

session_start();
$uid = $_SESSION['user_id'];
$tbranch = $_SESSION['branch'];

require 'config.php';
require 'globalf.php';
require 'grants.php';

$isAdmin = 0;
if ($USER_GRANTS == 100) $isAdmin = 1;

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "last_offset_id":

$str_response = 0;
$sql = "select offset_id from offsets order by offset_id desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	$rec = $rs->fetch_array();
	$str_response = $rec['offset_id'];
}
db_close();

$str_response += 1;

echo $str_response;

break;

case "add":
$pcid = (isset($_POST['pcid'])) ? $_POST['pcid'] : "";
$pcoid = (isset($_POST['pcoid'])) ? $_POST['pcoid'] : 0;
$pod = (isset($_POST['pod'])) ? addslashes($_POST['pod']) : "";
if ($pod != "") $pod = date("Y-m-d",strtotime($pod));
$pmn = (isset($_POST['pmn'])) ? $_POST['pmn'] : "";
$poffi = (isset($_POST['poffi'])) ? $_POST['poffi'] : "";
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;

$sql = "alter table offsets AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into offsets (offset_cid, offset_company, offset_date, offset_uid, offset_mn) ";
$sql .= "values ('$pcid', $pcoid, '$pod', $uid, '$pmn')";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "alter table offset_items AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$loffid = 0;
$sql = "select offset_id from offsets order by offset_id desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$loffid = $rec['offset_id'];
db_close();

$sql = "INSERT INTO `offset_items`(`offset_item_oid`, `offset_item_date`, `offset_item_no`, `offset_item_amount`) VALUES ";
$epoffi = explode("|",$poffi);
$ino = 0;
for ($i=0; $i<$pnr; ++$i) {
$ino = $i + 1;
$oi = explode(",",$epoffi[$i]);
$oidate = date("Y-m-d",strtotime($oi[0]));
$sql .= "($loffid, '$oidate', $ino, " . $oi[1] . "),";
}

$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Offset successfully added.";

echo $str_response;
break;

case "edit":
$oid = (isset($_GET['oid'])) ? $_GET['oid'] : 0;
$mo = (isset($_GET['mo'])) ? $_GET['mo'] : "0";

$json = '{ "editoffset": [{';
$isql = ", (ifnull((select sum(offset_item_amount) from offset_items where offset_item_oid = offset_id),0) - ifnull((select sum(sin_off) from stock_in where sin_offid = offset_cid),0)) balance";
$sql = "select offset_cid, (select supplier_desc from suppliers where supplier_id = offset_company) company, date_format(offset_date,'%m/%d/%Y') od, offset_mn$isql from offsets where offset_id = $oid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '"jod": "' . $rec['od'] . '",';
$json .= '"jcid":"' . $rec['offset_cid'] . '",';
$json .= '"jco":"' . $rec['company'] . '",';
$json .= '"jmn":"' . $rec['offset_mn'] . '",';
$json .= '"jbal":' . $rec['balance'] . ',';
$json .= '"jadmin":' . $isAdmin . '';
}
db_close();

$json .= '}], "offsetitems": [';

$sql = "select offset_item_id, offset_item_oid, offset_item_date, offset_item_no, offset_item_amount from offset_items where offset_item_oid = $oid";
if ($mo != '0') {
$sql .= " and offset_item_date like '$mo-%'";
}
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	for ($i=0; $i<$rc; ++$i) {
		$rec = $rs->fetch_array();

		$json .= '{';
		$json .= '"joffiid":' . $rec['offset_item_id'] . ',';		
		$json .= '"joffd":"' . date("m/d/Y",strtotime($rec['offset_item_date'])) . '",';
		$json .= '"joffno":' . $rec['offset_item_no'] . ',';
		$json .= '"joffamt":' . $rec['offset_item_amount'] . '';
		$json .= '},';		
	}
}
db_close();
$json = substr($json,0,strlen($json)-1);
$json .= ']}';
echo $json;
break;

case "update":
$pod = (isset($_POST['pod'])) ? addslashes($_POST['pod']) : "";
if ($pod != "") $pod = date("Y-m-d",strtotime($pod));
$poid = (isset($_POST['poid'])) ? addslashes($_POST['poid']) : 0;
$pmn = (isset($_POST['pmn'])) ? $_POST['pmn'] : "";
$poffi = (isset($_POST['poffi'])) ? $_POST['poffi'] : "";
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;
$ploffiid = (isset($_POST['ploffiid'])) ? $_POST['ploffiid'] : 0;
$pdoiids = (isset($_POST['pdoiids'])) ? $_POST['pdoiids'] : "";
if ($pdoiids != "") $pdoiids = substr($pdoiids,0,strlen($pdoiids)-1);

/*
if ($pod != date("Y-m-d",strtotime("+8 Hour"))) {

echo "Return entries cannot be edited.";
break;

}
*/

$sql = "update offsets set offset_mn = '$pmn' where offset_id = $poid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "alter table offset_items AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$items_count = 0;
$sql = "select count(*) total_items from offset_items where offset_item_oid = $poid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$items_count = $rec['total_items'];
}
db_close();

$chk_any_added = false;
$sql = "INSERT INTO `offset_items`(`offset_item_oid`, `offset_item_date`, `offset_item_no`, `offset_item_amount`) VALUES ";
$epoffi = explode("|",$poffi);
$ino = $items_count;
for ($i=0; $i<$pnr; ++$i) {
$oi = explode(",",$epoffi[$i]);
$oidate = date("Y-m-d",strtotime($oi[0]));
if ($oi[2] > $ploffiid) {
$ino += 1;
$sql .= "($poid, '$oidate', $ino, " . $oi[1] . "),";
$chk_any_added = true;
} else {

$usql = "UPDATE `offset_items` SET `offset_item_amount` = " . $oi[1] . " WHERE `offset_item_id` = " . $oi[2];
if ($pod == date("Y-m-d",strtotime("+8 Hour"))) {
db_connect();
$db_con->query($START_T);
$db_con->query($usql);
$db_con->query($END_T);
db_close();
}

}
}

if ($chk_any_added) {
$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
}

if ($pdoiids != "") {
$dsql = "delete from offset_items where offset_item_id in ($pdoiids)";
db_connect();
$db_con->query($START_T);
$db_con->query($dsql);
$db_con->query($END_T);
db_close();
}

$str_response = "Offset info updated.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from offsets where offset_id != 0";
$pql = "select offset_id, offset_cid, (select supplier_desc from suppliers where supplier_id = offset_company) company, offset_date, ifnull((select sum(offset_item_amount) from offset_items where offset_item_oid = offset_id),0) amount, ifnull((select sum(sin_off) from stock_in where sin_offid = offset_cid),0) stock_off, offset_mn from offsets where offset_id != 0";

// filter
$foid = (isset($_GET['foid'])) ? $_GET['foid'] : "";
$fco = (isset($_GET['fco'])) ? $_GET['fco'] : 0;
$fmn = (isset($_GET['fmn'])) ? $_GET['fmn'] : "";
$fod = (isset($_GET['fod'])) ? $_GET['fod'] : "";
if ($fod != "") $fod = date("Y-m-d",strtotime($fod));

$c1 = " and offset_cid like '$foid'";
$c2 = " and offset_company = $fco";
$c3 = " and offset_mn = '$fmn'";
$c4 = " and offset_date = '$fod'";

if ($foid == "") $c1 = "";
if ($fco == 0) $c2 = "";
if ($fmn == "") $c3 = "";
if ($fod == "") $c4 = "";

$sql .= $c1 . $c2 . $c3 . $c4;
$pql .= $c1 . $c2 . $c3 . $c4;
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
$str_response .= '<tr><td>Company</td><td>Member Name</td><td>ID</td><td>Date</td><td>Amount</td><td>Balance</td><td>Ref.no.</td><td style="text-align: center">Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '">';
$str_response .= '<td>' . $rec['company'] . '</td>';
$str_response .= '<td>' . $rec['offset_mn'] . '</td>';
$str_response .= '<td>' . $rec['offset_cid'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['offset_date'])) . '</td>';
$str_response .= '<td>' . round($rec['amount'],2) . '</td>';
$off = $rec['amount'] - $rec['stock_off'];
$str_response .= '<td>' . round($off,2) . '</td>';
$str_response .= '<td><a href="javascript: viewRefNo(\'' . $rec['offset_cid'] . '\');" class="tooltip-min"><img src="images/view-16.png" /><span>View Reference No.</span></a></td>';
$str_response .= '<td style="text-align: center">';
$str_response .= '<a href="javascript: returnOffsets(2,' . $rec['offset_id'] . ',\'0\');" class="tooltip-min" style="padding-right: 3px;"><img src="images/edit.png" /><span>Edit Offset</span></a>';
if ($isAdmin == 1) $str_response .= '<a href="javascript: delOffset(' . $rec['offset_id'] . ');" class="tooltip-min"><img src="images/delete.png" /><span>Delete Offset</span></a>';
$str_response .= '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(13,'rOffsetsF()',$pageNum,$max_p);
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
$oid = $_POST['oid'];

$sql = "delete from offsets where offset_id in ($oid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from offset_items where offset_item_oid in ($oid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Offset deleted.";

echo $str_response;
break;

case "view_ref_no":
$pcid = (isset($_POST['pcid'])) ? $_POST['pcid'] : "";

$offIds = "";
$sql = "select sin_ref, sin_off from stock_in where sin_offid = '$pcid'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$_5c = 0;
	for ($n=0; $n<$rc; ++$n) {	
		$rec = $rs->fetch_array();
		++$_5c;
		if ($_5c == 1) $offIds .= "<tr>";
		$offIds .= "<td style=\"border: 1px solid; padding: 3px;\"><a href=\"#\" class=\"tip-min\">" . $rec['sin_ref'] . "<span>Amount: " . $rec['sin_off'] . "</span></a></td>";
		if ($_5c == 5) {
		$offIds .= "</tr>";
		$_5c = 0;
		}
	}
	$offIds = substr($offIds,0,strlen($offIds)-1);
}
db_close();

$str_response = $offIds;

echo $str_response;
break;

}

?>