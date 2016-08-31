function transaction(src) {

var t = (src == 1) ? 'New Transaction' : 'Edit Transaction';
var inForm = 'forms/transaction.php?src=1';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmTransaction(id,src);
};

var exe = function() {

$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#tdn').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
		$('#hdid').val(item.id); $('#trec').val(item.dr); $('#tclimit').val(item.dcl); $('#htclimit').val(item.dcl);
		$('#hdt').val(item.ddt); $('#hclt').val(item.dclt);
		if ($('#hdid').val() != 0) enAddT();	
		}});				 
		}
});

$.ajax({
type: 'json',
url: 'transaction.php?p=list_products',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#tpcode').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
			$('#hsupd').val(item.jsd);
			$('#hsupid').val(item.jsid);
			$('#hitem').val(item.jpd);
			$('#tpcode').val(item.jpc);
			$('#tpname').val(item.jpn);
			$('#tpsize').val(item.jps);
			$('#tprice').val(item.jpp);

			var dt = $('#hdt').val();
			var supid = $('#hsupid').val();			
			$.ajax({ // get discount according to dealer's discount type - special case if avon
				url: 'transaction.php?p=discount_type&dt=' + dt + '&supid=' + supid,
				dataType: 'json',
				success: function(data, status) {
				var d = data.dtype[0];

				$('#hdis').val(d.jds);
				
				}
			});
		 }});
		 }
});

$('#adds').button();
$('#adds').click(function() { addTI(1); });
$('#terms').prop('checked',true);

}

if (src == 2) {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		var f = function() { uncheckMulti('frmContent'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
	inForm = 'forms/transaction.php?src=2';
	
	id = getCheckedId('frmContent');
	
	var f = function() {
		uncheckSelected(id);
	};
	
	var exe = function() {

		$('#adds').button();
		$('#adds').click(function() { });
		
		$('#tdn').prop('disabled',true);
		
		$.ajax({
			dataType: 'json',
			url: 'transaction.php?p=view&tid=' + id,
			success: function(data, status) {
			
			var d = data.edittransaction[0];
			$('#trano').val(d.jtn);
			$('#tdate').val(d.jtd);
			$('#ddate').val(d.jtdd);
			$('#tdn').val(d.jtdn);
			$('#trec').val(d.jtdr);
			$('#tclimit').val(d.jtcl);
			$('#tra-add-dis > span').html(d.jtcd);
			if (parseInt(d.jtpt) == 0) { $('#terms').prop('checked',true); }
			if (parseInt(d.jtpt) == 1) { $('#cash').prop('checked',true); }
			$('#terms').prop('disabled',true);
			$('#cash').prop('disabled',true);
			
			var inRow;
			
			$.each(data.traitems, function(i,d){
			
			inRow  = '<tr id="tra-' + d.jtin + '">';
			inRow += '<td class="tsup-' + d.jtin + '">' + d.jtis + '</td>'; // company
			inRow += '<td><input type="text" class="tquantity-' + d.jtin + '" value="' + d.jtiq + '" onfocus="this.blur();" /></td>'; // quantity
			inRow += '<td class="titem-' + d.jtin + '">' + d.jtii + '</td>'; // item name/description/size/variety
			inRow += '<td class="tprice-' + d.jtin + '">' + d.jtip + '</td>'; // unit price
			inRow += '<td class="tgross-' + d.jtin + '">' + roundToTwo(parseFloat(d.jtip)*parseInt(d.jtiq)) + '</td>'; // gross amount
			inRow += '<td class="tdiscount-' + d.jtin + '">' + d.jtid + '</td>'; // discount
			var ld = ( (parseFloat(d.jtip)) - (parseFloat(d.jtip) * (parseInt(d.jtid)/100)) ) * d.jtiq;
			var lvad = ld/parseFloat(d.jtvat)*parseFloat(d.jtadd/100);
			inRow += '<td class="tamt-' + d.jtin + '">' + roundToTwo(ld) + '</td>'; // net price
			inRow += '<td class="tvat-' + d.jtin + '">' + d.jtvat + '</td>'; // vat
			inRow += '<td class="tadd-' + d.jtin + '">' + d.jtadd + '</td>'; // add.dis
			inRow += '<td class="tnsale-' + d.jtin + '">' + roundToTwo(ld - lvad) + '</td>'; // net sale			
			inRow += '<td align="center"><a href="javascript: returnDItem(' + d.jtin + ',\'' + d.jtrano + '\',\'' + d.jtpc + '\',\'' + d.jtpn + '\',\'' + d.jtps + '\',\'' + d.jtis + '\',' + d.jtisid + ');" class="tooltip-min"><img src="images/return.png" /><span>Return product</span></a></td>';
			inRow += '<td align="center">' + d.jtret + '</td>';			
			inRow += '</tr>';
			
			$(inRow).appendTo('#tab-transaction-item tbody');
			var h = $('#frmTransactionItem').height();
			if (h >= 199) $('#frmTransactionItem').addClass('fixh');			
			totalTAmt();
			
			});
			}
		});
	}
	mainDialog(1200,520,t,inForm,exe);
	mainDialogB('Close',f);
	}
} else {
	mainDialog(1200,520,t,inForm,exe);
	mainDialogB('Add','Close',f);
}

}

