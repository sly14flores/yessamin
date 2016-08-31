function changeCashier(tid) {

var t = 'Change Cashier';
var inForm = 'forms/change-cashier.php';
var exe = function() {
$.ajax({
	url: 'transaction.php?p=get_cashier&ccid=' + tid,
	type: 'get',
	success: function(data, status) {
		$('#tra-cashier').val(parseInt(data));
	}
});
};

var f = function() {

var cid = $('#tra-cashier').val();

$.ajax({
	url: 'transaction.php?p=change_cashier&cid=' + cid,
	type: 'post',
	data: {tcc: tid},
	success: function(data, status) {
		closeMainDialog();
		content(4,2);	
	}
});

};

mainDialog(400,180,t,inForm,exe);
mainDialogB('Update','Cancel',f);

}

function chkDealerTstat(id) {

var stat = 0;

$.ajax({
	url: 'transaction.php?p=dealer_tstat&tdid=' + id,
	type: 'get',
	async: false,
	success: function(data, status) {
		stat = data;
	}
});

return stat;

}

function wiPenalty(id) {

$('#impose').prop('checked',false);
$('#waive').prop('checked',false);
$('#' + id).prop('checked',true);

$('#reason').prop('disabled',true);
if ($('#waive').prop('checked')) $('#reason').prop('disabled',false);

}

function penalty(tra) {

uncheckSelected(tra);

var t = 'Penalty';

var inForm = 'forms/penalty.php';
var f = function() {

var wa = ($('#waive').prop('checked')) ? 1 : 0;
var re = $('#reason').val();
if (wa == 0) re = '';

$.ajax({
	url: 'transaction.php?p=penalty',
	type: 'post',
	data: {ptra: tra, pwa: wa, pre: re},
	success: function(data, status) {
		closeMainDialog();
		content(4,2,rTranF());
	}
});

};

var exe = function() {

$.ajax({
	url: 'transaction.php?p=penalty_status&ptid=' + tra,
	dataType: 'json',
	success: function(data, status) {
		var d = data.penalty[0];
		if (parseInt(d.jtp) == 1) {
		$('#impose').prop('checked',false);
		$('#waive').prop('checked',true);
		$('#reason').prop('disabled',false);
		$('#reason').val(d.jtre);
		}
	}
});

};

mainDialog(250,300,t,inForm,exe);
mainDialogB('Update','Cancel',f);

}

function swapProduct() {

	if (count_checks('frmTransactionItem') == 0) {
		notify(330,200,'Please select one product to swap with.');
	} else if (count_checks('frmTransactionItem') > 1) {
		var f = function() { uncheckMulti('frmTransactionItem'); }
		notify(330,200,'Please select only one product.',f);
	} else {
		addTI(2);
	}

}

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
		
		var dt = $('#hdt').val();
		var supid = $('#hsupid').val();
		if ( (parseInt(jsid) == 2) || (parseInt(jsid) == 7) || (parseInt(jsid) == 11) || (parseInt(jsid) == 17) || (parseInt(jsid) == 24) || (parseInt(jsid) == 34) || (parseInt(jsid) == 35) || (parseInt(jsid) == 22) || (parseInt(jsid) == 39) || (parseInt(jsid) == 20) || (parseInt(jsid) == 10) || (parseInt(jsid) == 15) ) $('#tprice').prop('disabled',false); else $('#tprice').prop('disabled',true); // option to change price for avon
		$.ajax({ // get discount according to dealer's discount type - special case if avon
			url: 'transaction.php?p=discount_type&dt=' + dt + '&supid=' + supid,
			dataType: 'json',
			success: function(data, status) {
			var d = data.dtype[0];

			$('#hdis').val(d.jds);
			
			}
		});
		
closeSubDialog();
		
}

function suggestProduct(p) {

if (p != '') {

var inRow = ''
$('#tab-product-search-results tbody').html(inRow);

$.ajax({
url: 'transaction.php?p=suggest_products&filp=' + p,
dataType: 'json',
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

subDialog(720,600,t,inForm,exe);
subDialogB('Close',f);

}

function transaction(src) {

var tb = 0;

$.ajax({
	url: 'transaction.php?p=get_branch',
	type: 'get',
	async: false,
	success: function(data, status) {
		tb = data;
	}
});

var t = (src == 1) ? 'New Transaction' : 'Edit Transaction';
var inForm = 'forms/transaction.php?tmode=' + src + '&br=' + tb + '&pt=';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmTransaction(id,src);
};

var exe = function() {

$.ajax({
	url: 'transaction.php?p=last_trano',
	type: 'get',
	success: function(data, status) {
		$('#trano').val(data);
	}
});

$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers&ftb=1',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#tdn').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
		// $('#tdn').val(item.dealer);
		$('#hdid').val(item.id); $('#trec').val(item.dr); $('#tclimit').val(item.dcl); $('#htclimit').val(item.dcl);
		$('#hdt').val(item.ddt); $('#hclt').val(item.dclt);
		
		$('#frmTransactionItem tbody').html('<span style="padding: 5px;">Checking if dealer has due transactions. Please wait...<span>');
		// check if dealer has pending due tra
		// var tdstat = chkDealerTstat(item.id);
		$.ajax({
			url: 'transaction.php?p=dealer_tstat&tdid=' + item.id,
			type: 'get',
			success: function(data, status) {
				$('#frmTransactionItem tbody').html('');
				if (parseInt(data) > 0) {
				notify(350,200,'Dealer has ' + data + ' due transaction(s).');
				} else {
				if ( ($('#hdid').val() != 0) && (parseInt(data) == 0) ) enAddT();
				}		
			}
		});		
		//
		
		}});				 
		}
});

