function customer(src) {

var t = (src == 1) ? 'Add Dealer' : 'Edit Dealer';
var inForm = 'forms/customer.php';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmCustomer(id,src);
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
		
		// for adding additional credit line
		$('#addcl').prop('disabled',false);
		$('#reset-cl').prop('disabled',false);		
		
		$.ajax({
			url: 'customer.php?p=edit&cid=' + id,
			dataType: 'json',
			success: function(data, status) {
				
				var d = data.editcustomer[0];

				$('#mnumber').val(d.jdn);
				$('#climit').val(d.jdcl);
				$('#fname').val(d.jdfn);
				$('#lname').val(d.jdln);
				$('#mname').val(d.jdmn);
				$('#address').val(d.jdad);
				$('#contact').val(d.jdc);
				$('#age').val(d.jda);
				$('#bday').val(d.jdb);
				$('#job').val(d.jdo);
				$('#cstatus').val(d.jdcs);
				$('#spouse').val(d.jdsn);
				$('#recruiter').val(d.jdr);
				$('#discountt').val(d.jddt);
				$('#selCredit').val(d.jdclt);
				$('#customer-category').val(d.jcc);
				$('#cbranch').val(d.jdbr);
				var c = (parseInt(d.jdut) == 1) ? true : false;
				$('#unli-terms').prop('checked',c);
				togCredLine(d.jdclt);
				
			}
		});		
		
		}	
		mainDialog(1200,360,t,inForm,exe);
		mainDialogB('Update','Close',f,f2);		
	}
} else {
var l = function() {
$( "#bday" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true,
	changeMonth: true,
	changeYear: true,
	yearRange: '1950:2010'
});

$.ajax({
	url: 'customer.php?p=last_customer_id',
	type: 'get',
	success: function(data, status) {
		$('#mnumber').val(data);
	}
});

}
mainDialog(1200,360,t,inForm,l);
mainDialogB('Add','Close',f);
}

}

function confirmCustomer(id,src) {

var m = (src == 1) ? 'Add this new dealer?' : 'Update dealer\'s info?';
var f = function() { customerForm(id,src); };
confirmation(300,200,m,f);

}

function customerForm(id,src) {

var mnumber = $.trim($('#mnumber').val());
var climit = $.trim($('#climit').val());
var fname = $.trim($('#fname').val());
var lname = $.trim($('#lname').val());
var mname = $.trim($('#mname').val());
var add = $.trim($('#address').val());
var con = $.trim($('#contact').val());
var age = $.trim($('#age').val());
var bday = $('#bday').val();
var job = $.trim($('#job').val());
var cstat = $('#cstatus').val();
var spouse = $.trim($('#spouse').val());
var rec = $.trim($('#recruiter').val());
var discountt = $('#discountt').val();
var creditt = $('#selCredit').val();
var cbranch = $('#cbranch').val();
var resetcl = ($('#reset-cl').prop('checked')) ? 1 : 0;
var unlit = ($('#unli-terms').prop('checked')) ? 1 : 0;
var customer_category = $('#customer-category').val();

switch (src) {

case 1:

$.ajax({
	url: 'customer.php?p=add',
	type: 'post',
	data: {pmnumber: mnumber, pclimit: climit, pfname: fname, plname: lname, pmname: mname, padd: add, pcon: con, page: age, pbday: bday, pjob: job, pcstat: cstat, pspouse: spouse, prec: rec, pdiscountt: discountt, pcreditt: creditt, pcbranch: cbranch, punlit: unlit, pcustomer_category: customer_category},
	success: function(data, status) {
	closeMainDialog();
	customer(1);
	var f = function() { content(3,2,rCustomerF()); };
	notify(300,200,data,f);
	}
});

break;

case 2:

var addcl = $('#add-cl').val();

$.ajax({
	url: 'customer.php?p=update&cid=' + id,
	type: 'post',
	data: {pmnumber: mnumber, pclimit: climit, pfname: fname, plname: lname, pmname: mname, padd: add, pcon: con, page: age, pbday: bday, pjob: job, pcstat: cstat, pspouse: spouse, prec: rec, pdiscountt: discountt, pcreditt: creditt, pcbranch: cbranch, paddcl: addcl, presetcl: resetcl, punlit: unlit, pcustomer_category: customer_category},
	success: function(data, status) {
	closeMainDialog();
	var f = function() { content(3,2,rCustomerF()); }
	notify(350,200,data,f);
	}
});
break;

}

}

function confirmCustomerDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete customer(s)?';
		var f = function() { deleteCustomer(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(420,220,m,f,f2);
	}

}

function deleteCustomer(id) {

	$.ajax({
		url: 'customer.php?p=delete',
		type: 'post',
		data: {cid: id},
		success: function(data, status) {
			var f = function() { content(3,2); }
			notify(300,200,data,f);
		}
	});

}

function filterCustomer() {

var par = '';

var fbranch = $('#fbranch').val();
var fmno = $('#fmno').val();
var ffname = $('#ffname').val();
var fadd = $('#fadd').val();
var lcl = ($('#lowcl').prop('checked')) ? 1 : 0;
var frname = $('#frname').val();

par = '&fbranch=' + fbranch + '&fmno=' + fmno + '&ffname=' + ffname + '&fadd=' + fadd + '&lcl=' + lcl + '&frname=' + frname;

content(3,0,par);

}

function rCustomerF() {

var par = '';

var fbranch = $('#fbranch').val();
var fmno = $('#fmno').val();
var ffname = $('#ffname').val();
var fadd = $('#fadd').val();
var lcl = ($('#lowcl').prop('checked')) ? 1 : 0;
var frname = $('#frname').val();

par = '&fbranch=' + fbranch + '&fmno=' + fmno + '&ffname=' + ffname + '&fadd=' + fadd + '&lcl=' + lcl + '&frname=' + frname;

return par;

}

function togCredLine() {

var o = $('#selCredit').val();
var args = togCredLine.arguments;
if (args.length > 0) o = args[0];

switch (parseInt(o)) {

case 0:
$('#climit').val('1500');
$('#climit').prop('disabled',true);
break;

case 1:
if (args.length == 0) $('#climit').val('');
$('#climit').prop('disabled',false);
break;

case 2:
$('#climit').val('0');
$('#climit').prop('disabled',true);
break;

}

}

function togAddCL() {

var chk = $('#addcl').prop('checked');
$('#add-cl').prop('disabled',!chk);
if (!chk) $('#add-cl').val(0);

}

function togResetCL() {

var chk = $('#reset-cl').prop('checked');
$('#addcl').prop('disabled',chk);

}

function unliT() {
	var c = $('#unli-terms').prop('checked');
	$('#unli-terms').prop('checked',!c);
}