function confirmTransaction(id,src) {

var m = 'Add this transaction?';
var f = function() { transactionForm(id,src); };
confirmation(300,200,m,f);

}

function transactionForm(id,src) {

var nr = $("#tab-transaction-item tbody tr").size();

if (parseInt(nr) == 0) {
	notify(300,200,'Plase add product(s).');
	return;
}

var did = $('#hdid').val();
var iscash = ($('#cash').prop('checked')) ? 1 : 0;
var tclimit = $('#tclimit').val();
tclimit = parseFloat(tclimit);
var trai = '';
var cd = $('#tra-add-dis > span').html();

switch(src) {

case 1:

$('#tab-transaction-item tbody').children('tr').each(function() {

var ti = this.id.split("-");
var tsup = $('.htsup-' + ti[1]).val();
var tpq = $('.tquantity-' + ti[1]).val();
var tpc = $('.tpcode-' + ti[1]).val();
var tpn = $('.tpname-' + ti[1]).val();
var tps = $('.tpsize-' + ti[1]).val();
var tpp = $('.tprice-' + ti[1]).html();
var tpd = $('.tdiscount-' + ti[1]).html();
var tvat = $('.tvat-' + ti[1]).html();
var tadd = $('.tadd-' + ti[1]).html();

trai += tsup + ',' + tpq + ',' + tpc + ',' + tpn + ',' + tps + ',' + tpp + ',' + tpd + ',' + tvat + ',' + tadd + '|';

});

$.ajax({
	url: 'transaction.php?p=add',
	type: 'post',
	data: {pdid: did, piscash: iscash, ptclimit: tclimit, pcd: cd, ptrai: trai, pnr: nr},
	success: function(data, status) {
	var pt = $('#cash').prop('checked');	
	clearForm('frmModule');
	closeMainDialog();
	content(4,2);
	window.open('reports/transaction.php?tid=' + data, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');	
		if (pt) {
			cashPayment(data);	
		} else {
			notify(400,200,'Please go to PAYMENTS tab to process payment.');
		}
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
		content(4,2);
	}
});	
	
}

function printTran(id) {
	window.open('reports/transaction.php?tid=' + id, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');
	uncheckSelected(id);
}

function avonDiscount() {

var br = 0;

var pc = $.trim($('#tpcode').val());
var avonc = pc.split("-");
var supid = $('#hsupid').val();	
var vat = 1;
var addd = 0;

// check TRA items according avon category
if (parseInt(supid) == 2) {
// added items

$('#tab-transaction-item tbody').children('tr').each(function() {
var trai = this.id.split("-");
var tp = $('.tpcode-' + trai[1]).val();
var pf = tp.split("-");
	if (pf[0] == avonc[0]) {
		var tip = $('.tprice-' + trai[1]).html();
		var tid = $('.tdiscount-' + trai[1]).html();
		var tiq = $('.tquantity-' + trai[1]).val();
		var tvat = $('.tvat-' + trai[1]).html();
		var tadd = $('.tadd-' + trai[1]).html();		
		var ld = (parseFloat(tip) - (parseFloat(tip)*(parseInt(tid)/100))) * parseInt(tiq);
		var lvad = (ld/parseFloat(tvat)*(parseInt(tadd)/100));
		br = br + roundToTwo(ld - lvad);
	}
});

// item being added
var sp = $.trim($('#tprice').val());
var ds = $('#hdis').val();
var sq = $.trim($('#tquantity').val());

br = br + ((parseFloat(sp) - (parseFloat(sp)*(parseInt(ds)/100))) * sq);

// for avon additional discount
var ddid = $('#hdid').val();
$.ajax({ // get avon additional discount
	url: 'transaction.php?p=avon_discount&acat=' + avonc[0] + '&ddid=' + ddid + '&br=' + br,
	dataType: 'json',
	async: false,
	success: function(data, status) {
	var d = data.adis[0];
		vat = d.jvat;
		addd = d.jads;
	}
});
}
//

return vat + ',' + addd;

}

function addTI(src) {

var avond = avonDiscount().split(",");
var vat = parseFloat(avond[0]);
var addd = parseInt(avond[1]);

var sp = $.trim($('#tprice').val());
var sq = $.trim($('#tquantity').val());
var ds = $('#hdis').val();
var np = ((parseFloat(sp)*sq) -  ((parseFloat(sp)*sq) * parseFloat(parseInt(ds)/100))); // net
var nnp = np - roundToTwo(np/vat*(addd/100));

// check credit limit
var tclimit = $('#htclimit').val();
var tratotal = $('#tra-total > span').html();

var chkc = $('#cash').prop('checked');
var chkcl = $('#hclt').val();

if ( ((parseFloat(tratotal) + nnp) > parseFloat(tclimit)) && (parseInt(chkcl) != 2) && (!chkc) ) {
	notify(380,200,'Cannot add this item. Credit limit reached.');
} else {

var tc = $('#htc').val();
var sd = $('#hsupd').val();
var hs = $('#hsupid').val();
var pc = $.trim($('#tpcode').val());
var pn = $.trim($('#tpname').val());
var ps = $.trim($('#tpsize').val());
$('.validate').html('');	

var item = $('#hitem').val();

if ((pc != '') && (pn != '') && (ps != '') && (sp != '') && (sq != '')) {

	var h = $('#frmTransactionItem').height();
	if (h >= 199) $('#frmTransactionItem').addClass('fixh');	

	var inRow;

	tc = parseInt(tc) + 1;

	inRow  = '<tr id="tra-' + tc + '">';
	inRow += '<td class="tsup-' + tc + '">' + sd + '<input type="hidden" class="htsup-' + tc + '" value="' + hs + '" /></td>'; // company
	inRow += '<td><input type="text" class="tquantity-' + tc + '" value="' + sq + '" onchange="cTAmt(' + tc + ');" /></td>'; // quantity
	inRow += '<td class="titem-' + tc + '">' + item;
	inRow += '<input type="hidden" class="tpcode-' + tc + '" value="' + pc + '"/>';
	inRow += '<input type="hidden" class="tpname-' + tc + '" value="' + pn + '"/>';
	inRow += '<input type="hidden" class="tpsize-' + tc + '" value="' + ps + '"/>';	
	inRow += '</td>'; // item name/description/size/variety
	inRow += '<td class="tprice-' + tc + '">' + sp + '</td>'; // unit price
	inRow += '<td clsss="tgross-' + tc +'">' + roundToTwo(parseFloat(sp)*parseInt(sq)) + '</td>'; // gross amount
	inRow += '<td class="tdiscount-' + tc + '">' + parseInt(ds) + '</td>'; // discount
	inRow += '<td class="tamt-' + tc + '">' + roundToTwo(np) + '</td>'; // net price
	inRow += '<td class="tvat-' + tc + '">' + vat + '</td>'; // vat
	inRow += '<td class="tadd-' + tc + '">' + addd + '</td>'; // add.dis
	inRow += '<td class="tnsale-' + tc + '">' + roundToTwo(nnp) + '</td>'; // net sale
	inRow += '<td align="center">' + '<a href="javascript: delTI(' + tc + ',' + src + ');"><img src="images/delete.png" /></a>' + '</td>';	
	inRow += '</tr>';

	$(inRow).appendTo('#tab-transaction-item tbody');
	$('#htc').val(tc);	
	
} else {
	$('.validate').html('Please fill up; product code, product name, price, and quantity.');
}	

totalTAmt();
updateCL();

} // credit limit OK
	
}

function delTI(item,src) {
	var htc = $('#htc').val();
	$('#tra-' + item).remove();
	if (src == 1) {
		if (parseInt(item) == parseInt(htc)) $('#htc').val(parseInt(item) -1);
	}
totalTAmt();
updateCL();
}

function cTAmt(id) {

var amt = 0;
var gr = 0;
var p = $.trim($('.tprice-' + id).html());
var q = $.trim($('.tquantity-' + id).val());
var d = $('.tdiscount-' + id).html();
var v = $('.tvat-' + id).html();
var ad = $('.tadd-' + id).html(); 
var ld = ( (parseFloat(p)) - (parseFloat(p) * (parseInt(d)/100)) ) * parseInt(q);
var lvad = ld/parseFloat(v)*parseFloat(ad/100);
amt = roundToTwo(ld - lvad);
gr = parseFloat(p)*parseInt(q);
$('.tgross-' + id).val(gr);
$('.tamt-' + id).html(ld);
$('.tnsale-' + id).html(amt);

totalTAmt();

}

function totalTAmt() {

var tnet = 0;
var tnamt = 0;
var tamt = 0;
var adis = $('#tra-add-dis > span').html();

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tnsale-' + trai[1]).html());

tnamt = tnamt + parseFloat(tnet);

});

$('#tra-total-amt > span').html(roundToTwo(tnamt));
tamt = tnamt - (tnamt * (parseInt(adis)/100));
$('#tra-total > span').html(roundToTwo(tamt));

}

function updateCL() {

var chkc = $('#cash').prop('checked');
var chkcl = $('#hclt').val();
var tclimit = $('#htclimit').val();
var tratotal = $('#tra-total > span').html();
var ucl = parseFloat(tclimit) - parseFloat(tratotal);

if ((!chkc) && (parseInt(chkcl) != 2)) $('#tclimit').val(roundToTwo(ucl));

if (chkc) {
	cl = $('#tclimit').val();
	hcl = $('#htclimit').val();
	if (cl != hcl) $('#tclimit').val(hcl);
}

}

function enAddT() { 

$('#tpcode').prop('disabled',false);
$('#tpname').prop('disabled',false);
$('#tpsize').prop('disabled',false);
$('#tprice').prop('disabled',false);
$('#tquantity').prop('disabled',false);

}

function ttype(id) {

$('#terms').prop('checked',false);
$('#cash').prop('checked',false);
$('#' + id).prop('checked',true);

$('#tra-add-dis > span').html('0');
var supid = $('#hsupid').val();
if (id == 'cash' && parseInt(supid) != 2) $('#tra-add-dis > span').html('5');
totalTAmt();
updateCL();

}

function filterTransaction() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fdate = $.trim($('#fdate').val());
var ftrano = $.trim($('#ftrano').val());
var fcustomer = $.trim($('#fcustomer').val());
var und = ($('#unpaid-due').prop('checked')) ? 1 : 0;

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fdate=' + fdate + '&ftrano=' + ftrano + '&fcustomer=' + fcustomer + '&und=' + und;

content(4,0,par);

}

