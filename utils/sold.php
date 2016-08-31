<?php

require '../config.php';
require '../globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

$sql = "select stock_in_id, stock_in_sid, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id order by sin_date_invoice, stock_in_id";
//$sql = "select stock_in_id, stock_in_sid, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where sin_date_invoice <= date_sub(now(), interval 2 month) and stock_soldout = 0 order by sin_date_invoice, stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$sis = "0,";
$nsis = "0,";
    for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    if ($rec['stocks'] <= 0) {
        $sis .= $rec['stock_in_id'] . ",";
    } else {
        $nsis .= $rec['stock_in_id'] . ",";
    }    
    }
$sis = substr($sis,0,strlen($sis)-1);
$nsis = substr($nsis,0,strlen($nsis)-1);
$nsql = "update stock_in_item set stock_soldout = 1 where stock_in_id in (" . $sis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
$nsql = "update stock_in_item set stock_soldout = 0 where stock_in_id in (" . $nsis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}   
db_close();

echo "Sold out stocks updated.";

?>