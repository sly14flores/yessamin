<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PAID TRANSACTION</title>
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
<script type="text/javascript">
$(function() {
	$('#check').button();
	$('#check').click(function() { checkItem(); });

	$('#update').button();
	$('#update').click(function() { searchItem(); });
	highl();
});

function highl() {

$('.highlight').click(function() {
	$(this).select();
});

}

function checkItem() {
	var item = $('#item-check').val();
	if (item != "") {
	$.ajax({
	url: 'utils.php?p=check_tra',
	type: 'post',
	data: {pitem: item},
	success: function(data, status) {
		notify(300,200,data);
	}
	});
	} else {
	alert("Enter TRA.");
	}
}

function searchItem() {
	var item = $('#item-update').val();
	if (item != "") {
	$.ajax({
	url: 'utils.php?p=paid_tra',
	type: 'post',
	data: {pitem: item},
	success: function(data, status) {
		notify(300,200,data);
	}
	});
	} else {
	alert("Enter TRA.");
	}
}
</script>
</head>
<body>
<div id="top">
<input type="text" id="item-check" class="highlight" />&nbsp;<input type="button" id="check" value="CHECK" />
<input type="text" id="item-update" class="highlight" />&nbsp;<input type="button" id="update" value="UPDATE" />
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