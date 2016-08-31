var gtb = 0;


function filterOffsetID(mn) {

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_offsets&gmn=' + mn,
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#stock-off-id').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#stock-off-id').val(item.text); showOffsetBal(item.text); }});            
		 }
});

}

function stock(src) {

var tb = 0;

$.ajax({
	url: 'transaction.php?p=get_branch',
	type: 'get',
	async: false,
	success: function(data, status) {
		tb = data;
		gtb = data;
	}
});

var t = (src == 1) ? 'Add Stock' : 'Edit Stock';
var inForm = 'forms/stock-in.php?src=1&br=' + tb;
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmStock(id,src);
};

var exe = function() {

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_suppliers',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#supplier').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#ssup').val(item.id); chkCompanyBranch(item.sbr, item.cbr, item.cfn); }});            
		 }
});

// category select
$.ajax({
	url: 'stock-in.php?p=list_categories',
	type: 'post',
	async: false,
	success: function(data, status) {
		$('#pacat').html(data);
	}
});
//

$('#squantity').change(function() {

var p = $('#sprice').val();
var q = $('#squantity').val();
var a = parseFloat(p) * parseInt(q);

$('#samt').val(a);
var na = $('#samt').val();
if (na == 'NaN') $('#samt').val('');

});

$('#pcode').bind('keyup focus',function() {
	$('#tab-stock-in .autocomplete-own div').html('');
	if (this.value.length < 3) return;
	$('#tab-stock-in .autocomplete-own div').html('<p style="width: 100%; text-align: center;"><img src="images/ajax-loader.gif"></p>');	
	$('body').bind('click',function(){
		$('#tab-stock-in .autocomplete-own div').html('');
	});
	var query_pcode = this.value;
/*
	$.ajax({
	type: 'json',
	url: 'stock-in.php?p=list_product_codes',
	success: function(data, status) {
			 var suggestList = {};
			 suggestList.productlist = data;
			 $('input#pcode').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
			 $('#pcode').val(item.jpc);
			 $('#pname').val(item.jpn);
			 $('#psize').val(item.jps);
			 $('#sprice').val(item.jpp);
			 $('#houtd').val(item.jod);
			 $('#pcode').prop('disabled',true);
			 $('#pname').prop('disabled',true);
			 $('#psize').prop('disabled',true);	 
			 }});            
			 }
	});	
*/
	$.ajax({
	type: 'post',
	url: 'stock-in.php?p=list_product_codes_suggest_own',
	data: {pquery_pcode: query_pcode},
	success: function(data, status) {
            $('#tab-stock-in .autocomplete-own div').html(data);
		}
	});
	
});

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_members',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#smn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
			filterOffsetID(item.text);
		 }});            
		 }
});	

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_membernos',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#smno').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ fillMemNo(item.text); }});            
		 }
});

$( "#sindate" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});

$('#adds').button();
$('#adds').click(function() { addStockI(1); });

$.unblockUI();

}

