function addClickProduct(id,jsd,jsid,jpd,jpc,jpn,jps,jpp,jpst,jsiid) {

		$('#hsino').val(id); // to track what tra no is item sold to
		$('#hsupd').val(jsd);
		$('#hsupid').val(jsid);
		$('#hitem').val(jpd);
		$('#tpcode').val(jpc);
		$('#tpname').val(jpn);
		$('#tpsize').val(jps);
		$('#tprice').val(jpp);
		$('#hstock').val(jpst);
		$('#hsiid').val(jsiid);
		
		if ( (parseInt(jsid) == 2) || (parseInt(jsid) == 7) || (parseInt(jsid) == 11) || (parseInt(jsid) == 17) || (parseInt(jsid) == 24) || (parseInt(jsid) == 22) ) $('#tprice').prop('disabled',false); else $('#tprice').prop('disabled',true);
		
	closeMainDialog();
		
}

function suggestProduct(p) {

if (p != '') {

var inRow = ''
$('#tab-product-search-results tbody').html(inRow);

$.ajax({
url: 'transaction.php?p=suggest_products&filp=' + p,
dataType: 'json',
async: false,
success: function(data, status) {	

$.each(data.suggestproducts, function(i,d){

inRow += '<tr onclick="addClickProduct(\'' + d.id + '\',\'' + d.jsd + '\',\'' + d.jsid + '\',\'' + d.jpd + '\',\'' + d.jpc + '\',\'' + d.jpn + '\',\'' + d.jps + '\',\'' + d.jpp + '\',\'' + d.jpst + '\',\'' + d.jsiid + '\');">';
inRow += '<td>' + d.jsdate + '</td>';
inRow += '<td>' + d.jsref + '</td>';
inRow += '<td>' + d.jsd + '</td>';
inRow += '<td>' + d.jpc + '</td>';
inRow += '<td>' + d.jpn + '</td>';
inRow += '<td>' + d.jps + '</td>';
inRow += '<td>' + d.jpp + '</td>';
inRow += '<td>' + d.jpst + '</td>';	
inRow += '</tr>';

});

if (data.suggestproducts.length == 0) inRow = '<tr><td colspan="8">No stocks.</td></tr>';

$('#tab-product-search-results tbody').html(inRow);

},
error: function(xhr, ajaxOptions) {

inRow = '<tr><td colspan="8">No stocks.</td></tr>';
$('#tab-product-search-results tbody').html(inRow);

},
complete: function(jqXHR, textStatus) {

var h = $('#frmProductSearchResults').height();
if (parseInt(h) >= 439) $('#frmProductSearchResults').addClass('fixh');

}
});

}

}

function searchProduct() {

$.blockUI({ message: $('#loadingModal') });	

var t = 'Product Search List';
var inForm = 'forms/search-product.php';
var id = 0;

var exe = function() {

$.ajax({
type: 'json',
url: 'transaction.php?p=list_products',
async: false,
success: function(data, status) {
$.unblockUI();        
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#product-suggest').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
			suggestProduct(item.jpc + ' ' + item.jpn + ' ' + item.jps);
		 }});
		 }
});

};

var f = function() {};

mainDialog(700,600,t,inForm,exe);
mainDialogB('Close',f);

}

function confirmWalkInCash(src) {

var m = 'Add today\'s Walk-in transaction?';
var f = function() { walkInCashForm(src); };
confirmation(300,200,m,f);

}

