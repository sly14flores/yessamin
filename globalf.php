<?php

class pageNav {

var $param;
var $cur;
var $min;
var $max;
var $str_first;
var $str_previous;
var $cur_page;
var $str_next;
var $str_last;
var $inNav;
var $s;

function __construct($src,$par,$cur_page,$max_page) {

	$this->s = $src;
	$this->param = $par;
	$this->cur = $cur_page;
	$this->min = 1;
	$this->max = $max_page;

	$this->str_first = '<a href="javascript: content(' . $this->s . ',0,' . $this->param . ');"><img src="images/first_active.png" border="0" /></a>';
	$this->str_previous = '<a href="javascript: content(' . $this->s . ',-1,' . $this->param . ');"><img src="images/previous_active.png" border="0" /></a>';
	$this->cur_page = '<input type="text" id="curpage" size="3" onfocus="this.blur();" style="text-align: center;" border="0" value="' + $this->cur + '" />';
	$this->str_next = '<a href="javascript: content(' . $this->s . ',1,' . $this->param . ');"><img src="images/next_active.png" border="0" /></a>';
	$this->str_last = '<a href="javascript: content(' . $this->s . ',3,' . $this->param . ');"><img src="images/last_active.png" border="0" /></a>';

	if ($this->cur == $this->min) {
			$this->str_first = '<img src="images/first_disabled.gif" border="0" />';
			$this->str_previous = '<img src="images/previous_disabled.gif" border="0" />';
	}
	if ($this->cur == $this->max) {
			$this->str_next = '<img src="images/next_disabled.gif" border="0" />';
			$this->str_last = '<img src="images/last_disabled.gif" border="0" />';      
	}
	
}

function getNav() {

	$this->inNav  = '<ul>';
	$this->inNav .= '<li>Navigate Page:</li>';
	$this->inNav .= '<li>' . $this->str_first . '</li>';
	$this->inNav .= '<li>' . $this->str_previous . '</li>';
	$this->inNav .= '<li>' . $this->cur_page . '</li>';
	$this->inNav .= '<li>' . $this->str_next . '</li>';
	$this->inNav .= '<li>' . $this->str_last . '</li>';
	$this->inNav .= '</ul>';

	return $this->inNav;
	
}

}

function extStr($str,$no) {

$npos = strpos($str,$no); // find the number's position
$lstr = substr($str,0,$npos); // extract left part
$rstr = substr($str,$npos + strlen($no) + 1); // extract right part
$nstr = $lstr . $rstr; // concat both extraced parts
if (strlen($rstr) == 0) $nstr = substr($nstr,0,strlen($nstr) - 1); // if position is last truncate comma

return $nstr;

}

function staticInventory($sid) {

global $db_con, $START_T, $END_T;

if ($sid != "") {
$sid = substr($sid,0,strlen($sid)-1);
$sql = "select stock_in_id, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) inventory from stock_in_item where stock_in_id in ($sid)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$nsql = "update stock_in_item set stock_in_inventory = " . $rec['inventory'] . " where stock_in_id = " . $rec['stock_in_id'];
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}
}   
db_close();
}

}

function totalCL($pdid) {

global $db_con;

$total_cl = 0;

$sql = "select ";
/* amount */
// $sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, ";
/* balance */
$sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance ";
/* tra_gross_bal  */
// $sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id ";
$sql .= "where customer_id = $pdid and tra_cash = 0";
// $sql .= "and ((round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	for ($i=0; $i<$rc; ++$i) {
		$rec = $rs->fetch_array();
		$total_cl += $rec['balance'];
	}
}
db_close();

return $total_cl;

}

function debug_log($txt) {

$file = fopen("debug.txt","a+");
fwrite($file,$txt."\r\n");
fclose($file);

}

?>