if (src == 2) {
	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		var f = function() { uncheckMulti('frmContent'); }
		notify(300,200,'Please select only one.',f);
	} else {
	
		inForm = 'forms/stock-in.php?src=2&br=' + tb;
		
		id = getCheckedId('frmContent');
		
		f2 = function() { uncheckSelected(id); }
		
		exe = function() {		

		$('#adds').button();
		$('#adds').click(function() { addStockI(2); });		
		
		// category select
		$.ajax({
			url: 'stock-in.php?p=list_categories',
			type: 'post',
			async: false,
			success: function(data, status) {
				$('#pacat').html(data);
			}
		});
		//		
		
		$('#pcode').bind('keyup focus',function() {
			$('#tab-stock-in .autocomplete-own div').html('');
			if (this.value.length < 3) return;
			$('#tab-stock-in .autocomplete-own div').html('<p style="width: 100%; text-align: center;"><img src="images/ajax-loader.gif"></p>');	
			$('body').bind('click',function(){
				$('#tab-stock-in .autocomplete-own div').html('');
			});
			var query_pcode = this.value;
		/*
			$.ajax({
			type: 'json',
			url: 'stock-in.php?p=list_product_codes',
			success: function(data, status) {
					 var suggestList = {};
					 suggestList.productlist = data;
					 $('input#pcode').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){
					 $('#pcode').val(item.jpc);
					 $('#pname').val(item.jpn);
					 $('#psize').val(item.jps);
					 $('#sprice').val(item.jpp);
					 $('#houtd').val(item.jod);
					 $('#pcode').prop('disabled',true);
					 $('#pname').prop('disabled',true);
					 $('#psize').prop('disabled',true);	 
					 }});            
					 }
			});	
		*/
			$.ajax({
			type: 'post',
			url: 'stock-in.php?p=list_product_codes_suggest_own',
			data: {pquery_pcode: query_pcode},
			success: function(data, status) {
					$('#tab-stock-in .autocomplete-own div').html(data);
				}
			});
			
		});			
		
		$( "#sindate" ).datepicker({
			showOn: "button",
			buttonImage: "images/calendar.gif",
			buttonImageOnly: true
		});		

		$.ajax({
		type: 'json',
		async: false,
		url: 'stock-in.php?p=list_suppliers',
		success: function(data, status) {
				 var suggestList = {};
				 suggestList.supplierlist = data;
				 $('input#supplier').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){
				 $('#ssup').val(item.id);	 
				 }});            
				 }
		});
		
		$.ajax({
		type: 'json',
		url: 'stock-in.php?p=list_members',
		async: false,
		success: function(data, status) {
				 var suggestList = {};
				 suggestList.productlist = data;
				 $('input#smn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
				 }
		});			
		
		$.ajax({
			url: 'stock-in.php?p=edit&sid=' + id,
			dataType: 'json',
			async: false,
			success: function(data, status) {
				
				var d = data.editstock[0];
				
				var user_grant = d.jgrant;
				$('#sindate').val(d.jsid);
				$('#smno').val(d.jmno);				
				$('#smn').val(d.jmn);
				$('#srefno').val(d.jsr);
				$('#supplier').val(d.jsu);
				$('#ssup').val(d.jsupid);		
				$('#srefno').prop('disabled',true);
				$('#supplier').prop('disabled',true);
				$('#smno').prop('disabled',true);
				$('#smn').prop('disabled',true);
				$('#stock-off').val(d.jsoff);
				$('#stock-off-id').val(d.jsoffid);
				var supb = d.jsib; // supplier branch
				var rok = d.jrok;
				$('.stock-in-cashier').html(d.jcashier);				
				$('#sel-stock-mop').val(d.jspm);
				stockMOP(d.jspm);
				if (parseInt(d.jspm) == 0) $('#stock-dd').val(d.jsdd);
				$('#avon-rebate').val(d.javr);
				$('#avon-cft-discount').val(d.jcftd);
				$('#avon-ncft-discount').val(d.jncftd);
				$('#avon-home-discount').val(d.jhsd);
				$('#avon-health-discount').val(d.jhcd);
				
				d = data.siino[0];
				$('#hic').val(d.jsiino);
				$('#sinos').val(d.jsiinos);			
				
				var psino = d.jsiinos.split(",");
				
				$.each(data.stockitems, function(i,d){							
				
				if (d.jsino == undefined) return true;
				var inRow;

				inRow  = '<tr id="si-' + d.jsino + '">';
				if ((parseInt(tb) == 2) || (parseInt(tb) == 3)) inRow += '<td><select class="pacat-' + d.jsino + '" style="width: 120px;">' + d.japcs + '</select></td>';				
				inRow += '<td class="pcode-' + d.jsino + '">' + d.jpc + '<input type="hidden" class="outd-' + d.jsino + '" value="' + d.jod + '" /></td>';
				inRow += '<td class="pname-' + d.jsino + '">' + d.jpn + '</td>';
				inRow += '<td class="psize-' + d.jsino + '">' + d.jps + '</td>';				
				inRow += '<td><input type="text" class="sprice-' + d.jsino + '" value="' + d.jsp + '" onchange="cAmt(' + d.jsino + ');" /></td>';
				inRow += '<td><input type="text" class="squantity-' + d.jsino + '" value="' + d.jsq + '" onchange="cAmt(' + d.jsino + ');" /></a></td>';
				inRow += '<td class="samt-' + d.jsino + '">' + parseFloat(d.jsp) * d.jsq + '</td>';							
				inRow += '<td align="center">';
				if ((parseInt(supb) == 1) || (parseInt(rok) == 1) || (parseInt(supb) == 3) || (parseInt(rok) == 3)) inRow += '<a href="javascript: returnSItem(' + d.jsino + ',\'' + d.jpr + '\',\'' + d.jpc + '\',\'' + d.jpn + '\',\'' + d.jps + '\',\'' + d.jrsupd + '\',' + d.jrsup + ');" class="tooltip-min"><img src="images/return.png" /><span>Return stock</span></a>';
				else inRow += '<a href="javascript: stockReplace(' + d.jsino + ');" class="tooltip-min"><img src="images/swap.png" /><span>Replace Stock</span></a>';
				if (user_grant == 100) inRow += '<a href="javascript: delSItem(' + d.jsino + ',2);" class="tooltip-min" style="padding-left: 8px;"><img src="images/delete.png" /><span>Delete stock</span></a>';				
				inRow += '</td>';				
				inRow += '<td>' + d.jret + '</td>';
				inRow += '<td>' + d.jrem + '</td>';
				inRow += '</tr>';
												
				$(inRow).appendTo('#tab-stock-in-item tbody');
				$('.pacat-' + d.jsino).val(d.japcid);
				var h = $('#frmStockItem').height();
				if (h >= 199) $('#frmStockItem').addClass('fixh');
				totalAmt();
				
				});
				
			}
		});				
		
		avonMaxDiscount(id);
		
		$('#supplier').change(function() {
			var csup = $('#supplier').val();
			$.ajax({
				url: 'stock-in.php?p=check_supplier',
				type: 'post',
				data: {pcsup: csup},
				success: function(data, status) {
					if (data == 0) $('#ssup').val(data);
				}
			});
		});

		$('#srefno').prop('disabled',true);
		
		var gmn = $('#smn').val();
		filterOffsetID(gmn);
		
		$.unblockUI();
		}
		$.blockUI({ message: $('#loadingModal') });
		mainDialog(1250,650,t,inForm,exe);
		mainDialogB('Update','Close',f,f2);
	}
} else {
$.blockUI({ message: $('#loadingModal') });
mainDialog(1250,650,t,inForm,exe);
mainDialogB('Add','Close',f);
}
}

