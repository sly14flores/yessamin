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

case 6: // inventory
$.ajax({
	url: 'remittance-ajax.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	var sdata = data.split('|');
	$('#in-content').html(sdata[0]);
	//last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

}

}

function filterRemittance() {

var par = '';

var fbranch = $('#fbranch').val();
var fdate = $('#fdate').val();
var fcutoff = $('#fcutoff').val();

par = '&fbranch=' + fbranch + '&fdate=' + fdate + '&fcutoff=' + fcutoff;

content(6,0,par);

}

function rRemittanceF() {

var par = '';

var fbranch = $('#fbranch').val();
var fdate = $('#fdate').val();
var fcutoff = $('#fcutoff').val();

par = '&fbranch=' + fbranch + '&fdate=' + fdate + '&fcutoff=' + fcutoff;

return par;

}

function printRemittance() {

var fbranch = $('#fbranch').val();
var fdate = $('#fdate').val();
var fcutoff = $('#fcutoff').val();
if (fdate == '') {
	notify(300,150,'Please select date.');
	return;
}
window.open('reports/remittance.php?fbranch=' + fbranch + '&fdate=' + fdate + '&fcutoff=' + fcutoff, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

}

function deduction(src) {

var t = (src == 1) ? 'Add Deduction' : 'Edit Deduction';
var inForm = 'forms/deduction.php';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmDeduction(id,src);
};


if (src == 2) {
	if (count_checks('frmContent2') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent2') > 1) {
		var f = function() { uncheckMulti('frmContent2'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
	id = getCheckedId('frmContent2');

	f2 = function() { uncheckSelected(id); }	
	
	var exe = function() {
	
	$.ajax({
		url: 'remittance-ajax.php?p=edit_deduction&dedid=' + id,
		dataType: 'json',
		success: function(data, status) {
			var d = data.editded[0];
			$('#ded-cat').val(d.jdc);
			$('#ddesc').val(d.jdd);
			$('#damt').val(d.jda);
			$('#dnote').val(d.jdn);			
		}	
	});
	
	};

	mainDialog(350,300,t,inForm,exe);
	mainDialogB('Update','Close',f,f2);	
	}
} else {
mainDialog(350,300,t,inForm);
mainDialogB('Add','Close',f);
}

}

function confirmDeduction(id,src) {

var m = (src == 1) ? 'Add this deduction?' : 'Update deduction\'s info?';
var f = function() { deductionForm(id,src); };
confirmation(300,200,m,f);

}

function deductionForm(id,src) {

var dcat = $('#ded-cat').val();
var ddesc = $('#ddesc').val();
var damt = $('#damt').val();
var dnote = $('#dnote').val();

switch (src) {

case 1:

$.ajax({
	url: 'remittance-ajax.php?p=add',
	type: 'post',
	data: {pdcat: dcat, pddesc: ddesc, pdamt: damt, pdnote: dnote},
	success: function(data, status) {
	clearForm('frmModule');
	$('#dnote').val('');
	var f = function() { content(6,2,rRemittanceF()); }
	notify(300,200,data,f);
	}
});

break;

case 2:

$.ajax({
	url: 'remittance-ajax.php?p=update&dedid=' + id,
	type: 'post',
	data: {pdcat: dcat, pddesc: ddesc, pdamt: damt, pdnote: dnote},
	success: function(data, status) {
	closeMainDialog();	
	var f = function() { content(6,2,rRemittanceF()); }
	notify(320,200,data,f);
	}
});

break;

}

}

function confirmDeductionDelete() {

	if (count_checks('frmContent2') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent2');
		var m = 'Delete this deduction(s)?';
		var f = function() { deleteDeduction(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(350,150,m,f,f2);
	}

}

function deleteDeduction(id) {

	$.ajax({
		url: 'remittance-ajax.php?p=delete',
		type: 'post',
		data: {dedid: id},
		success: function(data, status) {
			var f = function() { content(6,2,rRemittanceF()); }
			notify(300,200,data,f);
		}
	});

}

function updateActualCash(b,d) {
var ac = $('#acash').val();

$.ajax({
	url: 'remittance-ajax.php?p=actual_cash&fbranch=' + b + '&fdate=' + d,
	type: 'post',
	data: {pac: ac},
	success: function(data, status) {
		filterRemittance();
	}
});

}

function updateActualFcf(b,d) {
var ac = $('#acashfc').val();

$.ajax({
	url: 'remittance-ajax.php?p=actual_cash_fc&fbranch=' + b + '&fdate=' + d,
	type: 'post',
	data: {pac: ac},
	success: function(data, status) {
		filterRemittance();
	}
});

}

function updateActualEod(b,d) {
var ac = $('#acasheod').val();

$.ajax({
	url: 'remittance-ajax.php?p=actual_cash_eod&fbranch=' + b + '&fdate=' + d,
	type: 'post',
	data: {pac: ac},
	success: function(data, status) {
		filterRemittance();
	}
});

}

function cutOff() {

var tday = new Date();
var co = 'First Cutoff';
if ((tday.getDay() == 0) || (tday.getDay() == 6)) co = 'End of the day';
// if (tday.getDay() == 5) co = 'End of the day';

var m = 'Proceed ' + co + ' ' + (parseInt(tday.getMonth()) + 1) + '/' + tday.getDate() + '/' + tday.getFullYear();
var f = function() { doCutoff(); };
confirmation(320,200,m,f);

}

function doCutoff() {

var par = '';

var fbranch = $('#fbranch').val();
var fdate = $('#fdate').val();

par = '&fbranch=' + fbranch + '&fdate=' + fdate;

var tday = new Date();
var co = 'first_cutoff';
if ((tday.getDay() == 0) || (tday.getDay() == 6)) co = 'end_of_the_day';
//if (tday.getDay() == 5) co = 'end_of_the_day';

$.ajax({
	url: 'remittance-ajax.php?p=' + co + par,
	type: 'get',
	success: function(data, status) {
		filterRemittance();
		notify(350,200,data);
	}
});

}