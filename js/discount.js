function editDiscount(id) {

var t = 'Edit Discount(s)';
var inForm = 'forms/discount.php';
var f = function() {
if (validateForm('frmModule')) confirmDiscount(id);
};

var exe = function() {

$.ajax({
	url: 'discount.php?p=edit&sid=' + id,
	dataType: 'json',
	success: function(data, status) {
		var d = data.editdiscount[0];
		$('#basicd').val(d.jbd);
		$('#topd').val(d.jtd);
		$('#outd').val(d.jod);
		$('#sped').val(d.jsd);
		$('#sld').val(d.jsld);		
	}
});

}

mainDialog(400,300,t,inForm,exe);
mainDialogB('Ok','Cancel',f);

}

function confirmDiscount(id) {

var m = 'Update Discount(s)?';
var f = function() { discountForm(id); };
confirmation(300,200,m,f);

}

function discountForm(id) {

var basicd = $.trim($('#basicd').val());
var topd = $.trim($('#topd').val());
var outd = $.trim($('#outd').val());
var sped = $.trim($('#sped').val());
var sld = $.trim($('#sld').val());

$.ajax({
	url: 'discount.php?p=update&sid=' + id,
	type: 'post',
	data: {pbasicd: basicd, ptopd: topd, poutd: outd, psped: sped, psld: sld},
	success: function(data, status) {
	closeMainDialog();
	var f = function() { content(7,2); }
	notify(300,200,data,f);	
	}
});

}

function filterDiscount() {

var par = '';

var fsup = $('#fsup').val();

var chksup = $('#fsupplier').val();

if (chksup == '') fsup = 0;

par = '&fsup=' + fsup;

content(7,0,par);

}

function rDiscountF() {

var par = '';

var fsup = $('#fsup').val();

var chksup = $('#fsupplier').val();

if (chksup == '') fsup = 0;

par = '&fsup=' + fsup;

return par;

}