function confirmStock(id,src) {

var m = (src == 1) ? 'Add this stock?' : 'Update stock\'s info?';
var f = function() { stockForm(id,src); };
confirmation(300,200,m,f);

}

function stockForm(id,src) {

$('body').unbind();

$.blockUI({ message: $('#processModal') });

var nr = $("#tab-stock-in-item tbody tr").size();

var sindate = $('#sindate').val();
var smno = $.trim($('#smno').val());
var smn = $.trim($('#smn').val());
var srefno = $.trim($('#srefno').val());
var ssup = $('#ssup').val();
var supplier = $.trim($('#supplier').val());
var sioff = $('#stock-off').val();
var sioffid = $('#stock-off-id').val();
var spm = $('#sel-stock-mop').val();
var sdd = '0000-00-00';
if (parseInt(spm) == 0) sdd = $('#stock-dd').val();

var avon_rebate = $('#avon-rebate').val();
if (avon_rebate == '') avon_rebate = 0;

var avon_cft_discount = $('#avon-cft-discount').val();
if (avon_cft_discount == '') avon_cft_discount = 0;

var avon_ncft_discount = $('#avon-ncft-discount').val();
if (avon_ncft_discount == '') avon_ncft_discount = 0;

var avon_home_discount = $('#avon-home-discount').val();
if (avon_home_discount == '') avon_home_discount = 0;

var avon_health_discount = $('#avon-health-discount').val();
if (avon_health_discount == '') avon_health_discount = 0;

var pcode;
var pname;
var psize;
var sprice;
var squantity;
var pacat;

var co = 0;
var arsitem = '';

switch (src) {

case 1:

$('#tab-stock-in-item tbody').children('tr').each(function() {
++co;
var sii = this.id.split("-");
pcode = $.trim($('.pcode-' + sii[1]).html());
pname = $.trim($('.pname-' + sii[1]).html());
pname = pname.replace(/\&amp;/g,'&');
psize = $.trim($('.psize-' + sii[1]).html());
sprice = $.trim($('.sprice-' + sii[1]).val());
squantity = $.trim($('.squantity-' + sii[1]).val());
pacat = ($('.pacat-' + sii[1]).val()) ? $('.pacat-' + sii[1]).val() : '0';
arsitem += pcode + ',' + pname + ',' + psize + ',' + sprice + ',' + squantity + ',' + pacat + '|';

});

$.ajax({
	url: 'stock-in.php?p=add',
	type: 'post',
	data: {psindate: sindate, psmno: smno, psmn: smn, psrefno: srefno, pssup: ssup, psupplier: supplier, parsitem: arsitem, pco: co, psioff: sioff, psioffid: sioffid, pspm: spm, psdd: sdd, pcftd: avon_cft_discount, pncftd: avon_ncft_discount, phsd: avon_home_discount, phcd: avon_health_discount, pavr: avon_rebate},
	success: function(data, status) {
	$.unblockUI();	
	clearForm('frmModule'); closeMainDialog(); stock(1);
	var f = function() { content(1,0,'&fs=' + tday + '&fe=' + tday); $('#fe').val(tday); }
	notify(300,200,data,f);
	}
});

break;

case 2:

var sinos = $('#sinos').val();

$('#tab-stock-in-item tbody').children('tr').each(function() {
++co;
var sii = this.id.split("-");
pcode = $.trim($('.pcode-' + sii[1]).html());
pname = $.trim($('.pname-' + sii[1]).html());
pname = pname.replace(/\&amp;/g,'&');
psize = $.trim($('.psize-' + sii[1]).html());
sprice = $.trim($('.sprice-' + sii[1]).val());
squantity = $.trim($('.squantity-' + sii[1]).val());
pacat = ($('.pacat-' + sii[1]).val()) ? $('.pacat-' + sii[1]).val() : '0';
arsitem += pcode + ',' + pname + ',' + psize + ',' + sprice + ',' + squantity + ',' + sii[1] + ',' + pacat + '|';

});

$.ajax({
	url: 'stock-in.php?p=update&sid=' + id,
	type: 'post',
	data: {psindate: sindate, psmno: smno, psmn: smn, psrefno: srefno, pssup: ssup, psupplier: supplier, parsitem: arsitem, psinos: sinos, pco: co, psioff: sioff, psioffid: sioffid, pspm: spm, psdd: sdd, pcftd: avon_cft_discount, pncftd: avon_ncft_discount, phsd: avon_home_discount, phcd: avon_health_discount, pavr: avon_rebate},
	success: function(data, status) {
	$.unblockUI();	
	closeMainDialog();
	var f = function() { content(1,2,rStockF()); }
	notify(300,200,data,f);
	}
});

break;

}

}