function rTranF() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fdate = $.trim($('#fdate').val());
var ftrano = $.trim($('#ftrano').val());
var fcustomer = $.trim($('#fcustomer').val());
var und = ($('#unpaid-due').prop('checked')) ? 1 : 0;

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fdate=' + fdate + '&ftrano=' + ftrano + '&fcustomer=' + fcustomer + '&und=' + und;
return par;

}

function confirmTransactionDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		notify(450,200,'One Tra.No at a time is only allowed to be deleted.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete this transaction?';
		var f = function() { deleteTransaction(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(420,220,m,f,f2);
	}

}

function deleteTransaction(id) {

	$.ajax({
		url: 'transaction.php?p=delete',
		type: 'post',
		data: {tid: id},
		success: function(data, status) {
			var f = function() { content(4,2); }
			notify(300,200,data,f);
		}
	});

}

function dealerReturns() {

var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('dealer-return.php', '', params);

}

function returnDItem(tino,pt,pc,pn,ps,supd,sup) {

var t = 'Return Product';
var inForm = 'forms/dealer-return.php?pt=' + pt + '&pc=' + pc + '&pn=' + pn + '&ps=' + ps + '&supd=' + supd;
var f = function() {
if (validateForm('subModule')) confirmRProduct(tino,pt,pc,pn,ps,sup);
};
var exe = function() {

};

subDialog(400,400,t,inForm,exe);
subDialogB('Ok','Cancel',f);

}

function confirmRProduct(tino,pt,pc,pn,ps,sup) {

var m = 'Return this stock?';
var f = function() { dealerRForm(tino,pt,pc,pn,ps,sup); };
confirmation(300,200,m,f);

}

function dealerRForm(tino,pt,pc,pn,ps,sup) {

var dretqty = $.trim($('#dretqty').val());
var dretnote = $.trim($('#dretnote').val());

$.ajax({
	url: 'dealer-return-ajax.php?p=add',
	type: 'post',
	data: {ptino: tino, ptsup: sup, pdrettrano: pt, pdretcode: pc, pdretname: pn, pdretsize: ps, pdretqty: dretqty, pdretnote: dretnote},
	success: function(data, status) {	
	closeSubDialog();
	closeMainDialog();
	transaction(2);
	notify(300,200,data);
	}
});

}

function walkIn() {

window.open('walk-in-cash.php', '', 'width=1024px, scrollbars=yes, toolbar=no, menubar=yes');

}