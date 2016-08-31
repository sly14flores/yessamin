cpage = null;
cdir = null;

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

case 2: // inventory
$.ajax({
	url: 'inventory-ajax-static.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	$('#to-print').val(sdata[1]);
//	last_page = parseInt(sdata[1]);
//	cpage = parseInt(sdata[2]);
//	cdir = parseInt(sdata[3]);
	adjConH();
	}
});
break;

}

}

function filterInventory() {

var par = '';

var fhsup = $('#fhsup').val();
var fcat = $('#fcat').val();
var fpcon = $.trim($('#fpcon').val());
var fsd = $('#fsd').val();
var fed = $('#fed').val();

var chksup = $('#fsup').val();

if (chksup == '') fhsup = 0;

par = '&fhsup=' + fhsup + '&fcat=' + fcat + '&fpcon=' + fpcon + '&fsd=' + fsd + '&fed=' + fed;

content(2,0,par);

}

function rInventoryF() {

var par = '';

var fhsup = $('#fhsup').val();
var fcat = $('#fcat').val();
var fpcon = $.trim($('#fpcon').val());
var fsd = $('#fsd').val();
var fed = $('#fed').val();

par = '&fhsup=' + fhsup + '&fcat=' + fcat + '&fpcon=' + fpcon + '&fsd=' + fsd + '&fed=' + fed;

return par;

}

function printInventory() {


var fhsup = $('#fhsup').val();
var fcat = $('#fcat').val();
if (fcat == undefined) fcat = 0;
var fpcon = $.trim($('#fpcon').val());
var fsd = $('#fsd').val();
var fed = $('#fed').val();

//window.open('reports/inventory.php' + '?fhsup=' + fhsup + '&fpcon=' + fpcon + '&n=' + cpage + '&d=' + cdir, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

var top = $('#to-print').val();
window.open('reports/inventory-static.php' + '?fhsup=' + fhsup + '&fcat=' + fcat + '&fpcon=' + fpcon + '&fsd=' + fsd + '&fed=' + fed + '&top=' + top, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

}