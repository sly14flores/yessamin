var list_page = null;
var last_page = null;
var objtday = new Date();
var tday = (objtday.getMonth() + 1).toString() + '/' + objtday.getDate() + '/' + objtday.getFullYear();

$(function() {

loadMenu(1);

});

function loadMenu() {

var args = loadMenu.arguments;
var src = args[0];

var ld = '<div class="module-loading"><img src="images/ajax-loader.gif" /><p>Loading...</p></div>';
if ((src != 2) && (src != 5) && (src != 6) && (src != 9) && (src != 10)) $('#sub-menu-search').html(ld);

if ((src != 2) && (src != 5) && (src != 6) && (src != 9) && (src != 10)) {

for (i=1; i<=tabn; i++) {
        itemclass = $('#menu-' + i)[0];
		if (!itemclass) continue;
        if (itemclass.className == 'active') {
                itemclass.className = '';
        }
}

itemclass = $('#menu-' + src)[0];

if (itemclass) {
        itemclass.className = 'active';
}

var m = moduleSub(src);

}

switch (src) {

case 1: // stock-in
var f = function() {

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_suppliers',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#fsupplier').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#fsup').val(item.psup); }});            
		 }
});

$( "#fs" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$( "#fe" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$( "#fd" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$.ajax({
type: 'json',
url: 'inventory-ajax.php?p=list_products',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#fpcon').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
		 }
});

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_members',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#fmemn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
			filterOffsetID(item.text);
		 }});            
		 }
});

highl();

}
$('#sub-menu-search').load(m, function() { f(); });
// content(1,0,'&fs=' + tday + '&fe=' + tday);
$('#in-content').html('');
break;

case 2: // inventories
var params = [
	'width=1024px', //+screen.width,
	/*'height='+screen.height,*/
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('inventory.php', '', params);
break;

case 3: // customers/dealers
var f = function() {

$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#ffname').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
			$('#ffname').val(item.dealer);
		}});				 
		}
});

$.ajax({
type: 'json',
url: 'customer.php?p=list_recruiters',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#frname').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
			$('#frname').val(item.dr);
		}});				 
		}
});

highl();

}
$('#sub-menu-search').load(m, function() { f(); });
content(3,0);
break;

case 4: // transactions
var f = function() {

$( "#fs" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$( "#fe" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#fcustomer').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
			$('#fcustomer').val(item.dealer);	
		}});				 
		}
});

$.ajax({
type: 'json',
url: 'inventory-ajax.php?p=list_products',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#fpcon').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
		 }
});

highl();
//soldOutStock();

};
$('#sub-menu-search').load(m, function() { f(); });
// content(4,0,'&fs=' + tday + '&fe=' + tday);
$('#in-content').html('');
break;

case 5: // payments
var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('payment.php', '', params);
break;

case 6: // remittances
var params = [
	'width='+screen.width,
	'height='+screen.height,	
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('remittance.php', '', params);
break;

case 7: // discounts
var f = function() {

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_suppliers',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#fsupplier').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#fsup').val(item.psup); }});            
		 }
});

highl();

}
$('#sub-menu-search').load(m, function() { f(); });
content(7,0);
break;

case 8: // cashiers
var f = function() {
	highl();
}
$('#sub-menu-search').load(m, function() { f(); });
content(8,0);
break;

case 9: // manual remittances
var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('manual-remittances.php', '', params);
break;

case 10:
var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('deposits.php', '', params);
break;

}

}

function moduleSub(src) {

var m = '';

switch (src) {

case 1: // stock-in
m = 'modules/stock-in.php';
break;

case 3: // customers/dealers
m = 'modules/customer.php';
break;

case 4: // transactions
m = 'modules/transaction.php';
break;

case 7: // discounts
m = 'modules/discount.php';
break;

case 8: // cashiers
m = 'modules/cashier.php';
break;

}

return m;

}

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