function walkInCashForm(src) {

var nr = $("#tab-transaction-item tbody tr").size();

if (parseInt(nr) == 0) {
	notify(300,150,'No product(s) added.');
	return;
}

var did = 0;
var iscash = 1;
var tclimit = 0;
var trai = '';
var cd = 0;
var vat = 1;
var ad = 0;

switch(src) {

case 1:

$('#tab-transaction-item tbody').children('tr').each(function() {

var ti = this.id.split("-");
var tsup = $('.htsup-' + ti[1]).val();
var tpq = $('.tquantity-' + ti[1]).val();
var tsino = $('.tsino-' + ti[1]).val();
var tpc = $('.tpcode-' + ti[1]).val();
var tpn = $('.tpname-' + ti[1]).val();
var tps = $('.tpsize-' + ti[1]).val();
var tpp = $('.tprice-' + ti[1]).html();
var tpd = $('.tdiscount-' + ti[1]).html();
var tpb = 0;
var vat = 1;
var ad = 0;

trai += tsup + ',' + tpq + ',' + tpc + ',' + tpn + ',' + tps + ',' + tpp + ',' + tpd + ',' + tsino + ',' + tpb + '|';

});

$.ajax({
	url: 'transaction.php?p=add',
	type: 'post',
	data: {pdid: did, piscash: iscash, ptclimit: tclimit, pcd: cd, tvat: vat, pad: ad, ptrai: trai, pnr: nr},
	success: function(data, status) {	
	$('#tab-transaction-item tbody').html('');
	window.open('reports/transaction.php?tid=' + data, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');
	cashPayment(data);
	$('#tpcode').val('');
	$('#tpname').val('');
	$('#tpsize').val('');
	$('#tpdisc').val('');
	$('#tpquantity').val('');
	notify(320,150,'Walk-in Transaction added you may now close this window.');
	}
});
break;

}

}

function cashPayment(id) {
	
$.ajax({
	type: 'post',
	url: 'transaction.php?p=cash_payment',
	data: {ptid: id},
	success: function(data, status) {

	}
});	
	
}

function chkD(src) {

var ds = $('#tpdisc').val();
if (ds == '') ds = 0;

if (parseInt(ds) > 15) {
var t = 'Discount';
var inForm = 'forms/walk-in-discount.php';
	var f = function() {
		var pass = $('#admin-pass').val();
		var allow = 0;

		$.ajax({
			url: 'walk-in-cash-ajax.php?p=discount',
			type: 'post',
			data: {ppass: pass},
			async: false,
			success: function(data, status) {	
				allow = parseInt(data);
			}
		});
		if (allow == 0) {
		$('.sub-validate').html('Invalid admin password.');
		} else {
		addTI(src);
		closeSubDialog();
		}
	};
subDialog(350,150,t,inForm);
subDialogB('Ok','Cancel',f);
} else {
addTI(src);
}

}

function chkIfItemAdded() {

var isAdded = false;

var pc = $.trim($('#tpcode').val());
var pn = $.trim($('#tpname').val());
var ps = $.trim($('#tpsize').val());
var psiid = $('#hsiid').val();

$('#tab-transaction-item tbody').children('tr').each(function() {

var ti = this.id.split("-");
var cpc = $('.tpcode-' + ti[1]).val();
var cpn = $('.tpname-' + ti[1]).val();
var cps = $('.tpsize-' + ti[1]).val();
var cpsiid = $('.tpsiid-' + ti[1]).val();

if (pc == cpc && pn == cpn && ps == cps && psiid == cpsiid) isAdded = true;

});

return isAdded;

}

function addTI(src) {

/*
if (chkIfItemAdded()) {
	notify(380,150,'Item has been already added. Change quantity anyway.');
	return;
}
*/

var sp = $.trim($('#tprice').val());
var sq = $.trim($('#tquantity').val());
var ds = $('#tpdisc').val();
if (ds == '') ds = 0;

if (ds != 0) {
if (parseInt(ds) < 5) {
	notify(300,150,'Minimum discount is 5%.');
	return;
}
}

// check if quantity > stocks
var stock = $('#hstock').val();
if ((parseInt(stock) -  (parseInt(sq) + itemAddedQty())) < 0) {
	notify(300,150,'Insufficient stock(s) available.');
	return;
}

var np = ((parseFloat(sp)*sq) -  ((parseFloat(sp)*sq) * parseFloat(parseInt(ds)/100))); // net

var tc = $('#htc').val();
var sd = $('#hsupd').val();
var hs = $('#hsupid').val();
var hsino = $('#hsino').val();
var pc = $.trim($('#tpcode').val());
var pn = $.trim($('#tpname').val());
var ps = $.trim($('#tpsize').val());
var psiid = $('#hsiid').val();
$('.validate').html('');	

var item = $('#hitem').val();

if ((pc != '') && (pn != '') && (ps != '') && (sp != '') && (sq != '')) {

	var h = $('#frmTransactionItem').height();
	if (h >= 199) $('#frmTransactionItem').addClass('fixh');	

	var inRow;

	tc = parseInt(tc) + 1;

	inRow  = '<tr id="tra-' + tc + '" data-hsino="' + hsino + '" data-tquantity="' + sq + '">';
	inRow += '<td class="tsup-' + tc + '">' + sd + '<input type="hidden" class="htsup-' + tc + '" value="' + hs + '" /></td>'; // company
	inRow += '<td><input type="text" class="tquantity-' + tc + '" value="' + sq + '" onchange="cTAmt(' + tc + '); itemAddedChangeQty(' + tc + ');" /><input type="hidden" class="hsoh-' + tc + '" value="' + stock + '" /></td>'; // quantity
	inRow += '<td class="titem-' + tc + '">' + item;
	inRow += '<input type="hidden" class="tsino-' + tc + '" value="' + hsino + '"/>';	
	inRow += '<input type="hidden" class="tpcode-' + tc + '" value="' + pc + '"/>';
	inRow += '<input type="hidden" class="tpname-' + tc + '" value="' + pn + '"/>';
	inRow += '<input type="hidden" class="tpsize-' + tc + '" value="' + ps + '"/>';
	inRow += '<input type="hidden" class="tpsiid-' + tc + '" value="' + psiid + '"/>';
	inRow += '</td>'; // item name/description/size/variety
	inRow += '<td class="tprice-' + tc + '">' + sp + '</td>'; // unit price
	inRow += '<td clsss="tgross-' + tc +'">' + roundToTwo(parseFloat(sp)*parseInt(sq)) + '</td>'; // gross amount
	inRow += '<td class="tdiscount-' + tc + '">' + parseInt(ds) + '</td>'; // discount
	inRow += '<td class="tnsale-' + tc + '">' + roundToTwo(np) + '</td>'; // net sale
	inRow += '<td align="center">' + '<a href="javascript: delTI(' + tc + ',' + src + ');"><img src="images/delete.png" /></a>' + '</td>';	
	inRow += '</tr>';

	$(inRow).appendTo('#tab-transaction-item tbody');
	$('#htc').val(tc);	
	totalTAmt();
	totalQty();	
} else {
	$('.validate').html('Please fill up; product code, product name, price, and quantity.');
}	

$('#hsino').val('0'); // to track what tra no is item sold to
$('#hsupd').val('');
$('#hsupid').val('0');
$('#hitem').val('');
$('#tpcode').val('');
$('#tpname').val('');
$('#tpsize').val('');
$('#tprice').val('');
$('#tpdisc').val('0');
$('#hstock').val('0');
$('#hsiid').val('0');
	
}

function delTI(item,src) {
	var htc = $('#htc').val();
	$('#tra-' + item).remove();
	if (src == 1) {
		if (parseInt(item) == parseInt(htc)) $('#htc').val(parseInt(item) -1);
	}
totalTAmt();
totalQty();
}

function totalTAmt() {

var tnet = 0;
var tnamt = 0;
var tamt = 0;

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tnsale-' + trai[1]).html());

tnamt = tnamt + parseFloat(tnet);

});