$('#adds').button();
$('#adds').click(function() { addTI(src); });
$('#terms').prop('checked',true);

$.unblockUI();

}

if (src == 2) {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		var f = function() { uncheckMulti('frmContent'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
	id = getCheckedId('frmContent');	
	var pay_stat = $('.tstatok-' + id).html();	
	var pay_t = $('.payt-' + id).html();
	var pen_stat = $('.penstat-' + id).html();
	var isDue = pay_stat.indexOf('due');
	
 	if ( ((pay_stat == 'Cleared') && (pay_t == 'Terms')) || (pen_stat == 'Waived') ) {
		if ( (parseInt(getUid()) != 2) && (parseInt(getUid()) != 28) ) {
			var unchk = function() { uncheckSelected(id); };
			notify(350,200,'Use the View TRA at the Tools column to view info.',unchk);
			return;
		}
	}	
	
	inForm = 'forms/transaction.php?tmode=' + src + '&br=' + tb + '&pt=' + pay_t;
	
	var f2 = function() {
		uncheckSelected(id);
	};
	
	var exe = function() {
		
		var vat = 1;
		var addd = 0;
		var chkstat = 0;		
		var ut = 0;
		var wa = 0;	
		var swap = 0;
		
		$('#adds').button();
		$('#adds').click(function() { addTI(src); });
		$('#swaps').button();
		$('#swaps').click(function() { swapProduct(); });		
		enAddT();

		if (isDue > -1) {
			$('#tpcode').prop('disabled',true);
		}		
		
		$('#tdn').prop('disabled',true);
		
		var lessd = 0;
		var dayp = 0;
		var pbal = 0;
		$.ajax({
			dataType: 'json',
			async: false,
			url: 'transaction.php?p=view&tid=' + id,
			success: function(data, status) {
			var d = data.tino[0];
			$('#htc').val(d.jltino);
			$('#hhtc').val(d.jltino);			
			$('#tinos').val(d.jtinos);
			
			d = data.edittransaction[0];
			$('#trano').val(d.jtn);
			$('#hdid').val(d.jtdid);
			$('#tdate').val(d.jtd);
			$('#ddate').val(d.jtdd);
			$('#tdn').val(d.jtdn);			
			$('#trec').val(d.jtdr);
			$('#hdt').val(d.jdt);
			$('#hclt').val(d.jtclt);
			$('#tclimit').val(d.jtcl);
			$('#htclimit').val(d.jtcl);			
			$('#tra-add-dis > span').html(d.jtcd);
			if (parseInt(d.jtpt) == 0) { $('#terms').prop('checked',true); }
			if (parseInt(d.jtpt) == 1) { $('#cash').prop('checked',true); }
			$('#terms').prop('disabled',true);
			$('#cash').prop('disabled',true);
			vat = d.jtvat;
			addd = d.jtadd;
			swap = d.jswapa;
			var iscash = d.jtpt;
			ut = d.jtut;
			wa = d.jtwa;
			var bal = d.jtbal;
			$('#tbal').val(d.jtbal);		

			if ( (parseInt(d.jtpt) == 1) || (parseInt(ut) == 1) || (parseInt(wa) == 1) ) $('#ddate').val('');			
			
			// computation with penalty
			if ( (parseInt(iscash) == 0) && (parseInt(ut) == 0) && (parseInt(wa) == 0) ) { // skip for unlimited terms

				if ( (parseInt(d.jdp) > 0) && (parseInt(d.jdp) <= 7) ) lessd = 5;
				if ( (parseInt(d.jdp) > 7) && (parseInt(d.jdp) <= 14) ) lessd = 10;
				if ( (parseInt(d.jdp) > 14) && (parseInt(d.jdp) <= 21) ) lessd = 15;
				if ( (parseInt(d.jdp) > 21) && (parseInt(d.jdp) <= 29) ) lessd = 15;				
				if (parseInt(d.jdp) > 29) lessd = 0;

				if ((parseInt(d.jdp) == 0) && (parseFloat(bal) > 0)) $('#tstatus').val('Due today');		
				if ((parseInt(d.jdp) > 0) && (parseFloat(bal) > 0)) $('#tstatus').val(d.jdp + ' day(s) due');
				var tstat = $('.tstatok-' + id).html();
				// if (parseFloat(bal) <= 0) $('#tstatus').val('Cleared');
				$('#tstatus').val(tstat);
			}
			//
			dayp = d.jdp;
			pbal = d.jpbal;
			
			var inRow;
			$.each(data.traitems, function(i,d){
			
			var nb = '';
			if (parseInt(d.jtb) == 1) nb = ' -  Borrowed';
			
			var hswap = '';
			var idis = d.jtid;			
			var idesc = d.jtii;
			if (parseInt(d.jrep) != 0) idesc += d.jrepi;
			if (parseInt(d.jswp) != 0) {
				idesc += d.jswpi;
				hswap = 'style="color: #ff0000;"';
			}
			
			inRow  = '<tr id="tra-' + d.jtin + '">';
			if ( (parseInt(tb) == 2) && (pay_t == 'Terms') ) {
			inRow += '<td>';
			inRow += '<input type="checkbox" name="swap_' + d.jtin + '" id="swap_' + d.jtin + '" />';
			inRow += '</td>';
			}
			inRow += '<td class="tsup-' + d.jtin + '">' + d.jtis + '</td>'; // company
			inRow += '<td>';
			inRow += '<input type="text" class="tquantity-' + d.jtin + '" value="' + d.jtiq + '" onchange="cTAmt(' + d.jtin + ');" /><input type="hidden" class="hsoh-' + d.jtin + '" value="' + d.jsoh + '" />';
			inRow += '</td>'; // quantity
			inRow += '<td ' + hswap + ' class="titem-' + d.jtin + '">' + idesc + nb + '</td>'; // item name/description/size/variety
			inRow += '<td class="tprice-' + d.jtin + '">' + d.jtip + '</td>'; // unit price
			inRow += '<td class="tgross-' + d.jtin + '">' + roundToTwo(parseFloat(d.jtip)*(parseInt(d.jtiq) - parseInt(d.jtret))) + '</td>'; // gross amount
			inRow += '<td><input type="text" class="tdiscount-' + d.jtin + '" value="' + idis + '" size="10" onfocus="this.blur();" /></td>'; // discount
			var ld = ( (parseFloat(d.jtip)) - (parseFloat(d.jtip) * (parseInt(idis)/100)) ) * (d.jtiq - d.jtret);
			inRow += '<td class="tamt-' + d.jtin + '">' + roundToTwo(ld) + '</td>'; // net price
			inRow += '<td align="center">';
			inRow += '<a href="javascript: returnDItem(' + d.jtin + ',' + d.jtsino + ',\''  + d.jtrano + '\',\'' + d.jtpc + '\',\'' + d.jtpn + '\',\'' + d.jtps + '\',\'' + d.jtis + '\',' + d.jtisid + ');" class="tooltip-min"><img src="images/return.png" style="padding-right: 5px;" /><span>Return product</span></a>';
			//inRow += '<a href="javascript: itemReplace(' + d.jtin + ',' + d.jtsino + ',\''  + d.jtrano + '\',\'' + d.jtpc + '\',\'' + d.jtpn + '\',\'' + d.jtps + '\',' + d.jtip + ',' + d.jtid + ',' + d.jtiq + ',' + d.jtisid + ');" class="tooltip-min"><img src="images/swap.png" /><span>Replace product</span></a>';
			inRow += '</td>';
			inRow += '<td class="dret-' + d.jtin + '" align="center">' + d.jtret + '</td>';						
			inRow += '</tr>';
			
			$(inRow).appendTo('#tab-transaction-item tbody');			
			
			});
			}
		});
		var h = $('#frmTransactionItem').height();
		if (h >= 199) $('#frmTransactionItem').addClass('fixh');

		if ( (chkstat == 1) && (parseInt(ut) == 0) && (parseInt(wa) == 0) ) {
			vat = 1;
			addd = 0;
		}
		totalVTAmt(vat,addd,swap,lessd,dayp,pbal);
		totalQty();
		$.unblockUI();
	}
	$.blockUI({ message: $('#loadingModal') });	
	mainDialog(1300,630,t,inForm,exe);
	mainDialogB('Update','Close',f,f2);
	}
} else {
	$.blockUI({ message: $('#loadingModal') });
	mainDialog(1300,630,t,inForm,exe);
	mainDialogB('Add','Close',f);
}

}

