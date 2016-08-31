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

case 5: // payments
$.ajax({
	url: 'payment-ajax.php?p=contents' + page + par,
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

function payment(src) {

var t = (src == 1) ? 'New Payment' : 'Add Payment';
var inForm = 'forms/payment.php';
var id = 0;
var f = function() {
	
	if ($('#hiscash').val() == 1) {
		notify(300,150,'This is a Cash Transaction.');
		return;
	}
	
	$('.validate').html('');	
	var chkd = $('#hdid').val();
	var chkp = $("#receipts-items tbody tr").size();
	if (chkd ==0) {
		$('.validate').html('Please add dealer.');
	} else if (chkp == 0) {
		$('.validate').html('Please add transaction to payment.');	
	} else {
		confirmPayment(id,src);
	}
};

var exe = function() {

$('#span-pay').button();
$('#span-pay').click(function() { spanPayment(); });

$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers&ftb=1',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#tdn').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
		$('#hdid').val(item.id); $('#prec').val(item.dr); $('#piclimit').val(item.dicl);
		$('#pclimit').val(item.dcl); $('#hpclimit').val(item.dcl); $('#hclt').val(item.dclt);
		showTermsT();
		$("#receipts-items tbody").html('');
		}});
		}
});

//$('#addr').button();
highl();

};

if (src == 2) {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		var f = function() { uncheckMulti('frmContent'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
	id = getCheckedId('frmContent');
	
	exe = function() {
	
	$('#span-pay').button();
	$('#span-pay').click(function() { spanPayment(); });	
	
	$('#tdn').focus(function() { $('#tdn').blur(); } );
	
		$.ajax({
			dataType: 'json',
			url: 'payment-ajax.php?p=edit&rid=' + id,
			success: function(data, status) {
			
			var d = data.editpayment[0];
			$('#recno').val(d.jrno);
			$('#recdate').val(d.jrd);
			$('#tdn').val(d.jrdn);
			$('#rca').val(d.jrca);			
			$('#hdid').val(d.jrdid);
			$('#prec').val(d.jrr);
			$('#piclimit').val(d.jrcl);
			$('#pclimit').val(d.jrcla);
			$('#hpclimit').val(d.jrcla);			
			$('#hiscash').val(d.jrc);
			$('#hclt').val(d.jrclt);			
			
			var inRow;
			var rcl = 0;
			
			$.each(data.payments, function(i,d){
			
		    inRow  = '<tr id="pay-' + d.jrpno + '" class="payment">';
			inRow += '<td>' + d.jrpd + '</td>';
			inRow += '<td id="tra-' + d.jrpno + '">' + d.jrptra + '</td>';
			inRow += '<td>';
			inRow += '<input id="payamt-' + d.jrpno + '" type="text" value="' + d.jrpa + '" disabled />';
			inRow += '<input type="hidden" id="hap-' + d.jrpno + '" value="' + d.jrpa + '" />';			
			inRow += '</td>';
			inRow += '<td>';
			inRow += '<select id="mop-' + d.jrpno + '" style="width: 100px;" disabled >';
			inRow += '<option value="0">Advance</option>';
			inRow += '<option value="1" selected="selected">Full</option>';	
			inRow += '</select>';
			inRow += '</td>';
			inRow += '<td>' + d.jrpca + '</td>';
			inRow += '<td>&nbsp;</td>';			
			inRow += '</tr>';			
			
			$(inRow).appendTo('#receipts-items tbody');
			$('#mop-' + d.jrpno).val(d.jrpmop);
			rcl = parseFloat($('#returnedCL').val()) + parseFloat(d.jrpa);
			$('#returnedCL').val(rcl); // cached amount to be subtracted from updated CL
			totalRAmt();
			
			});
			}
		});

		$.ajax({
			type: 'post',
			url: 'payment-ajax.php?p=return_receipt_did',
			data: {prid: id},
			success: function(data, status) {
				showTermsTE(data);
			}
		});		
		
		cachePaidP();
		highl();
		
	};
	
	var f2 = function() {
		uncheckSelected(id);
	};		
	
	mainDialog(850,610,t,inForm,exe);
	mainDialogB('Update','Close',f,f2);	
	}
} else {
mainDialog(850,610,t,inForm,exe);
mainDialogB('Add','Close',f);
}

}

function confirmPayment(id,src) {

var m = 'Add this payment?';
var f = function() { paymentForm(id,src); };
confirmation(300,200,m,f);

}

