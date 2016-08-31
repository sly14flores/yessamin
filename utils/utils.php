<?php

require '../config.php';
require '../globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "stock_in_item":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : "";
$pisd = (isset($_POST['pisd'])) ? $_POST['pisd'] : "";
if ($pisd != "") $pisd = date("Y-m-d",strtotime($pisd));
$pied = (isset($_POST['pied'])) ? $_POST['pied'] : "";
if ($pied != "") $pied = date("Y-m-d",strtotime($pied));
$psup = (isset($_POST['psup'])) ? $_POST['psup'] : 0;

$sql = "select sin_date_invoice, stock_in_id, stock_in_sid, stock_in_ref, ifnull((select avon_cat_name from avon_categories where avon_cat_id = stock_in_acat),'None') cat_name, supplier_desc, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_quantity, ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) tra_qty, ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) dealer_qty, ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) company_qty, ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) swapp, stock_in_inventory from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where stock_in_id != 0 ";
if ($pisd != "") $sql .= " and sin_date_invoice >= '$pisd'";
if ($pied != "") $sql .= " and sin_date_invoice <= '$pied'";
if ($pitem != "") $sql .= " and concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) like '%$pitem%'";
if ($psup != 0) {
$sql .= " and supplier_id = $psup";
}
$sql .= " order by stock_in_id";