function addStockI(src) {

var ic = $('#hic').val();
var apc = $('#pacat option:selected').text();
var apcid = $('#pacat').val();
var pc = $.trim($('#pcode').val());
var pn = $.trim($('#pname').val());
var ps = $.trim($('#psize').val());
var sp = $.trim($('#sprice').val());
var sq = $.trim($('#squantity').val());
$('.validate').html('');

var outd = $.trim($('#houtd').val());

if ((pc != '') && (pn != '') && (ps != '') && (sp != '') && (sq != '')) {

	var h = $('#frmStockItem').height();
	if (h >= 199) $('#frmStockItem').addClass('fixh');	

	var inRow;

	ic = parseInt(ic) + 1;
	
	inRow  = '<tr id="si-' + ic + '">';
	if ((parseInt(gtb) == 2) || (parseInt(gtb) == 3)) inRow += '<td>' + apc + '<input class="pacat-' + ic + '" type="hidden" value="' + apcid + '" /></td>';
	inRow += '<td class="pcode-' + ic + '">' + pc + '</td>';
	inRow += '<td class="pname-' + ic + '">' + pn + '</td>';
	inRow += '<td class="psize-' + ic + '">' + ps + '</td>';	
	inRow += '<td><input type="text" class="sprice-' + ic + '" value="' + sp + '" onchange="cAmt(' + ic + ');" /><input type="hidden" class="outd-' + ic + '" value="' + outd + '" /></td>';
	inRow += '<td><input type="text" class="squantity-' + ic + '" value="' + sq + '" onchange="cAmt(' + ic + ');" /></td>';
	inRow += '<td class="samt-' + ic + '">' + parseFloat(sp) * sq + '</td>';
	inRow += '<td align="center">' + '<a href="javascript: delSItem(' + ic + ',' + src + ');"><img src="images/delete.png" /></a>' + '</td>';
	if (parseInt(src) == 2) inRow += '<td>&nbsp;</td><td>&nbsp;</td>';	
	inRow += '</tr>';
	
	$(inRow).appendTo('#tab-stock-in-item tbody');
	$('#hic').val(ic);

totalAmt();
$('#pcode').val('');
$('#pname').val('');
$('#psize').val('');
$('#sprice').val('');
$('#squantity').val('');

 $('#pcode').prop('disabled',false);
 $('#pname').prop('disabled',false);
 $('#psize').prop('disabled',false);
	
} else {
	$('.validate').html('Please fill up; product code, product name, price, and quantity.');
}	
	
}