function confirmTransaction(id,src) {

var m = (src == 1) ? 'Add this transaction?' : 'Update this transaction?';
var f = function() { transactionForm(id,src); };
confirmation(300,200,m,f);

}

function transactionForm(id,src) {

$.blockUI({ message: $('#processModal') });

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
var vat = $('#tra-vat > span').html();
var ad = $('#tra-avond > span').html();
var total_amt = $('#tra-total > span').html();

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
var tpd = $('.tdiscount-' + ti[1]).val();
var tpb = $('.tpborrow-' + ti[1]).val();

trai += tsup + ',' + tpq + ',' + tpc + ',' + tpn + ',' + tps + ',' + tpp + ',' + tpd + ',' + tsino + ',' + tpb + '|';

});

$.ajax({
	url: 'transaction.php?p=add',
	type: 'post',
	data: {pdid: did, piscash: iscash, ptclimit: tclimit, pcd: cd, tvat: vat, pad: ad, ptrai: trai, pnr: nr, ptamt: total_amt},
	success: function(data, status) {
	$.unblockUI();
	var pt = $('#cash').prop('checked');	
	clearForm('frmModule');
	closeMainDialog();
	content(4,0,'&fs=' + tday + '&fe=' + tday); $('#fe').val(tday);
	window.open('reports/transaction.php?tid=' + data, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');
		if (pt) {
			cashPayment(data);	
		} else {
			// notify(400,200,'Please go to PAYMENTS tab to process payment.');
		}
	}
});
break;

case 2:

var trano = $('#trano').val();
var ltino = $('#hhtc').val();
var tinos = $('#tinos').val();

// check for swap product
/*
if (count_checks('frmTransactionItem') != 0) {
swapid = getCheckedId('frmTransactionItem');
var swtsup = $('.htsup-' + swapid).val();
var swtpq = $('.tquantity-' + swapid).val();
var swtsino = $('.tsino-' + swapid).val();
var swtpc = $('.tpcode-' + swapid).val();
var swtpn = $('.tpname-' + swapid).val();
var swtps = $('.tpsize-' + swapid).val();
returnSwappedProduct(swapid,swtsino,trano,swtpc,swtpn,swtps,swtsup,swtpq); // if item is swapped return it first
}
*/
//

$('#tab-transaction-item tbody').children('tr').each(function() {

var ti = this.id.split("-");

// update
var tino = ti[1];
var tpqu = $('.tquantity-' + ti[1]).val();
//

// add
var tsup = $('.htsup-' + ti[1]).val();
var tpq = $('.tquantity-' + ti[1]).val();
var tsino = $('.tsino-' + ti[1]).val();
var tpc = $('.tpcode-' + ti[1]).val();
var tpn = $('.tpname-' + ti[1]).val();
var tps = $('.tpsize-' + ti[1]).val();
var tpp = $('.tprice-' + ti[1]).html();
var tpd = $('.tdiscount-' + ti[1]).val();
var tpb = $('.tpborrow-' + ti[1]).val();
var tpsw = $('.tpswap-' + ti[1]).val();
//

if (parseInt(tino) <= parseInt(ltino)) trai += tino + ',' + tpqu + '|';
else trai += tino + ',' + tsup + ',' + tpq + ',' + tpc + ',' + tpn + ',' + tps + ',' + tpp + ',' + tpd + ',' + tsino + ',' + tpb + ',' + tpsw + '|';

});

$.ajax({
	url: 'transaction.php?p=update',
	type: 'post',
	data: {pdid: did, ptrano: trano, ptclimit: tclimit, ptrai: trai, pnr: nr, pltino: ltino, ptinos: tinos, ptamt: total_amt},
	success: function(data, status) {
	$.unblockUI();	
	var pt = $('#cash').prop('checked');	
	clearForm('frmModule');
	closeMainDialog();
	content(4,0,'&fs=' + tday + '&fe=' + tday); $('#fe').val(tday);
	window.open('reports/transaction.php?tid=' + data, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');
	//notify(400,200,'Transaction successfully updated.');
	}
});
break;

}

}