case 1: // stock-in
$.ajax({
	url: 'stock-in.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

case 3: // customers
$.ajax({
	url: 'customer.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

case 4: // transactions
$.ajax({
	url: 'transaction.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

case 7: // discounts
$.ajax({
	url: 'discount.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

case 8: // cashiers
$.ajax({
	url: 'cashier.php?p=contents' + page + par,
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

function clearForm(frm) {

var f = $('#' + frm)[0];
var e = f.elements;
var id = '';

for (i=0; i<e.length; ++i) {
	if ((e[i].type == 'text') || (e[i].type == 'password')) {
		id = document.getElementById(e[i].name);
		if (id.className != 'iclear') $(id).val('');
	}
}

}

function validateForm(frm) {

var f = $('#' + frm)[0];
var e = f.elements;
var c = '';
var id = '';
var r = true;
var p1 = '';
var p2 = '';
var pid = '';
var pid1 = '';
var pid2 = '';
var pc1 = '';
var pc2 = '';

$('.validate').html('');
$('.password').html('');

for (i=0; i<e.length; ++i) {
	if ((e[i].type == 'text') || (e[i].type == 'password')) {
		id = document.getElementById(e[i].name);
		c = $(id).val();
		
		if (c == '') {
			if (id.className != 'iclear') {
			$(id).css('border', '2px groove #dd4b39');
			r = false;
			if (frm == 'frmModule') $('.validate').html('All field(s) are required.');
			else $('.sub-validate').html('All field(s) are required.');
			}		
		} else {
		$(id).css('border','');
		}
		
	}
	if (e[i].type == 'password') {
		pid = document.getElementById(e[i].name);
		if (pid.name == 'password') {
		pc1 = $(pid).val();
		pid1 = pid;
		}
		if (pid.name == 'ppassword') {
		pc2 = $(pid).val();
		pid2 = pid;
		}
	}
}

if ((pid1 != '') && (pid2 != '')) {
	if ((pc1 != '') && (pc2 != '')) {
	if (pc1 != pc2) { r = false; $('.password').html('Password does not match.'); }
	} 
}

return r;

}

function confirmLogOut() {

var m = 'Are you sure you want to logout?';
var f = function() { logout(); };
confirmation(320,200,m,f);

}

function logout() {

window.location.href = 'logout.php';

}

function adjConH() {

var h = $('#content').height();
if (h < 450) $('#content').css('height','450px');
else $('#content').css('height','');

}

function Check_all(theForm, theParentCheck){
	elem = theForm.elements;
		
	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			elem[i].checked	= theParentCheck.checked;
		}
	}
}

function Uncheck_Parent(ParentCheckboxName, me){
	var theParentCheckbox = document.getElementById(ParentCheckboxName);
	
	if(!me.checked && theParentCheckbox.checked){
		theParentCheckbox.checked = false;		
	}
}

function uncheckSelected(id) {

	$('#chk_' + id).prop('checked',false);

}

function uncheckMulti(frm) {

	var f = $('#' + frm)[0];
	var e = f.elements;

	for (i=0; i<e.length; ++i) {
		if (e[i].type == "checkbox") {
			if (e[i].checked) e[i].checked = false;
		}
	}

}

function getCheckedId(theFormName){
var theForm		= document.getElementById(theFormName);
var	elem		= theForm.elements;
var tmp_arr, rec_id;

	rec_id	= "";

	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			if (elem[i].checked && elem[i].name != 'chk_checkall'){
				tmp_arr	= elem[i].name.split('_');
				rec_id	+= tmp_arr[1] + ',';
			}
		}
	}

	if (rec_id.length > 0){
		rec_id = rec_id.substr(0, rec_id.length-1);
	}
	return rec_id;
}

function count_checks(theFormName){
var theForm		= document.getElementById(theFormName);
var	elem		= theForm.elements;
var int_count	= 0;
		
	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			if (elem[i].checked  && elem[i].name != 'chk_checkall') ++int_count;
		}
	}
	
	return int_count;
}

function validateSettingForm(frm) {

var f = $('#' + frm)[0];
var e = f.elements;
var c = '';
var id = '';
var r = true;
var p1 = '';
var p2 = '';
var pid = '';
var pid1 = '';
var pid2 = '';
var pid3 = '';
var pc1 = '';
var pc2 = '';
var pc3 = '';

$('.validate').html('');
$('.password').html('');

var chk = $('#change-password').prop('checked');

for (i=0; i<e.length; ++i) {
	if ((e[i].type == 'text') || (e[i].type == 'password')) {
		id = document.getElementById(e[i].name);
		c = $(id).val();
		if (chk) {
			if (c == '') {
			$(id).css('border', '2px groove #dd4b39');
			r = false;
			$('.validate').html('All field(s) are required.');
			} else {
			$(id).css('border','');
			}
		}
	}
	if (e[i].type == 'password') {
		if (chk) {
			pid = document.getElementById(e[i].name);
			if (pid.name == 'opassword') {
			pc1 = $(pid).val();
			pid1 = pid;
			}
			if (pid.name == 'npassword') {
			pc2 = $(pid).val();
			pid2 = pid;
			}
			if (pid.name == 'nnpassword') {
			pc3 = $(pid).val();
			pid3 = pid;
			}
		}
	}
}

if ((pid1 != '') && (pid2 != '') && (pid3 != '')) {
	if (chk) {
		if ((pc1 != '') && (pc2 != '') && (pc3 != '')) {
			var p = $('#user-password').val();	
			if (pc1 != p) {
				r = false; $('.password').html('Old password is invalid.');
			} else if (pc2 != pc3) {
				r = false; $('.password').html('New Password does not match.');
			}		
		}
	}
}

return r;

}

function settings() {

var t = 'Settings';
var inForm = 'forms/setting.php';

var exe = function() {

$('#sys-msg').html('');

$('#opassword').prop('disabled',true);
$('#npassword').prop('disabled',true);
$('#nnpassword').prop('disabled',true);

$('#change-password').click(function() { toggleChangePassword(); });

$('#brestore').button();
$('#brestore').click(function() { restoreDB(); });

/* upload db */
$('#db-file').change(function() {

$.blockUI({ message: $('#processModal') });	

var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;

$('#frmUploadDb').submit();

document.getElementById('upload_target').onload = function() {
	$('#sys-msg').html('Click restore now to sync database.');
	$.unblockUI();
};

});
/* * */

};

var f = function() {
if (validateSettingForm('frmModule')) confirmSetting();
};

mainDialog(420,450,t,inForm,exe);
mainDialogB('Update','Close',f);

}

function confirmSetting() {

var chk = $('#change-password').prop('checked');

if (chk) {
var m = 'Update Settings?';
var f = function() { settingForm(); };
confirmation(300,200,m,f);
} else {
notify(300,200,'Nothing to update.');
}

}

function settingForm() {

var password = $('#npassword').val();

$.ajax({
	url: 'content.php?p=update',
	type: 'post',
	data: {pnpassword: password},
	success: function(data, status) {
	closeMainDialog();
	notify(300,200,data);
	}
});

}

function chkToggleChangePassword() {

var chk = $('#change-password').prop('checked');
var tog = (chk) ? false : true;
$('#change-password').prop('checked', tog);
toggleChangePassword();

}

function toggleChangePassword() {

var chk = $('#change-password').prop('checked');
var tog = (chk) ? false : true;

$('#opassword').prop('disabled',tog);
$('#npassword').prop('disabled',tog);
$('#nnpassword').prop('disabled',tog);

}

function highl() {

$('.highlight').click(function() {
	$(this).select();
});

}

function chkRow(row) {

var gi = row.getElementsByTagName('input')[0];
gi.checked = !gi.checked;

}

function roundToTwo(value) { // round off float to 2 decimal places
    return(Math.round(value * 100) / 100);
}

function restoreDB() {

if ($('#db-file').val() == '') {
	$('#sys-msg').html('Please select database first.');
	return;
}

$.blockUI({ message: $('#processModal') });	

$.ajax({
	url: 'transaction.php?p=restore_db',
	type: 'post',
	success: function(data, status) {		
		$.unblockUI();
	}
});

}