function delSItem(item,src) {
	var hic = $('#hic').val();
	$('#si-' + item).remove();
	if (src == 1) {
		if (parseInt(item) == parseInt(hic)) $('#hic').val(parseInt(item) -1);
	}
totalAmt();	
}

function confirmStockDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete this stock(s)?';
		var f = function() { deleteStock(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(420,220,m,f,f2);
	}

}

function deleteStock(id) {

	$.ajax({
		url: 'stock-in.php?p=delete',
		type: 'post',
		data: {sid: id},
		success: function(data, status) {
			var f = function() { content(1,2); }
			notify(300,200,data,f);
		}
	});

}

function filterStock() {

var par = '';

var fs = $('#fs').val();
var fe = $('#fe').val();
var fsup = $('#fsup').val();
var frefno = $('#frefno').val();
var fmemn = $('#fmemn').val();
var fpcon = $('#fpcon').val();
var fd = $('#fd').val();

var chksup = $('#fsupplier').val();

if (chksup == '') fsup = 0;

par = '&fsup=' + fsup + '&frefno=' + frefno + '&fs=' + fs + '&fe=' + fe + '&fmemn=' + fmemn + '&fpcon=' + fpcon + '&fd=' + fd;

content(1,0,par);

}

function rStockF() {

var par = '';

var fs = $('#fs').val();
var fe = $('#fe').val();
var fsup = $('#fsup').val();
var frefno = $('#frefno').val();
var fmemn = $('#fmemn').val();
var fpcon = $('#fpcon').val();
var fd = $('#fd').val();

var chksup = $('#fsupplier').val();

if (chksup == '') fsup = 0;

par = '&fsup=' + fsup + '&frefno=' + frefno + '&fs=' + fs + '&fe=' + fe + '&fmemn=' + fmemn + '&fpcon=' + fpcon + '&fd=' + fd;

return par;

}

function cAmt(id) {

var p = $.trim($('.sprice-' + id).val());
var q = $.trim($('.squantity-' + id).val());
var amt = parseFloat(p) * q;
$('.samt-' + id).html(amt);

totalAmt();

}

function totalAmt() {

var nr = $("#tab-stock-in-item tbody tr").size();
var sprice = 0;
var squantity = 0;
var tamt = 0;
var tnamt = 0;
var outd = 0;

$('#tab-stock-in-item tbody').children('tr').each(function() {

var sii = this.id.split("-");
sprice = $.trim($('.sprice-' + sii[1]).val());
squantity = $.trim($('.squantity-' + sii[1]).val());
tamt = tamt + (parseFloat(sprice) * squantity);
outd = $.trim($('.outd-' + sii[1]).val());
tnamt = tnamt + ((parseFloat(sprice) - (parseFloat(sprice)*((parseInt(outd))/100)) ) * squantity);

});

var off = $('#stock-off').val();
$('#si-total-amt > span').html(tamt.toFixed(2));
tnamt = tnamt - parseFloat(off);
$('#si-total-net-amt > span').html(tnamt.toFixed(2));

}

function avonMaxDiscount(sid) {

var ret = false;
var avon_net = 0;
var tmp_avon_net

var off = $('#stock-off').val();

$.ajax({
	type: 'post',
	dataType: 'json',
	async: false,
	url: 'stock-in.php?p=avon_discounts',
	data: {psid: sid},
	success: function(data, status) {
		if (data.avonDiscounts[0].content == 'none') {
			ret = true;
			return;
		} else {
			avon_net = (data.avonDiscounts[0].jcftd + data.avonDiscounts[0].jncftd + data.avonDiscounts[0].jhsd + data.avonDiscounts[0].jhcd + data.avonDiscounts[0].janet) - parseFloat(off);
			tmp_avon_net = data.avonDiscounts[0].janet;
			if (avon_net == 0) avon_net = tmp_avon_net;
		}
	}
});

if (ret) return;

$('#si-total-net-amt > span').html(avon_net.toFixed(2));

}

/* Return Stocks */

function stockReturns() {

var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('stock-return.php', '', params);

}