function soldOutStock() {

$.blockUI({ message: $('#loadingModal') });
$.ajax({
	url: 'transaction.php?p=soldout_stock',
	type: 'post',
	success: function(data, status) {
        $.unblockUI();        
	}
});

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
var supid = 0;
var vat = 1;
var addd = 0;

var sups = '';
$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
sups += $.trim($('.htsup-' + trai[1]).val()) + ',';

});

var supss = sups.split(",");
var n = supss.length - 1;

var i;
for (i=0; i<n; ++i) {
	if (parseInt(supss[i]) == 2) {
	supid = 2;
	break;
	}
}

if (supid == 2) {

br = rTotalAmt();

// for avon additional discount
var ddid = $('#hdid').val();
$.ajax({ // get avon additional discount
	url: 'transaction.php?p=avon_discount&ddid=' + ddid + '&br=' + br,
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

/* removed -- reason: not logically appicable coz ur not supposed to add item of the same date and ref no
function chkStock() {

var stock = 0;

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

if (pc == cpc && pn == cpn && ps == cps && psiid == cpsiid) {
	stock = $('.tquantity-' + ti[1]).val();
	stock = parseInt(stock);
}

});

return stock;

}
*/

function addTI(src) {

var tb = 0;

$.ajax({
	url: 'transaction.php?p=get_branch',
	type: 'get',
	async: false,
	success: function(data, status) {
		tb = data;
	}
});

var swapid = 0;
var ns = '';

if (count_checks('frmTransactionItem') != 0) {

swapid = getCheckedId('frmTransactionItem');
var swapdesc = $('.titem-' + swapid).html();

if (swapid != 0) ns = ' - Swapped for ' + swapdesc;

}

if (chkIfItemAdded()) {
	notify(320,200,'Item has been already added.');
	return;
}

var sp = $.trim($('#tprice').val());
var sq = $.trim($('#tquantity').val());
var ds = $('#hdis').val();
var borrow = ($('#borrow').prop('checked')) ? 1 : 0;
var nb = '';
if (parseInt(borrow) == 1) nb = ' - Borrowed';
var np = ((parseFloat(sp)*sq) -  ((parseFloat(sp)*sq) * parseFloat(parseInt(ds)/100))); // net

// check if quantity > stocks
var stock = $('#hstock').val();
if ((parseInt(stock) -  parseInt(sq)) < 0) {
	notify(350,200,'Insufficient stock(s) available.');
	return;
}

// check credit limit
var tclimit = $('#htclimit').val();
var tratotal = $('#tra-total > span').html();

var chkc = $('#cash').prop('checked');
var chkcl = $('#hclt').val();

var hhtc = $('#hhtc').val();
if (parseInt(hhtc) > 0) tratotal = totalTAmtEdit();

if ( ((parseFloat(tratotal) + np) > parseFloat(tclimit)) && (parseInt(chkcl) != 2) && (!chkc) ) {
	notify(380,200,'Cannot add this item. Credit limit reached.');
} else {

var tc = $('#htc').val();
var sd = $('#hsupd').val();
var hs = $('#hsupid').val();
var hsino = $('#hsino').val();
var pc = $.trim($('#tpcode').val());
var pn = $.trim($('#tpname').val());
var ps = $.trim($('#tpsize').val());
var psiid = $('#hsiid').val();
$('.validate').html('');	

var disableEditDiscount = 'onfocus = "this.blur();"';
if ((parseInt(hs) == 11) || (parseInt(hs) == 17) || (parseInt(hs) == 35) || (parseInt(hs) == 38) || (parseInt(hs) == 15) || (parseInt(hs) == 20) || (parseInt(hs) == 39) || (parseInt(hs) == 7) ) disableEditDiscount = '';

var item = $('#hitem').val();

if ((pc != '') && (pn != '') && (ps != '') && (sp != '') && (sq != '')) {

	var h = $('#frmTransactionItem').height();
	if (h >= 199) $('#frmTransactionItem').addClass('fixh');	

	var inRow;

	tc = parseInt(tc) + 1;

	inRow  = '<tr id="tra-' + tc + '">';
	if ( (tb == 2) && (parseInt(src) == 2) ) inRow += '<td>&nbsp;</td>';
	inRow += '<td class="tsup-' + tc + '">' + sd + '<input type="hidden" class="htsup-' + tc + '" value="' + hs + '" /></td>'; // company
	inRow += '<td><input type="text" class="tquantity-' + tc + '" value="' + sq + '" onchange="cTAmt(' + tc + ');" onfocus="this.blur();" /><input type="hidden" class="hsoh-' + tc + '" value="' + stock + '" /></td>'; // quantity
	item = item + nb;
	if ( (tb == 2) && (parseInt(src) == 2) && (swapid != 0) ) item = item + ns;
	inRow += '<td class="titem-' + tc + '">' + item;
	inRow += '<input type="hidden" class="tsino-' + tc + '" value="' + hsino + '"/>';
	inRow += '<input type="hidden" class="tpcode-' + tc + '" value="' + pc + '"/>';
	inRow += '<input type="hidden" class="tpname-' + tc + '" value="' + pn + '"/>';
	inRow += '<input type="hidden" class="tpsize-' + tc + '" value="' + ps + '"/>';
	inRow += '<input type="hidden" class="tpsiid-' + tc + '" value="' + psiid + '"/>';
	inRow += '<input type="hidden" class="tpborrow-' + tc + '" value="' + borrow + '"/>';	
	inRow += '<input type="hidden" class="tpswap-' + tc + '" value="' + swapid + '"/>';	
	inRow += '</td>'; // item name/description/size/variety
	inRow += '<td class="tprice-' + tc + '">' + sp + '</td>'; // unit price
	inRow += '<td class="tgross-' + tc +'">' + roundToTwo(parseFloat(sp)*parseInt(sq)) + '</td>'; // gross amount
	inRow += '<td><input type="text" class="tdiscount-' + tc + '" value="' + parseInt(ds) + '" size="10" ' + disableEditDiscount + ' onchange="cTAmt(' + tc + ');" /></td>'; // discount
	inRow += '<td class="tamt-' + tc + '">' + roundToTwo(np) + '</td>'; // net price
	inRow += '<td align="center">' + '<a href="javascript: delTI(' + tc + ',' + src + ');"><img src="images/delete.png" /></a>' + '</td>';
	if (parseInt(src) == 2) inRow += '<td>&nbsp;</td>';
	inRow += '</tr>';

	$(inRow).appendTo('#tab-transaction-item tbody');
	$('#htc').val(tc);	
	$('#tpcode').prop('disabled',false);
	
} else {
	$('.validate').html('Please fill up; product code, product name, price, and quantity.');
}	

getTtype();
totalTAmt();
totalQty();
updateCL();
if ($('#borrow').prop('checked')) $('#borrow').prop('checked',false);
$('#hsino').val('0'); // to track what tra no is item sold to
$('#hsupd').val('');
$('#hsupid').val('0');
$('#hitem').val('');
$('#tpcode').val('');
$('#tpname').val('');
$('#tpsize').val('');
$('#tprice').val('');
$('#hstock').val('0');
$('#hsiid').val('0');
$('#hdis').val('0');

} // credit limit OK

uncheckMulti('frmTransactionItem');
	
}

function delTI(item,src) {
	var htc = $('#htc').val();
	$('#tra-' + item).remove();
	if (src == 1) {
		if (parseInt(item) == parseInt(htc)) $('#htc').val(parseInt(item) -1);
	}
getTtype();
totalTAmt();
totalQty();
updateCL();
}

function cTAmt(id) {

var gr = 0;
var net = 0;
var p = $.trim($('.tprice-' + id).html());
var q = $.trim($('.tquantity-' + id).val());
var d = $('.tdiscount-' + id).val();

var stock = $('.hsoh-' + id).val();

if ((parseInt(stock) - parseInt(q)) < 0) {
	$('.tquantity-' + id).val(1);
	notify(350,200,'Insufficient stock(s) available.');
	return;
}

gr = parseFloat(p) * parseInt(q);
net = ( (parseFloat(p)) - (parseFloat(p) * (parseInt(d)/100)) ) * parseInt(q);
$('.tgross-' + id).html(gr);
$('.tamt-' + id).html(net);

totalTAmt();
totalQty();
updateCL();

}

function totalTAmtEdit() {

var hhtc = $('#hhtc').val();

var tnet = 0;
var tnamt = 0;
var tamt = 0; // total net amount
var adis = $('#tra-add-dis > span').html();

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tamt-' + trai[1]).html());

if (parseInt(trai[1]) > parseInt(hhtc)) tnamt = tnamt + parseFloat(tnet);

});

// avon
var supid = 0;	
var vat = 1;
var addd = 0;

var sups = '';
$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
sups += $.trim($('.htsup-' + trai[1]).val()) + ',';

});