$siids = "";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rowc = "even";
$totalq = 0; // total quantity
$total_tra = 0;
$total_dr = 0;
$total_rc = 0;
$total_swap = 0;
$total_si = 0; // static inventory
$total_di = 0; // dynamic inventory
$str_response = "<table id=\"tab-content\">";
$str_response .= "<tr><td>Date</td><td>No.</td><td>STOCK _IN_ID</td><td>STOCK_IN_SID</td><td>REF#</td><td>Category</td><td>Supplier</td><td>PRODUCT CODE</td><td>PRODUCT NAME</td><td>PRODUCT SIZE</td><td>STOCKS QTY</td><td>TRA QTY (-)</td><td>Returns from Dealers (+)</td><td>Returns to Company (-)</td><td>Swaps (+)</td><td>Static Inventory</td><td>Inventory</td></tr>";
for ($i=0; $i<$rc; ++$i) {
$inv = 0;
$rec = $rs->fetch_array();
$c = $i + 1;
$rowc = ((($c) % 2) == 1 ) ? "odd" : "even";
$str_response .= "<tr class=\"$rowc\">";
$str_response .= "<td>" . $c . "</td>";
$str_response .= "<td>" . date("M j, Y",strtotime($rec['sin_date_invoice'])) . "</td>";
$str_response .= "<td>" . $rec['stock_in_id'] . "</td>";
$siids .= (string)$rec['stock_in_id'] . ",";
$str_response .= "<td>" . $rec['stock_in_sid'] . "</td>";
$str_response .= "<td>" . $rec['stock_in_ref'] . "</td>";
$str_response .= "<td>" . $rec['cat_name'] . "</td>";
$str_response .= "<td>" . $rec['supplier_desc'] . "</td>";
$str_response .= "<td><a href=\"javascript: copyPcode('" . $rec['stock_in_pcode'] . "');\">" . $rec['stock_in_pcode'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPname('" . $rec['stock_in_pname'] . "');\">" . $rec['stock_in_pname'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPsize('" . $rec['stock_in_psize'] . "');\">" . $rec['stock_in_psize'] . "</a></td>";
$str_response .= "<td>" . $rec['stock_in_quantity'] . "</td>";
$str_response .= "<td>" . $rec['tra_qty'] . "</td>";
$total_tra += $rec['tra_qty'];
$str_response .= "<td>" . $rec['dealer_qty'] . "</td>";
$total_dr += $rec['dealer_qty'];
$str_response .= "<td>" . $rec['company_qty'] . "</td>";
$total_rc += $rec['company_qty'];
$str_response .= "<td>" . $rec['swapp'] . "</td>";
$total_swap += $rec['swapp'];
$inv = $rec['stock_in_quantity'] - $rec['tra_qty'] + $rec['dealer_qty'] - $rec['company_qty'] + $rec['swapp'];
$str_response .= "<td>" . $rec['stock_in_inventory'] . "</td>";
$total_si += $rec['stock_in_inventory'];
$str_response .= "<td>" . $inv . "</td>";
$total_di += $inv;

$str_response .= "</tr>";
$totalq = $totalq + $rec['stock_in_quantity'];
}
$str_response .= "<tr><td colspan=\"10\">Total:</td><td>$totalq</td><td>$total_tra</td><td>$total_dr</td><td>$total_rc</td><td>$total_swap</td><td>$total_si</td><td>$total_di</td></tr>";
$str_response .= "<table>";
} else {
	$str_response = "No item(s) found.";
}
db_close();


$cpcode = (isset($_POST['cpcode'])) ? $_POST['cpcode'] : '';
$cpname = (isset($_POST['cpname'])) ? $_POST['cpname'] : '';
$cpsize = (isset($_POST['cpsize'])) ? $_POST['cpsize'] : '';
$siids = substr($siids,0,strlen($siids)-1);
$usql = "update stock_in_item set stock_in_pcode = '$cpcode', stock_in_pname = '$cpname', stock_in_psize = '$cpsize' where stock_in_id in ($siids);";

if ( ($cpcode != "") && ($cpname != "") && ($cpsize != "") && ($rc) ) echo "<p>$usql</p><p><a href=\"javascript: execQuery('" . addslashes($usql) . "');\">Execute Query</a></p>";
else echo "<br>To change product code/name/size enter code/name/size respectively.<br><br>";

echo $str_response;
break;

case "transaction_item":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : "";
$psup = (isset($_POST['psup'])) ? $_POST['psup'] : 0;

$sql = "select tra_item_no, tra_item_sino, tra_item_trano, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_qty, supplier_desc from transaction_items left join suppliers on transaction_items.tra_item_sup = suppliers.supplier_id where tra_item_no != 0";
if ($pitem != "") $sql .= " and concat(tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) like '%$pitem%'";
if ($psup != 0) $sql .= " and supplier_id = $psup";
$sql .= " order by tra_item_sino";

$siids = "";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rowc = "even";
$total = 0;
$str_response = "<table id=\"tab-content\">";
$str_response .= "<tr><td>No.</td><td>TRA ITEM NO</td><td>STOCK _IN_ID</td><td>TRA NO</td><td>SUPPLIER</td><td>PRODUCT CODE</td><td>PRODUCT NAME</td><td>PRODUCT SIZE</td><td>QTY</td></tr>";
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$c = $i + 1;
$rowc = ((($c) % 2) == 1 ) ? "odd" : "even";
$str_response .= "<tr class=\"$rowc\">";
$str_response .= "<td>" . $c . "</td>";
$str_response .= "<td>" . $rec['tra_item_no'] . "</td>";
$str_response .= "<td>" . $rec['tra_item_sino'] . "</td>";
$siids .= (string)$rec['tra_item_sino'] . ",";
$str_response .= "<td>" . $rec['tra_item_trano'] . "</td>";
$str_response .= "<td>" . $rec['supplier_desc'] . "</td>";
$str_response .= "<td><a href=\"javascript: copyPcode('" . $rec['tra_item_pcode'] . "');\">" . $rec['tra_item_pcode'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPname('" . $rec['tra_item_pname'] . "');\">" . $rec['tra_item_pname'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPsize('" . $rec['tra_item_psize'] . "');\">" . $rec['tra_item_psize'] . "</a></td>";
$str_response .= "<td>" . $rec['tra_item_qty'] . "</td>";
$str_response .= "</tr>";
$total = $total + $rec['tra_item_qty'];
}
$str_response .= "<tr><td colspan=\"8\">Total:</td><td>$total</td></tr>";
$str_response .= "<table>";
} else {
	$str_response = "No item(s) found.";
}
db_close();

$cpcode = (isset($_POST['cpcode'])) ? $_POST['cpcode'] : '';
$cpname = (isset($_POST['cpname'])) ? $_POST['cpname'] : '';
$cpsize = (isset($_POST['cpsize'])) ? $_POST['cpsize'] : '';
$siids = substr($siids,0,strlen($siids)-1);
$usql = "update transaction_items set tra_item_pcode = '$cpcode', tra_item_pname = '$cpname', tra_item_psize = '$cpsize' where tra_item_sino in ($siids);";

echo "<p>$usql</p><p><a href=\"javascript: execQuery('" . addslashes($usql) . "');\">Execute Query</a></p>";
echo $str_response;
break;

case "dealer_return":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : "";
$psup = (isset($_POST['psup'])) ? $_POST['psup'] : 0;

$sql = "select dret_sino, dret_tino, dret_trano, dret_pcode, dret_pname, dret_psize, dret_qty, supplier_desc from dealers_returns left join suppliers on dealers_returns.dret_sup = suppliers.supplier_id where dret_no != 0";
if ($pitem != "") $sql .= " and concat(dret_pcode, ' ', dret_pname, ' ', dret_psize) like '%$pitem%'";
if ($psup != 0) $sql .= " and supplier_id = $psup";
$sql .= " order by dret_sino";

$siids = "";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rowc = "even";
$total = 0;
$str_response = "<table id=\"tab-content\">";
$str_response .= "<tr><td>No.</td><td>STOCK _IN_ID</td><td>TRA ITEM NO</td><td>TRA NO</td><td>SUPPLIER</td><td>PRODUCT CODE</td><td>PRODUCT NAME</td><td>PRODUCT SIZE</td><td>QTY</td></tr>";
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$c = $i + 1;
$rowc = ((($c) % 2) == 1 ) ? "odd" : "even";
$str_response .= "<tr class=\"$rowc\">";
$str_response .= "<td>" . $c . "</td>";
$str_response .= "<td>" . $rec['dret_sino'] . "</td>";
$siids .= (string)$rec['dret_sino'] . ",";
$str_response .= "<td>" . $rec['dret_tino'] . "</td>";
$str_response .= "<td>" . $rec['dret_trano'] . "</td>";
$str_response .= "<td>" . $rec['supplier_desc'] . "</td>";
$str_response .= "<td><a href=\"javascript: copyPcode('" . $rec['dret_pcode'] . "');\">" . $rec['dret_pcode'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPname('" . $rec['dret_pname'] . "');\">" . $rec['dret_pname'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPsize('" . $rec['dret_psize'] . "');\">" . $rec['dret_psize'] . "</a></td>";
$str_response .= "<td>" . $rec['dret_qty'] . "</td>";
$str_response .= "</tr>";
$total = $total + $rec['dret_qty'];
}
$str_response .= "<tr><td colspan=\"8\">Total:</td><td>$total</td></tr>";
$str_response .= "<table>";
} else {
	$str_response = "No item(s) found.";
}
db_close();

$cpcode = (isset($_POST['cpcode'])) ? $_POST['cpcode'] : '';
$cpname = (isset($_POST['cpname'])) ? $_POST['cpname'] : '';
$cpsize = (isset($_POST['cpsize'])) ? $_POST['cpsize'] : '';
$siids = substr($siids,0,strlen($siids)-1);
$usql = "update dealers_returns set dret_pcode = '$cpcode', dret_pname = '$cpname', dret_psize = '$cpsize' where dret_sino in ($siids);";

echo "<p>$usql</p><p><a href=\"javascript: execQuery('" . addslashes($usql) . "');\">Execute Query</a></p>";
echo $str_response;
break;

case "return_company":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : "";
$psup = (isset($_POST['psup'])) ? $_POST['psup'] : 0;

$sql = "select retc_sino, retc_pcode, retc_pname, retc_psize, retc_qty, supplier_desc from returns_companies left join suppliers on returns_companies.retc_sup = suppliers.supplier_id where retc_no != 0";
if ($pitem != "") $sql .= " and concat(retc_pcode, ' ', retc_pname, ' ', retc_psize) like '%$pitem%'";
if ($psup != 0) $sql .= " and supplier_id = $psup";
$sql .= " order by retc_sino";

$siids = "";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rowc = "even";
$total = 0;
$str_response = "<table id=\"tab-content\">";
$str_response .= "<tr><td>No.</td><td>STOCK _IN_ID</td><td>SUPPLIER</td><td>PRODUCT CODE</td><td>PRODUCT NAME</td><td>PRODUCT SIZE</td><td>QTY</td></tr>";
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$c = $i + 1;
$rowc = ((($c) % 2) == 1 ) ? "odd" : "even";
$str_response .= "<tr class=\"$rowc\">";
$str_response .= "<td>" . $c . "</td>";
$str_response .= "<td>" . $rec['retc_sino'] . "</td>";
$siids .= (string)$rec['retc_sino'] . ",";
$str_response .= "<td>" . $rec['supplier_desc'] . "</td>";
$str_response .= "<td><a href=\"javascript: copyPcode('" . $rec['retc_pcode'] . "');\">" . $rec['retc_pcode'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPname('" . $rec['retc_pname'] . "');\">" . $rec['retc_pname'] . "</a></td>";
$str_response .= "<td><a href=\"javascript: copyPsize('" . $rec['retc_psize'] . "');\">" . $rec['retc_psize'] . "</a></td>";
$str_response .= "<td>" . $rec['retc_qty'] . "</td>";
$str_response .= "</tr>";
$total = $total + $rec['retc_qty'];
}
$str_response .= "<tr><td colspan=\"6\">Total:</td><td>$total</td></tr>";
$str_response .= "<table>";
} else {
	$str_response = "No item(s) found.";
}
db_close();

