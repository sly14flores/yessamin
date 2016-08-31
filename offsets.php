<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Offsets from Returns</title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}

img {
	border: 0;
}

body {
	background-color: #fbf8fa;
	font: 14px sans-serif;
}

#sub-menu {
	width: 100px;
	margin-top: 0;
	text-align: center;
	margin-left: auto;
	margin-right: auto;
}

#in-sub-menu {
	padding-top: 10px;
}

#search-bar {
	margin: 0 auto;
	text-align: center;
}

#filter {
	margin: 15px auto;
	border: 1px solid #f3cce6;
	border-radius: 5px;
}

#search-bar  #filter td {
	padding: 3px;
}

#search-bar #filter input {
		font-size: 1.1em;
}

#content {
	width: 90%;
	margin: 20px auto 0 auto;
	border: 1px solid #f3cce6;
	border-radius: 5px;	
	background-color: #fff;
}

#in-content {
	padding: 10px;
}

#content-page {
	width: 100%;
	border-collapse: collapse;
	font-family: sans-serif;
}

#content-page thead {
	color: #d235a1;
}

#content-page thead tr {
	border-bottom: 2px solid #d0b252;
}

#content-page thead td {
	padding-bottom: 4px;
}

#content-page thead td:first-child {
	padding-left: 3px;
}

#content-page tbody td {
	padding: 7px 0 5px 3px;
	border-bottom: 1px solid #d0b252;
	color: #265fb9;
}

#content-page tbody td:last-child {
	padding-left: 0;
}

#content-page tbody td img {
	padding-right: 5px;
}

#content-page tfoot ul {
	list-style-type: none;
	float: left;
	margin-top: 5px;
	padding-top: 10px;
}

#content-page tfoot li {
	float: left;
	padding-right: 10px;
}

#frmModule table {
	width: 100%;
	border-collapse: collapse;
	padding-bottom: 8px;
	padding-right: 8px;
}

#frmModule td {
	padding-bottom: 8px;
	padding-right: 8px;
}

.validate {
	padding-top: 5px;
	color: #dd4b39;
}

/* tabulated items */
#frmOffsetItem {
	height: 250px;
}

#frmOffsetItem table {
	border-collapse: collapse;
	width: 100%;
	overflow: scroll;
}

#tab-offset-item thead td {
	background-color: #dd4b39;
	padding: 5px;
	color: #fff;
	font-weight: bold;
}

#tab-offset-item td {
	padding: 5px;
}

#tab-offset-item tbody td {
	border-bottom: 1px solid #dd4b39;
}

.fixh {
	overflow: scroll;
}

.row-style-odd:hover, .row-style-even:hover {
	background-color: #fddfdf;
}

/* tooltip */
a.tooltip:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none; padding:2px 3px; margin-top:60px; margin-left:30px; width:100px;}
a.tooltip:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}

a.tooltip-min:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip-min span {display:none; padding:2px 3px; margin-top:27px; margin-left:-25px; width:60px; font-size:.9em; text-align:center;}
a.tooltip-min:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}

a.tip-min:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tip-min span {display:none; padding:5px; margin-top:5px; margin-left:-15px; width:100px; font-size:.9em; text-align:center;}
a.tip-min:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/dialogbox.js"></script>
<script type="text/javascript" src="js/globalf.js"></script>
<script type="text/javascript" src="js/offset.js"></script>
<script type="text/javascript">
$(function() {

var m = 'modules/offsets.php';
var f = function() {
	$('#add').button();
	$('#add').click(function() { returnOffsets(1,0,'0'); });
	$('#search').button();
	$('#search').click(function() { filterOffsets(); });
	
	$('#foffset-date').datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
	});
	
	$.ajax({
	type: 'json',
	url: 'stock-in.php?p=list_suppliers',
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.supplierlist = data;
			 $('input#foffset-co').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#foffset-coid').val(item.id); }});            
			 }
	});	

	$.ajax({
	type: 'json',
	url: 'stock-in.php?p=list_members',
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.productlist = data;
			 $('input#foffset-mn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
			 }
	});	
	
	$.ajax({
	type: 'json',
	url: 'stock-in.php?p=list_offsets',
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.supplierlist = data;
			 $('input#foffset-id').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ }});            
			 }
	});	
	
	highl();	
};
$('#sub-menu-search').load(m, function() { f(); });

content(13,0);

});
</script>
</head>
<body>
<div id="sub-menu-search">
</div>
<div id="content">
<div id="in-content">
</div>
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