function returnSItem(sino,pr,pc,pn,ps,supd,sup) {

var t = 'Return Stock';
var inForm = 'forms/stock-return.php';
var f = function() {
if (validateForm('subModule')) confirmRStock(sino,pr,pc,pn,ps,sup);
};
var exe = function() {
	$('#retref').val(pr);
	$('#retsup').val(supd);
	$('#retcode').val(pc);
	$('#retname').val(pn);
	$('#retsize').val(ps);
};

subDialog(400,400,t,inForm,exe);
subDialogB('Ok','Cancel',f);

}

function confirmRStock(sino,pr,pc,pn,ps,sup) {

var m = 'Return this stock?';
var f = function() { stockRForm(sino,pr,pc,pn,ps,sup); };
confirmation(300,200,m,f);

}

function stockRForm(sino,pr,pc,pn,ps,sup) {

var retqty = $.trim($('#retqty').val());
var retnote = $.trim($('#retnote').val());

$.ajax({
	url: 'stock-return-ajax.php?p=add',
	type: 'post',
	data: {psino: sino, prsup: sup, pretref: pr, pretcode: pc, pretname: pn, pretsize: ps, pretqty: retqty, pretnote: retnote},
	success: function(data, status) {	
	closeSubDialog();
	closeMainDialog();
	stock(2);
	notify(300,200,data);
	}
});

}

function viewStock(sid,refno) {

window.open('view-stock.php?sid=' + sid + '&refno=' + refno, '', 'width=1024px, scrollbars=yes, toolbar=no, menubar=yes');
uncheckSelected(sid);

}

function avonCats() {

var params = [
	'width=800px',
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'	
].join(',');
window.open('avon-categories.php', '', params);

}

function offsets() {

var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'
].join(',');
window.open('offsets.php', '', params);

}

function returnOffsets() {

}

function stockReplace(siid) {

var sino = siid;
var sup = 0;
var pr = '';
var pc = '';
var pn = '';
var ps = '';
var retqty = 0;
var retnote = 'Swapped with other stock';

var t = 'Replace Stock';
var inForm = 'forms/stock-replace.php';
var f = function() {
if (validateForm('subModule')) confirmSwapStock(sino,sup,pr,pc,pn,ps,retqty,retnote);
};

var exe = function() {
	
	$.ajax({
	dataType: 'json',
	async: false,
	url: 'stock-in.php?p=info_swap_stock&siid=' + siid,
	success: function(data, status) {
			 var d = data.stockinfo[0];
			 $('#srepcode').val(d.jssc);
			 $('#srepname').val(d.jssn);
			 $('#srepsize').val(d.jsss);
			 $('#srepqty').val(d.jssq);
			 $('#repsname').val(d.jssn);
			 sup = d.jssup;
			 pr = d.jssr;
			 pc = d.jssc;
			 pn = d.jssn;
			 ps = d.jsss;
			 retqty = d.jssq;			 
			 }
	});	
	
};

subDialog(1150,400,t,inForm,exe);
subDialogB('Ok','Cancel',f);

}

function confirmSwapStock(sino,sup,pr,pc,pn,ps,retqty,retnote) {

var m = 'Replace this stock?';
var f = function() { swapStock(sino,sup,pr,pc,pn,ps,retqty,retnote); };
confirmation(300,200,m,f);

}

function swapStock(sino,sup,pr,pc,pn,ps,retqty,retnote) {

// return product first
$.ajax({
	url: 'stock-return-ajax.php?p=add',
	type: 'post',
	data: {psino: sino, prsup: sup, pretref: pr, pretcode: pc, pretname: pn, pretsize: ps, pretqty: retqty, pretnote: retnote},
	success: function(data, status) {	

	}
});

pc = $('#repscode').val();
ps = $('#repssize').val();
retqty = $('#repsqty').val();

$.ajax({
	url: 'stock-in.php?p=add_swapped_stock',
	type: 'post',
	data: {psino: sino, pretref: pr, pretcode: pc, pretname: pn, pretsize: ps, pretqty: retqty},
	success: function(data, status) {	
		closeSubDialog();
		closeMainDialog();
		stock(2);
		notify(300,200,data);
	}
});

}

function stockMOP(m) {

var im = '';

switch (parseInt(m)) {

case 0:
im = '&nbsp;Dute Date: <input type="text" id="stock-dd" />';
setTimeout(function() {
$( "#stock-dd" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});
},500);
break;

case 1:

break;

}

$('.stock-mop').html(im);

}