var supss = sups.split(",");
var n = supss.length - 1;

var i;
for (i=0; i<n; ++i) {
	if (parseInt(supss[i]) == 2) {
	supid = 2;
	break;
	}
}

if (supid == 2) {
var avon = avonDiscount();
var avons = avon.split(",");
vat = parseFloat(avons[0]);
addd = parseInt(avons[1]);
}
//

return tnamt;

}

function totalTAmt() {

var tnet = 0;
var tnamt = 0;
var tamt = 0; // total net amount
var adis = $('#tra-add-dis > span').html();

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tamt-' + trai[1]).html());

tnamt = tnamt + parseFloat(tnet);

});

// avon
var supid = 0;
var vat = 1;
var addd = 0;

var sups = '';
$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
sups += $.trim($('.htsup-' + trai[1]).val()) + ',';

});

var supss = sups.split(",");
var n = supss.length - 1;

var i;
for (i=0; i<n; ++i) {
	if (parseInt(supss[i]) == 2) {
	supid = 2;
	break;
	}
}

if (supid == 2) {
var avon = avonDiscount();
var avons = avon.split(",");
vat = parseFloat(avons[0]);
addd = parseInt(avons[1]);
}
//

$('#tra-total-amt > span').html(roundToTwo(tnamt));
tamt = tnamt - (tnamt/vat*(addd/100));
tamt = tamt - (tamt * (parseInt(adis)/100)); // cash
$('#tra-vat > span').html(vat);
$('#tra-avond > span').html(addd);
$('#tra-total > span').html(roundToTwo(tamt));

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