function paymentForm(id,src) {

$.blockUI({ message: $('#processModal') });

var nr = $("#receipts-items tbody tr").size();
var payi = '';
var did = $('#hdid').val();
var climit = $('#pclimit').val();
var clt = $('#hclt').val();

switch (src) {

case 1:
$('#receipts-items tbody').children('tr').each(function() {

var ri = this.id.split("-");
var tra = $('#tra-' + ri[1]).html();
var pay = $('#payamt-' + ri[1]).val();
var mop = $('#mop-' + ri[1]).val();

payi += tra + ',' + pay + ',' + mop + '|';

});

$.ajax({
	url: 'payment-ajax.php?p=new',
	type: 'post',
	data: {pdid: did, pclimit: climit, ppayi: payi, pclt: clt, pnr: nr},
	success: function(data, status) {
	$.unblockUI();		
	clearForm('frmModule'); closeMainDialog(); payment(1);
	var f = function() { content(5,2); }
	notify(250,150,data,f);
	}
});
break;

case 2:
$('#receipts-items tbody').children('tr').each(function() {

if (this.className == 'payment') {
nr = nr - 1;
return true;
}
var ri = this.id.split("-");
var tra = $('#tra-' + ri[1]).html();
var pay = $('#payamt-' + ri[1]).val();
var mop = $('#mop-' + ri[1]).val();

payi += tra + ',' + pay + ',' + mop + '|';

});

$.ajax({
	url: 'payment-ajax.php?p=add&rid=' + id,
	type: 'post',
	data: {pdid: did, pclimit: climit, ppayi: payi, pclt: clt, pnr: nr},
	success: function(data, status) {
	$.unblockUI();		
	clearForm('frmModule'); closeMainDialog();
	var f = function() { content(5,2); }
	notify(250,150,data,f);
	}
});
break;

}

}

function getCashier() {

var ca = '';

$.ajax({
	url: 'payment-ajax.php?p=cashier_fullname',
	type: 'get',	
	async: false,
	success: function(data, status) {
		ca = data;
	}
});

return ca;

}

function addPayment(d,no,amt,pen) {

var ca = getCashier();

var inRow  = '<tr id="pay-' + no + '">';
	inRow += '<td>' + d + '</td>';
	inRow += '<td id="tra-' + no + '">' + no + '</td>';
	camt = parseFloat(amt) + parseFloat(pen);
	inRow += '<td>';
	inRow += '<input id="payamt-' + no + '" type="text" value="' + camt + '" onchange="totalRAmt(); updatePCL(' + no + ');" disabled />';
	inRow += '<input type="hidden" id="hpayamt-' + no + '" value="' + camt + '" />';
	inRow += '<input type="hidden" id="hap-' + no + '" value="' + amt + '" />';	
	inRow += '</td>';
	inRow += '<td>';
	inRow += '<select id="mop-' + no + '" style="width: 100px;"  onchange="cMop(this.value,\'' + no + '\');">';
	inRow += '<option value="0">Advance</option>';
	inRow += '<option value="1" selected="selected">Full</option>';	
	inRow += '</select>';
	inRow += '</td>';
	inRow += '<td>' + ca + '</td>';
	inRow += '<td><a href="javascript: remTraR(\'' + no + '\')" class="tooltip-min"><img src="images/delete.png" /><span>Remove from payment</span></a></td>';
	inRow += '</tr>';

// check if transaction is already added
var nr = $("#receipts-items tbody tr").size();
var add = true;
if (nr>0) {
$('#receipts-items tbody').children('tr').each(function() {

var ri = this.id.split("-");
if (no == ri[1]) add = false;

});
}
//	
if (add) {
$(inRow).appendTo('#receipts-items tbody');
totalRAmt();
updatePCL(no);
} else {
notify(300,150,'Transaction is already added.');
}

}

function cMop(m,no) {
	var c = false;
	if (parseInt(m) == 1) c = true;
	$('#payamt-' + no).prop('disabled',c);
	if (c) { // in case user switch back to full payment
	var amt = $('#hpayamt-' + no).val();
	$('#payamt-' + no).val(amt);
	}
totalRAmt();
updatePCL(no);
}

function remTraR(no) {

var amt = $('#payamt-' + no).val();
$('#pay-' + no).remove();
totalRAmt();
updatePCL(no);

}

function totalRAmt() {

var tamt = 0;

$('#receipts-items tbody').children('tr').each(function() {

var ri = this.id.split("-");
var amt = $('#payamt-' + ri[1]).val();
tamt = tamt + parseFloat(amt);

});

var pp = $('#returnedCL').val();
tamt = tamt - parseFloat(pp);

$('#receipt-total-amt > span').html(roundToTwo(tamt));
var rtotal = $('#receipt-total-amt > span').html();
if (parseFloat(rtotal) == 0) $('#receipt-total-amt > span').html(parseFloat(pp));

}

