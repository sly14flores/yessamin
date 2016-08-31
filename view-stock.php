<?php

$sid = $_GET['sid'];
$refno = $_GET['refno'];

session_start();
require 'config.php';
require 'globalf.php';
require 'grants.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $refno; ?></title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}

body {
	background-color: #fbf8fa;
	font: 12px sans-serif;
}

#content {
	width: 95%;
	margin: 20px auto 0 auto;
	border: 1px solid #c9c7c7;
	border-radius: 5px;	
	background-color: #fff;
}

#in-content {
	padding: 10px;
	color: #265fb9;
}

#tab-view-stock {
	border-collapse: collapse;
	width: 100%;
}

#tab-view-stock thead td {
	border: 1px solid #fa17ae;
	padding: 5px;
}

#tab-view-stock tbody td {
	border: 1px solid #fa17ae;
	padding: 5px;
}

/* tooltip */
a.tooltip:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none; padding:2px 3px; margin-top:-25px; margin-left:-5px; width:120px;}
a.tooltip:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}

.archive {
	margin-top: 25px;
}
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/dialogbox.js"></script>
<script type="text/javascript" src="js/globalf.js"></script>
<script type="text/javascript">

$(function() {
	
	$('#archive').button();
	$('#archive').click(function() {
		confirmArchive();
	});
	
});
	
function confirmArchive() {

var m = 'Are you sure you want to archive this stock?';
var f = function() { archive(); };
confirmation(350,180,m,f);

}

function archive() {

var id = $('#hsid').val();

$.ajax({
	url: 'stock-in.php?p=archive_stock',
	type: 'post',
	data: {psid: id},
	success: function(data, status) {
		notify(300,200,data);
	}
});

}
	
</script>
</head>
<body>
<div id="content">
<div id="in-content">
<table id="tab-view-stock">
<thead>
<?php

$str_response = "";
$sql = "select sin_date_invoice, sin_member_no, member_name, sin_ref, (select supplier_desc from suppliers where supplier_id = sin_supid) supplier, sin_supid, sin_off, sin_offid, (select concat(firstname, ' ', lastname) user_fullname from users where user_id = sin_userid) cashier, if((sin_payment_mode = 0),date_format(sin_due_date,'%M %e, %Y'),'NA') payment_mode from stock_in where sin_id = $sid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = '<tr><td colspan="2">Date:</td><td colspan="3">' . date("F j, Y",strtotime($rec['sin_date_invoice'])) . '</td><td colspan="2">Ref.No:</td><td>' . $rec['sin_ref'] . '</td><td>Supplier:</td><td colspan="3">' . $rec['supplier'] . '</td></tr>';
$str_response .= '<tr><td colspan="2">Member No:</td><td colspan="5">' . $rec['sin_member_no'] . '</td><td>Member Name:</td><td colspan="4">' . $rec['member_name']  . '</td></tr>';
$str_response .= '<tr><td colspan="5">Offset: ' . number_format($rec['sin_off'],2) . ' &nbsp;&nbsp;&nbsp;&nbsp;Offset ID: ' . $rec['sin_offid'] . '</td><td colspan="4">Cashier: ' . $rec['cashier']  . '</td><td colspan="3">Due Date:&nbsp;' . $rec['payment_mode'] . '</td></tr>';
}
db_close();

?>
</thead>
<tbody>
<?php

$sql = "select @rep := sii_replacement, stock_in_id, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, stock_in_quantity, stock_in_amt, ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) returns_dealers, ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) returns_company, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) stocks, ifnull((select avon_cat_name from avon_categories where avon_cat_id = stock_in_acat),'None') cat_name, if(sii_replacement != 0,(select concat('Replaced: ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) from stock_in_item where stock_in_id = @rep),'') remarks from stock_in_item where stock_in_sid = $sid order by stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$left_stock = 0;
$str_response .= '<tr style="background-color: #fadab8;"><td>Category</td><td>Product Code</td><td>Product Name</td><td>Size</td><td>Price</td><td>Amount</td><td>Quantity</td><td>Returns from Dealers</td><td>Returns to Company</td><td>Stock-on-hand</td><td>Tra.No</td><td>Remarks</td></tr>';
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['cat_name'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pcode'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pname'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_psize'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_price'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_amt'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_quantity'] . '</td>';
$str_response .= '<td>' . $rec['returns_dealers'] . '</td>';
$str_response .= '<td>' . $rec['returns_company'] . '</td>';
$str_response .= '<td>' . $rec['stocks'] . '</td>';
if ($rec['stocks'] > 0) ++$left_stock;
$tras = "";
//$isql = "select tra_item_trano from transaction_items where tra_item_sino = " . $rec['stock_in_id'];
//$isql = "select tra_item_trano, (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)) sold from transaction_items where tra_item_sino = " . $rec['stock_in_id'] . " and ((tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)) > 0)";
$isql = "select tra_item_trano, tra_item_qty, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0) return_from_dealer from transaction_items where tra_item_sino = " . $rec['stock_in_id'];

$irs = $db_con->query($isql);
$irc = $irs->num_rows;
if ($irc>0) {
for ($n=0; $n<$irc; ++$n) {
$irec = $irs->fetch_array();
$tras .= '<a href="#" class="tooltip">' . $irec['tra_item_trano'] . '<span>TRA: ' . $irec['tra_item_trano'] . '<br />Quantity: ' . $irec['tra_item_qty'] . '<br />Dealer\'s returns: ' . $irec['return_from_dealer'] . '</span></a>, ';
}
$tras = substr($tras,0,strlen($tras)-2);
}
$str_response .= '<td>' . $tras . '</td>';
$str_response .= '<td>' . $rec['remarks'] . '</td>';
$str_response .= '</tr>';
}
}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
</tfoot>
</table>
<?php

if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) {

$str_response = '<div class="archive">';
$str_response .= '<input type="button" id="archive" value="ARCHIVE" />';
$str_response .= '<input type="hidden" id="hsid" value="' . $sid . '" />';
$str_response .= '</div>';

echo $str_response;

}

?>
</div>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
</body>
</html>