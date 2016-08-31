function cashier(src) {

var t = (src == 1) ? 'Add Cashier' : 'Edit Cashier';
var inForm = 'forms/cashier.php';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmCashier(id,src);
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
			url: 'cashier.php?p=edit&cid=' + id,
			dataType: 'json',
			success: function(data, status) {
				
				var d = data.editcashier[0];

				$('#user-type').val(d.jug);
				$('#user-branch').val(d.jub);				
				$('#username').val(d.jun);
				$('#password').val(d.jup);				
				$('#ppassword').val(d.jup);				
				$('#cashierid').val(d.jcid);
				$('#fname').val(d.jfn);
				$('#lname').val(d.jln);
				$('#position').val(d.jpos);
				$('#address').val(d.jadd);
				$('#contact').val(d.jcon);
				
			}
		});		
		$('#password').prop('disabled', true);
		$('#ppassword').prop('disabled', true);		
		}	
		mainDialog(500,500,t,inForm,exe);
		mainDialogB('Update','Close',f,f2);	
	
	}
} else {
mainDialog(500,500,t,inForm);
mainDialogB('Add','Close',f);
}

}

function confirmCashier(id,src) {

var m = (src == 1) ? 'Add this new cashier?' : 'Update casheir\'s info?';
var f = function() { cashierForm(id,src); };
confirmation(300,200,m,f);

}

function cashierForm(id,src) {

var usertype = $('#user-type').val();
var userbranch = $('#user-branch').val();
var username = $.trim($('#username').val());
var password = $('#password').val();
var cashierid = $.trim($('#cashierid').val());
var fname = $.trim($('#fname').val());
var lname = $.trim($('#lname').val());
var pos = $.trim($('#position').val());
var add = $.trim($('#address').val());
var con = $.trim($('#contact').val());

switch (src) {

case 1:

$.ajax({
	url: 'cashier.php?p=add',
	type: 'post',
	data: {pusername: username, ppassword: password, pcashierid: cashierid, pfname: fname, plname: lname, ppos: pos, padd: add, pcon: con, putype: usertype, puserbranch: userbranch},
	success: function(data, status) {
	clearForm('frmModule');
	var f = function() { content(8,2); }
	notify(300,200,data,f);
	}
});

break;

case 2:

$.ajax({
	url: 'cashier.php?p=update&cid=' + id,
	type: 'post',
	data: {pusername: username, ppassword: password, pcashierid: cashierid, pfname: fname, plname: lname, ppos: pos, padd: add, pcon: con, putype: usertype, puserbranch: userbranch},
	success: function(data, status) {
	clearForm('frmModule');
	closeMainDialog();	
	var f = function() { window.location.href = 'index.php'; /* content(8,2); */ }
	notify(320,200,data,f);
	}
});

break;

}

}

function confirmCashierDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete cashier(s)?';
		var f = function() { deleteCashier(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(420,220,m,f,f2);
	}

}

function deleteCashier(id) {

	$.ajax({
		url: 'cashier.php?p=delete',
		type: 'post',
		data: {cid: id},
		success: function(data, status) {
			var f = function() { content(8,2); }
			notify(300,200,data,f);
		}
	});

}

function filterCashier() {

var par = '';

var fcname = $('#fcname').val();
var fcashierid = $('#fcashierid').val();

par = '&fcname=' + fcname + '&fcashierid=' + fcashierid;

content(8,0,par);

}

function rCashierF() {

var par = '';

var fcname = $('#fcname').val();
var fcashierid = $('#fcashierid').val();

par = '&fcname=' + fcname + '&fcashierid=' + fcashierid;

return par;

}