function totalVTAmt(vat,addd,swap,lessd,dayp,pbal) {

var tnet = 0;
var tnamt = 0;
var tamt = 0; // total net amount
var adis = $('#tra-add-dis > span').html();

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tamt-' + trai[1]).html());

tnamt = tnamt + parseFloat(tnet);

});

tnamt = tnamt - (swap*2);
var ptnamt = tnamt;
if (parseInt(dayp)>29) ptnamt = pbal;
$('#tra-total-amt > span').html(roundToTwo(ptnamt));
tamt = tnamt - (tnamt/vat*(addd/100));
tamt = tamt - (tamt * (parseInt(adis)/100)); // cash
$('#tra-vat > span').html(vat);
$('#tra-avond > span').html(addd);
var pen = 0;
var bal = $('#tbal').val();
pen = parseFloat(bal)*(parseFloat(lessd)/100);
if (parseFloat(bal)<0) pen = 0;
$('#tra-penalty > span').html(pen.toFixed(2));
$('#tra-total > span').html(roundToTwo(tamt));

}

function rTotalAmt() {

var tnet = 0;
var tnamt = 0;
var tamt = 0;
var adis = $('#tra-add-dis > span').html();

$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
tnet = $.trim($('.tamt-' + trai[1]).html());
var chk = $('.dret-' + trai[1]).html();

if (chk) {
if (parseInt(chk) > 0) return true;
}

tnamt = tnamt + parseFloat(tnet);

});