function updatePCL(no) {

var icl = 0;
var total = 0;
var amt = 0;
var hamt = 0;
var aamt = 0;
var climit = $('#hpclimit').val();
var rcl = $('#returnedCL').val();

var nr = $("#receipts-items tbody tr").size();
if (nr>0) {
$('#receipts-items tbody').children('tr').each(function() {

var ri = this.id.split("-");
amt = $('#payamt-' + ri[1]).val();
hamt = $('#hap-' + ri[1]).val();
aamt = amt;
if (parseFloat(amt) > parseFloat(hamt)) aamt = hamt; // don't include penalty

total = parseFloat(total) + parseFloat(aamt);

});
}

icl = (roundToTwo(parseFloat(climit)) + roundToTwo(parseFloat(total)))  - roundToTwo(parseFloat(rcl));
var chkcl = $('#hclt').val();
if (parseInt(chkcl) != 2) $('#pclimit').val(roundToTwo(icl));

}

function showTermsT() {
var id = $('#hdid').val();
$('#tab-terms-transactions tbody').html('Processing...');

$.ajax({
	type: 'post',
	url: 'payment-ajax.php?p=show_terms_transactions',
	data: {pdid: id},
	success: function(data, status) {
		$('#tab-terms-transactions tbody').html(data);
	}
});

}

function showTermsTE(id) {

$.ajax({
	type: 'post',
	url: 'payment-ajax.php?p=show_terms_transactions',
	data: {pdid: id},
	success: function(data, status) {
		$('#tab-terms-transactions tbody').html(data);
	}
});

}

function printReceipt(id) {
	window.open('reports/receipt.php?rid=' + id, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');
	uncheckSelected(id);	
}

function filterReceipt() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var frno = $.trim($('#frno').val());
var ftnno = $.trim($('#ftnno').val());
var fcustomer = $.trim($('#fcustomer').val());

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fs=' + fs + '&fe=' + fe + '&frno=' + frno + '&ftnno=' + ftnno +'&fcustomer=' + fcustomer;

content(5,0,par);

}

function rReceiptF() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var frno = $.trim($('#frno').val());
var ftnno = $.trim($('#ftnno').val());
var fcustomer = $.trim($('#fcustomer').val());

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fs=' + fs + '&fe=' + fe + '&frno=' + frno + '&ftnno=' + ftnno +'&fcustomer=' + fcustomer;

return par;

}

function confirmReceiptDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		notify(450,200,'One Receipt at a time is only allowed to be deleted.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete this receipt?';
		var f = function() { deleteReceipt(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(380,180,m,f,f2);
	}

}

function deleteReceipt(id) {

	$.ajax({
		url: 'payment-ajax.php?p=delete',
		type: 'post',
		data: {rid: id},
		success: function(data, status) {
			var f = function() { content(5,2); }
			notify(300,200,data,f);
		}
	});

}

function spanPayment() {

$('#receipts-items tbody').html('');

var nr = $('#tab-terms-transactions tbody tr').size();
var sppay = $('#span-amt').val();

if (parseInt(nr) == 0) {
	$('.validate').html('Please add dealer.');
	return true;
}

if ( (sppay == '') || (sppay == '0') ) {
	$('.validate').html('Please enter amount.');
	return true;
}

nr = $('#tab-terms-transactions tbody tr').size();
if (nr>0) {
var ahref = '';
var lhref = '';
var ti = '';
var rown = 0;
var ar_tra = '';
var totalpayment = 0;
$('#tab-terms-transactions tbody').children('tr').each(function() {

ti = this.id.split("-");
rowc = this.className.split("-");
ahref = $('.trano-' + ti[1]).attr('href');

var spamt = $('.spamt-' + ti[1]).val();
var sppen = $('.sppen-' + ti[1]).val();
var traamt = parseFloat(spamt) + parseFloat(sppen);
totalpayment = totalpayment + traamt;
ar_tra = ar_tra + ti[1] + ',';

if (totalpayment < parseFloat(sppay)) {
	setTimeout(ahref,1000);
	last_tra_added = ti[1]; // last trano added
	rowno = parseInt(rowc[1]) + 1; // item index
}

});

var rtotal = 0;
var adj_pay = 0;

setTimeout(function() {

rtotal = $('#receipt-total-amt > span').html();
lta_id = $('.rowno-' + rowno).attr('id');
var nti = lta_id.split("-");
lhref = $('.trano-' + nti[1]).attr('href');
setTimeout(lhref,1000);
adj_pay = roundToTwo(parseFloat(sppay) - parseFloat(rtotal));

setTimeout(function() {
$('#payamt-' + nti[1]).val(adj_pay);
$('#hpayamt-' + nti[1]).val(adj_pay);
$('#hap-' + nti[1]).val(adj_pay);
$('#mop-' + nti[1]).val(0);
totalRAmt();
updatePCL(nti[1]);

var loop_tra = ar_tra.split(",");
for (var i = 0; i < loop_tra.length-1; ++i) {
$('#mop-' + loop_tra[i]).prop('disabled',true);
}

},2000);

},1000);

}

}