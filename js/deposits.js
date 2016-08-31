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

case 10: // manual remittance
$.ajax({
	url: 'deposits-ajax.php?p=contents' + page + par,
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

function selBankAccount() {

var brid = $('#deposit-branch').val();
$.ajax({
	url: 'deposits-ajax.php?p=select_bank_account&brid=' + brid,
	type: 'get',
	success: function(data, status) {
		$('#deposit-account-name').html(data);
	}
});

}

function deposit(src) {

var t = (src == 1) ? 'Add Deposit' : 'Edit Deposit Info';
var inForm = 'forms/deposits.php';
var id = 0;
var exe = function() {

$('#deposit-branch').change(function() {
	selBankAccount();
});

$( "#deposit-from" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$( "#deposit-to" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

selBankAccount();

};
var f = function() {
if (validateForm('frmModule')) confirmDeposit(id,src);
};

if (src == 2) {
	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		var f = function() { uncheckMulti('frmContent'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
		id = getCheckedId('frmContent');

		f2 = function() { uncheckSelected(id); }		
	
		var exe = function() {
		
		$.ajax({
			url: 'deposits-ajax.php?p=edit&depid=' + id,
			dataType: 'json',
			success: function(data, status) {
				var d = data.editdeposit[0];
				$('#deposit-branch').val(d.jdbr);
				selBankAccount();
				$('#deposit-from').val(d.jdf);
				$('#deposit-from-co').val(d.jdfc);
				$('#deposit-to').val(d.jdt);
				$('#deposit-to-co').val(d.jdtc);
				$('#deposit-account-name').val(d.jdan);
				$('#deposit-amount').val(d.jda);
				$('#deposit-note').val(d.jdn);
			}
		});		
	
		}	
		mainDialog(400,320,t,inForm,exe);
		mainDialogB('Update','Close',f,f2);
	
	}
} else {
mainDialog(400,320,t,inForm,exe);
mainDialogB('Add','Close',f);
}

}

function confirmDeposit(id,src) {

var m = 'Add this deposit?';
var f = function() { depositForm(id,src); };
confirmation(300,200,m,f);

}

function depositForm(id,src) {

var depbranch = $('#deposit-branch').val();
var depfrom = $('#deposit-from').val();
var depfromco = $('#deposit-from-co').val();
var depto = $('#deposit-to').val();
var deptoco = $('#deposit-to-co').val();
var depaname = $('#deposit-account-name').val();
var depamt = $('#deposit-amount').val();
var depnote = $('#deposit-note').val();

switch (src) {

case 1:

$.ajax({
	url: 'deposits-ajax.php?p=add',
	type: 'post',
	data: {pdepbranch: depbranch, pdepfrom: depfrom, pdepfromco: depfromco, pdepto: depto, pdeptoco: deptoco, pdepaname: depaname, pdepamt: depamt, pdepnote: depnote},
	success: function(data, status) {
	closeMainDialog();
	content(10,2); deposit(1);
	}
});

break;

case 2:

$.ajax({
	url: 'deposits-ajax.php?p=update&depid=' + id,
	type: 'post',
	data: {pdepbranch: depbranch, pdepfrom: depfrom, pdepfromco: depfromco, pdepto: depto, pdeptoco: deptoco, pdepaname: depaname, pdepamt: depamt, pdepnote: depnote},
	success: function(data, status) {
	closeMainDialog();	
	var f = function() { content(10,2,rDepositsF()); }
	notify(320,200,data,f);
	}
});

break;

}

}

function filterDeposits() {

var par = '';

var fbranch = $('#fbranch').val();
var fan = $('#faccount-name').val();
var fs = $('#fs').val();
var fe = $('#fe').val();

par = '&fbranch=' + fbranch + '&fan=' + fan + '&fs=' + fs + '&fe=' + fe;

content(10,0,par);

}

function rDepositsF() {

var par = '';

var fbranch = $('#fbranch').val();
var fan = $('#faccount-name').val();
var fs = $('#fs').val();
var fe = $('#fe').val();

par = '&fbranch=' + fbranch + '&fan=' + fan + '&fs=' + fs + '&fe=' + fe;

return par;

}

function confirmDDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete this deposit?';
		var f = function() { deleteDeposit(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(380,180,m,f,f2);
	}

}

function deleteDeposit(id) {

	$.ajax({
		url: 'deposits-ajax.php?p=delete',
		type: 'post',
		data: {depid: id},
		success: function(data, status) {
			var f = function() { content(10,2); }
			notify(300,200,data,f);
		}
	});

}

function printDeposits() {

var fbranch = $('#fbranch').val();
var fan = $('#faccount-name').val();
var fs = $('#fs').val();
var fe = $('#fe').val()
var par = '?fbranch=' + fbranch + '&fan=' + fan + '&fs=' + fs + '&fe=' + fe;
window.open('reports/deposits.php' + par, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

}