$('#tra-total-amt > span').html(roundToTwo(tnamt));
tamt = tnamt - (tnamt * (parseInt(adis)/100));

return tamt;

}

function updateCL() {

var swap = 0;
if (count_checks('frmTransactionItem') > 0) swap = 1;

var hhtc = $('#hhtc').val();

var chkc = $('#cash').prop('checked');
var chkcl = $('#hclt').val();
var tclimit = $('#htclimit').val();
var tratotal = $('#tra-total > span').html();
if (parseInt(hhtc) > 0) tratotal = totalTAmtEdit();
var ucl = parseFloat(tclimit) - parseFloat(tratotal);

if ( ((!chkc) && (parseInt(chkcl) != 2)) && (swap == 0) ) $('#tclimit').val(roundToTwo(ucl));

if (chkc) {
	cl = $('#tclimit').val();
	hcl = $('#htclimit').val();
	if (cl != hcl) $('#tclimit').val(hcl);
}

}

function enAddT() { 

$('#tpcode').prop('disabled',false);
//$('#tpname').prop('disabled',false);
//$('#tpsize').prop('disabled',false);
//$('#tprice').prop('disabled',false);
$('#tquantity').prop('disabled',false);

}

function getTtype() {

var id = ($('#terms').prop('checked')) ? 'terms' : 'cash';

ttype(id);

}

function ttype(id) {

$('#terms').prop('checked',false);
$('#cash').prop('checked',false);
$('#' + id).prop('checked',true);

$('#tra-add-dis > span').html('0');

var sups = '';
$('#tab-transaction-item tbody').children('tr').each(function() {

var trai = this.id.split("-");
sups += $.trim($('.htsup-' + trai[1]).val()) + ',';

});

var supss = sups.split(",");
var n = supss.length - 1;

var nocashd = 0;
var i;
for (i=0; i<n; ++i) {
	if ( (parseInt(supss[i]) == 2) || (parseInt(supss[i]) == 4) || (parseInt(supss[i]) == 10) || (parseInt(supss[i]) == 11) || (parseInt(supss[i]) == 12) || (parseInt(supss[i]) == 17) || (parseInt(supss[i]) == 18) || (parseInt(supss[i]) == 24) || (parseInt(dt) == 22) ) nocashd = 1;
}

// check if discount is topseller/outright/special/sl
var dt = $('#hdt').val();
if ((parseInt(dt) == 2) || (parseInt(dt) == 3) || (parseInt(dt) == 4) || (parseInt(dt) == 5)) nocashd = 1;

if (id == 'cash' && nocashd == 0) $('#tra-add-dis > span').html('5');
totalTAmt();
updateCL();

}

function filterTransaction() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var ftrano = $.trim($('#ftrano').val());
var fcustomer = $.trim($('#fcustomer').val());
var fpcon = $('#fpcon').val();
var und = $('#unpaid-due').val();

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fs=' + fs + '&fe=' + fe + '&ftrano=' + ftrano + '&fcustomer=' + fcustomer + '&fpcon=' + fpcon +'&und=' + und;

content(4,0,par);

}