$cpcode = (isset($_POST['cpcode'])) ? $_POST['cpcode'] : '';
$cpname = (isset($_POST['cpname'])) ? $_POST['cpname'] : '';
$cpsize = (isset($_POST['cpsize'])) ? $_POST['cpsize'] : '';
$siids = substr($siids,0,strlen($siids)-1);
$usql = "update returns_companies set retc_pcode = '$cpcode', retc_pname = '$cpname', retc_psize = '$cpsize' where retc_sino in ($siids);";

echo "<p>$usql</p><p><a href=\"javascript: execQuery('" . addslashes($usql) . "');\">Execute Query</a></p>";
echo $str_response;
break;

case "paid_tra":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : 0;

//$sql = "update transactions set tra_fullpaid = '0000-00-00' where tra_no in ($pitem)";
$sql = "update transactions set tra_fullpaid = tra_due where tra_no in ($pitem)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Transaction updated.";

echo $str_response;
break;

case "update_stock_inventory":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : 0;

$sql = "select stock_in_id, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) inventory from stock_in_item where stock_in_id = $pitem";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$nsql = "update stock_in_item set stock_in_inventory = " . $rec['inventory'] . " where stock_in_id = $pitem";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}   
db_close();

$str_response = "Product Static Inventory Updated.";

echo $str_response;

break;

case "execute_query":
$sql = (($_POST['psql'])) ? $_POST['psql'] : "";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Query Executed.";

echo $str_response;
break;

case "check_tra":
$pitem = (isset($_POST['pitem'])) ? $_POST['pitem'] : 0;

$bal = 0;
$sql = "select round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) payment from transactions where tra_no = $pitem";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();	
	$bal = $rec['amount'] - $rec['payment'];
	$str_response .= "Amount: " . $rec['amount'] . "<br>Payment: " . $rec['payment'] . "<br>Balance: " . $bal;
}
db_close();

echo $str_response;
break;

}

?>