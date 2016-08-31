<?php

session_start();
require '../grants.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>STOCK-IN ITEM</title>
<style type="text/css">
#tab-content {
	margin-top: 10px;
	border-collapse: collapse;
	width: 100%;
}

#tab-content td {
	padding: 5px;
	border: 1px solid;
}

.odd {
	background-color: #E0E0E0;
}

.even {
	background-color: #fff;
}
</style>
<link rel="icon" type="image/ico" href="../favicon.ico" />
<link rel="shortcut icon" href="../favicon.ico" />
<link href="../jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="../js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="../js/dialogbox.js"></script>
<script type="text/javascript" src="../js/globalf.js"></script>
<script type="text/javascript">
$(function() {
	$('#search').button();
	$('#search').click(function() { searchItem(); });
	$('#item-sd').datepicker();
	$('#item-ed').datepicker();
	highl();
	$.ajax({
	type: 'json',
	url: '../inventory-ajax.php?p=list_products',
	async: false,
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.productlist = data;
			 $('input#item-search').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
			 }
	});
	$.ajax({
	type: 'json',
	url: '../stock-in.php?p=list_suppliers',
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.supplierlist = data;
			 $('input#item-sup').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#item-hsup').val(item.id); }});            
			 }
	});	
});

function searchItem() {
	var isd = $('#item-sd').val();
	var ied = $('#item-ed').val();
	var item = $('#item-search').val();
	var pcode = $('#cpcode').val();
	var pname = $('#cpname').val();
	var psize = $('#cpsize').val();
	var sup = $('#item-hsup').val();
	if ($('#item-sup').val() == '') sup = 0;	
	if (item != "") {
	$.ajax({
	url: 'utils.php?p=stock_in_item',
	type: 'post',
	data: {pisd: isd, pied: ied, pitem: item, cpcode: pcode, cpname: pname, cpsize: psize, psup: sup},
	success: function(data, status) {
		$('#content').html(data);
	}
	});
	} else {
	alert("Enter Product.");
	}
}

function execQuery(sql) {
	$.ajax({
		url: 'utils.php?p=execute_query',
		type: 'post',
		data: {psql: sql},
		success: function(data, success) { alert(data); }
	});
}

function copyPcode(pcode) {

$('#cpcode').val(pcode);

}

function copyPname(pname) {

$('#cpname').val(pname);

}

function copyPsize(psize) {

$('#cpsize').val(psize);

}
</script>
</head>
<body>
<div id="top">
Start: <input type="text" id="item-sd" class="highlight" /> End: <input type="text" id="item-ed" class="highlight" /> Supplier: <input type="text" id="item-sup" class="highlight" /><input type="hidden" id="item-hsup" /> Product: <input type="text" id="item-search" class="highlight" style="width: 500px;" />&nbsp;<input type="button" id="search" value="SEARCH" />
<?php if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) { ?>
<p>Change with this:</p>
<label>Code:</label><input type="text" id="cpcode" value="" /><label>Name:</label><input type="text" id="cpname" value="" /><label>Size:</label><input type="text" id="cpsize" value="" />
<?php } ?>
</div>
<div id="content">
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
</body>
</html>