function rTranF() {

var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var ftrano = $.trim($('#ftrano').val());
var fcustomer = $.trim($('#fcustomer').val());
var fpcon = $('#fpcon').val();
var und = $('#unpaid-due').val();

par = '&fptype=' + fptype + '&fbranch=' + fbranch + '&fs=' + fs + '&fe=' + fe + '&ftrano=' + ftrano + '&fcustomer=' + fcustomer + '&fpcon=' + fpcon +'&und=' + und;

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

function returnDItem(tino,sino,pt,pc,pn,ps,supd,sup) { // added stock-in item no for reference in dealer's return

var t = 'Return Product';
var inForm = 'forms/dealer-return.php';
var f = function() {
if (validateForm('subModule')) confirmRProduct(tino,sino,pt,pc,pn,ps,sup);
};

var exe = function() {
	$('#drettrano').val(pt);
	$('#dretsup').val(supd);
	$('#dretcode').val(pc);
	$('#dretname').val(pn);
	$('#dretsize').val(ps);
};

subDialog(400,400,t,inForm,exe);
subDialogB('Ok','Cancel',f);

}

function confirmRProduct(tino,sino,pt,pc,pn,ps,sup) {

var m = 'Return this stock?';
var f = function() { dealerRForm(tino,sino,pt,pc,pn,ps,sup); };
confirmation(300,200,m,f);

}

function dealerRForm(tino,sino,pt,pc,pn,ps,sup) {

var dretqty = $.trim($('#dretqty').val());
var dretnote = $.trim($('#dretnote').val());

$.ajax({
	url: 'dealer-return-ajax.php?p=add',
	type: 'post',
	data: {ptino: tino, psino: sino, ptsup: sup, pdrettrano: pt, pdretcode: pc, pdretname: pn, pdretsize: ps, pdretqty: dretqty, pdretnote: dretnote},
	success: function(data, status) {	
	closeSubDialog();
	closeMainDialog();
	transaction(2);
	notify(300,200,data);
	}
});

}

function itemReplace(tino,sino,pt,pc,pn,ps,pgp,pd,pq,sup) {

var t = 'Replace Product';
var inForm = 'forms/item-replace.php';
var f = function() {
if (validateForm('subModule')) confirmReProduct(tino,sino,pt,pc,pn,ps,pgp,pd,pq,sup);
};

var exe = function() {

	$('#irepcode').val(pc);
	$('#irepname').val(pn);
	$('#irepsize').val(ps);
	$('#irepqty').val(pq);
	
    $.blockUI({ message: $('#loadingModal') });	
	$.ajax({
	type: 'json',
	url: 'transaction.php?p=list_products',
	success: function(data, status) {
			 $.unblockUI();
			 var suggestList = {};
			 suggestList.productlist = data;
			 $('input#repicode').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
				$('#hrsino').val(item.id); // to track what tra no is item sold to
				$('#repicode').val(item.jpc);
				$('#repiname').val(item.jpn);
				$('#repisize').val(item.jps);
			 }});
			 }
	});	
	
};

subDialog(1150,400,t,inForm,exe);
subDialogB('Ok','Cancel',f);

}

function confirmReProduct(tino,sino,pt,pc,pn,ps,pgp,pd,pq,sup) {

var m = 'Replace this item?';
var f = function() {

// return the item first
var dretqty = $.trim($('#irepqty').val());
var dretnote = 'Replaced';

$.ajax({
	url: 'dealer-return-ajax.php?p=add',
	type: 'post',
	data: {ptino: tino, psino: sino, ptsup: sup, pdrettrano: pt, pdretcode: pc, pdretname: pn, pdretsize: ps, pdretqty: dretqty, pdretnote: dretnote},
	success: function(data, status) {	
		dealerReForm(tino,sup,pt,pgp,pd);
	}
});
//

};
confirmation(300,200,m,f);

}

function dealerReForm(tino,sup,pt,pgp,pd) {

var hrsino = $('#hrsino').val();
var repicode = $('#repicode').val();
var repiname = $('#repiname').val();
var repisize = $('#repisize').val();
var repiqty = $('#repiqty').val();

$.ajax({
	url: 'transaction.php?p=replace_item',
	type: 'post',
	data: {ptino: tino, psup: sup, ppt: pt, prepicode: repicode, prepiname: repiname, prepisize: repisize, prepiqty: repiqty, ppgp: pgp, ppd: pd, phrsino: hrsino},
	success: function(data, status) {	
		closeSubDialog();
		closeMainDialog();
		transaction(2);
		notify(300,200,data);		
	}
});

}

function walkIn() {

window.open('walk-in-cash.php', '', 'width=1280px, scrollbars=yes, toolbar=no, menubar=yes');

}

function viewTran(tid) {

window.open('view-tra.php?tid=' + tid, '', 'width=1024px, scrollbars=yes, toolbar=no, menubar=yes');
uncheckSelected(tid);

}

function traReport() {

var chk = $('#unpaid-due').val();
var par = '';

var fptype = $('#fptype').val();
var fbranch = $('#fbranch').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var ftrano = $.trim($('#ftrano').val());
var fcustomer = $.trim($('#fcustomer').val());
var fpcon = $('#fpcon').val();
var und = $('#unpaid-due').val();

if (parseInt(chk) == 0) {
	notify(430,200,'You cannot produce report for All transactions.');
	return;
}

par = '?fptype=' + fptype + '&fbranch=' + fbranch + '&fs=' + fs + '&fe=' + fe + '&ftrano=' + ftrano + '&fcustomer=' + fcustomer + '&fpcon=' + fpcon +'&und=' + und;

window.open('reports/transaction-report.php' + par, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

}

/*
function returnSwappedProduct(tino,sino,pt,pc,pn,ps,sup,pq) {

// return the item first
var dretnote = 'Swapped';

$.ajax({
	url: 'dealer-return-ajax.php?p=add',
	type: 'post',
	data: {ptino: tino, psino: sino, ptsup: sup, pdrettrano: pt, pdretcode: pc, pdretname: pn, pdretsize: ps, pdretqty: pq, pdretnote: dretnote},
	success: function(data, status) {}
});
//

}
*/

function getUid() {

var uid = 0;
$.ajax({
	url: 'transaction.php?p=get_uid',
	type: 'get',
	async: false,
	success: function(data, status) { uid = data; }
});
return uid;

}