function content() {

var args = content.arguments;
var src = args[0];
var dir = args[1];
var par = '';

if (args.length > 2) par = args[2];

var ld = '<div class="content-loading"><img src="images/ajax-loader.gif" /><p>Loading...</p></div>';
$('#in-content').html(ld);

switch (dir) {

case 0: // first page
list_page = 1;
break;

case 2: // current page
break;

case 3: // last page
list_page = last_page;
break;

default: // previous next -1/1
list_page = (list_page) + parseInt(dir);

}

var page = '&n=' + list_page + '&d=' + dir;

switch (src) {

case 11: // dealer returns
$.ajax({
	url: 'dealer-return-ajax.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

}

}

function filterReturnStock() {

var par = '';

var fdate = $.trim($('#fdate').val());
var fhsup = $('#fhsup').val();
var frno = $.trim($('#frno').val());
var fpcon = $.trim($('#fpcon').val());

par = '&fdate=' + fdate + '&fhsup=' + fhsup + '&frno=' + frno + '&fpcon=' + fpcon;

content(10,0,par);

}

function rStockReturnF() {

var par = '';

var fdate = $.trim($('#fdate').val());
var fhsup = $('#fhsup').val();
var frno = $.trim($('#frno').val());
var fpcon = $.trim($('#fpcon').val());

par = '&fdate=' + fdate + '&fhsup=' + fhsup + '&frno=' + frno + '&fpcon=' + fpcon;

return par;

}

function filterReturnDealer() {

var par = '';

var fs = $('#fs').val();
var fe = $('#fe').val();
var fdealer = $('#fdealer').val();
var fhsup = $('#fhsup').val();
var ftrano = $.trim($('#ftrano').val());
var fpcon = $.trim($('#fpcon').val());

par = '&fs=' + fs + '&fe=' + fe + '&fdealer=' + fdealer + '&fhsup=' + fhsup + '&ftrano=' + ftrano + '&fpcon=' + fpcon;

content(11,0,par);

}

function rDealerReturnF() {

var par = '';

var fs = $('#fs').val();
var fe = $('#fe').val();
var fdealer = $('#fdealer').val();
var fhsup = $('#fhsup').val();
var ftrano = $.trim($('#ftrano').val());
var fpcon = $.trim($('#fpcon').val());

par = '&fs=' + fs + '&fe=' + fe + '&fdealer=' + fdealer + '&fhsup=' + fhsup + '&ftrano=' + ftrano + '&fpcon=' + fpcon;

return par;

}

function confirmCancelDRet(id) {

	var m = 'Are you sure you want to cancel this return?';
	var f = function() { cancelDReturn(id); };
	confirmation(420,220,m,f);

}

function cancelDReturn(id) {

	$.ajax({
		url: 'dealer-return-ajax.php?p=delete',
		type: 'post',
		data: {rid: id},
		success: function(data, status) {
			var f = function() { content(11,2); }
			notify(300,200,data,f);
		}
	});

}