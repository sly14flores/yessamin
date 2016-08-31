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

case 8: // avon
$.ajax({
	url: 'avon-ajax.php?p=contents' + page + par,
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

function editAvon(id) {

var t = 'Dealer\'s Avon Info';
var inForm = 'forms/avon.php';
var f = function() {
if (validateForm('frmModule')) confirmAvon(id);
};

var exe = function() {

$.ajax({
	url: 'avon-ajax.php?p=edit&adid=' + id,
	dataType: 'json',
	success: function(data, status) {
		
		var d = data.editavon[0];

		$('#avon-dealer').val(d.jad);
		$('#cft').val(d.jcft);				
		$('#ncft').val(d.jncft);				
		$('#hcms').val(d.jhcms);
		$('#hc').val(d.jhc);
		
	}
});	

}

mainDialog(300,280,t,inForm,exe);
mainDialogB('Update','Close',f);

}

function confirmAvon(id) {

var m = 'Update dealer\'s avon info?';
var f = function() { avonForm(id); };
confirmation(300,200,m,f);

}

function avonForm(id) {

var cft = $('#cft').val();		
var ncft = $('#ncft').val();
var hcms = $('#hcms').val();
var hc = $('#hc').val();

$.ajax({
	url: 'avon-ajax.php?p=update',
	type: 'post',
	data: {padid: id, pcft: cft, pncft: ncft, phcms: hcms, phc: hc},
	success: function(data, status) {
	closeMainDialog();
	var f = function() { content(8,2); }
	notify(300,200,data,f);
	}
});

}

function filterAvon() {

var par = '';

var fcustomer = $.trim($('#fcustomer').val());
par = '&fcustomer=' + fcustomer;

content(8,0,par);

}

function rAvonF() {

var par = '';

var fcustomer = $.trim($('#fcustomer').val());
par = '&fcustomer=' + fcustomer;

return par;

}