function chkCompanyBranch(sbr, cbr, cfn) {

var br = 'Francey';
var cbranch = 'Francey';
if (parseInt(cbr) == 2) br = 'Yessamin';
if (parseInt(sbr) == 2) cbranch = 'Yessamin';

var f = function() { closeMainDialog(); };
if (sbr != cbr) {

if (parseInt(sbr) == 0) return;
notify(500,250,'Hoy Ate ' + cfn + ', wrong account ka. Nasa ' + br + ' ka tapos ang stock(s) na iencode mo para sa ' + cbranch + '. Logout ka muna :D',f);

}

}

function pullOutCheck(sid) {

uncheckMulti('frmContent');
var t = 'Pull-out Cheque';
var inForm = 'forms/pullout.php';
var id = 0;
var f = function() {
if ($('#pullout-loan').prop('checked')) {
	if (validateForm('frmModule')) confirmPullOutCheque(sid);
} else {
	confirmPullOutCheque(sid);
}
};

var exe = function() {

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_loans',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.loanlist = data;
		 $('input#loan-desc').jsonSuggest({data: suggestList.loanlist, minCharacters: 2, onSelect: function(item){ $('#loan-id').val(item.id); }});
		 }
});

$.ajax({
url: 'stock-in.php?p=edit_pullout_cheque&gsid=' + sid,
dataType: 'json',
success: function(data, status) {
	var d = data.editpullout[0];
	$('#loan-id').val(d.jslid);
	$('#loan-desc').val(d.jsloan);
	if (parseInt(d.jslid) != 0) pulloutPayment('pullout-loan');
	else pulloutPayment('pullout-cash');
}
});

};

mainDialog(400,220,t,inForm,exe);
mainDialogB('Update','Cancel',f);

}

function confirmPullOutCheque(sid) {

var m = 'Are you sure you want to pull-out cheque for this stock?';
var f = function() {

var lid = $('#loan-id').val();

$.ajax({
	url: 'stock-in.php?p=pullout_cheque',
	type: 'post',
	data: {psid: sid, plid: lid},
	success: function(data, status) {
		var f = function() { closeMainDialog(); }	
		notify(350,200,data,f);
	}
});

};
confirmation(350,200,m,f);

}

function loans() {

var params = [
	'width='+screen.width,
	'height='+screen.height,
	'scrollbars=yes',
	'toolbar=no',
	'menubar=yes'
].join(',');
window.open('loans.php', '', params);

}

function showOffsetBal(cid) {

$.ajax({
	url: 'stock-in.php?p=offset_balance',
	type: 'post',
	data: {pcid: cid},
	success: function(data, status) {
		$('#stock-off-bal').val(data);
	}
});

}

function updateOffBal() {

var off_amt = $('#stock-off-bal').val();
var stock_off = $('#stock-off').val();
var lbal = 0;

lbal = parseFloat(off_amt) - parseFloat(stock_off);
lbal = lbal.toFixed(2);
$('#stock-off-bal').val(lbal);
if ($('#stock-off-bal').val() == 'NaN') {
$('#stock-off-bal').val('0');
$('#stock-off').val('0');
}
totalAmt();

}

function fillMemNo(mn) {

$.ajax({
	url: 'stock-in.php?p=fill_mem_no',
	type: 'post',
	data: {pmemn: mn},
	success: function(data, status) {
		$('#smn').val(data);
		filterOffsetID(data);
	}
});

}

function pulloutPayment(id) {

$('#pullout-cash').prop('checked',false);
$('#pullout-loan').prop('checked',false);
$('#' + id).prop('checked',true);

var loan = $('#pullout-loan').prop('checked');

if (loan) {
	$('#loan-desc').prop('disabled',false);
	$('#loan-id').prop('disabled',false);
} else {
	$('#loan-desc').prop('disabled',true);
	$('#loan-desc').val('');
	$('#loan-id').prop('disabled',true);
	$('#loan-id').val(0);
}

}

function clickStockItem(stock) {
	 $('#pcode').val(stock.dataset.pcode);
	 $('#pname').val(stock.dataset.pname);
	 $('#psize').val(stock.dataset.psize);
	 $('#sprice').val(stock.dataset.sprice);
	 $('#houtd').val(stock.dataset.houtd);
	 // $('#pcode').prop('disabled',true);
	 $('#pname').prop('disabled',true);
	 $('#psize').prop('disabled',true);		
}

function newStockItem() {
	$('#tab-stock-in .autocomplete-own div').html('');
}