$('#tra-total > span').html(roundToTwo(tnamt));

}

function cTAmt(id) {

var p = $.trim($('.tprice-' + id).html());
var q = $.trim($('.tquantity-' + id).val());
var d = $('.tdiscount-' + id).html();

// check if quantity > stocks
var stock = $('.hsoh-' + id).val();

if ((parseInt(stock) - parseInt(q)) < 0) {
	$('.tquantity-' + id).val(1);
	notify(300,150,'Insufficient stock(s) available.');
	return;
}

var amt = 0;
var gr = 0;
var v = 1
var ad = 0; 
var ld = ( (parseFloat(p)) - (parseFloat(p) * (parseInt(d)/100)) ) * parseInt(q);
var lvad = ld/parseFloat(v)*parseFloat(ad/100);
amt = roundToTwo(ld - lvad);
gr = parseFloat(p)*parseInt(q);
$('.tgross-' + id).val(gr);
$('.tamt-' + id).html(ld);
$('.tnsale-' + id).html(amt);

totalTAmt();
totalQty();

}

function totalQty() {

var q = 0;
var tq = 0;
$('#tab-transaction-item tbody').children('tr').each(function() {
var trai = this.id.split("-");
q = $.trim($('.tquantity-' + trai[1]).val());
tq = tq + parseInt(q);
});

$('#tra-total-qty > span').html(tq);

}

function itemAddedQty() {
	if ($("#tab-transaction-item tbody tr").length == 0) return 0;
	var quantity = 0;
	var hsino = $('#hsino').val();	
	$('#tab-transaction-item tbody').children('tr').each(function() {
		if (this.dataset.hsino == hsino) {
			quantity = quantity + parseInt(this.dataset.tquantity);
		}
	});
	return quantity;
}

function itemAddedChangeQty(id) {
	$('#tra-'+id)[0].dataset.tquantity = $('.tquantity-'+id).val();
}