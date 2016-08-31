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

case 9: // manual remittance
$.ajax({
	url: 'manual-remittances-ajax.php?p=contents' + page + par,
	type: 'post',
	success: function(data, status) {
	//var sdata = data.split('|');
	//$('#in-content').html(sdata[0]);
	$('#in-content').html(data);
	//last_page = parseInt(sdata[1]);
	adjConH();
	}
});
break;

}

}

function manual_remittance(src) {

var t = (src == 1) ? 'Add Manual Remittance' : 'Edit Manual Remittance';
var inForm = 'forms/manual-remittance.php';
var id = 0;
var exe = function() {
/*
$( "#mr-date" ).datepicker({
	showOn: "button",
	buttonImage: "images/calendar.gif",
	buttonImageOnly: true
});
*/
$.ajax({
type: 'json',
url: 'transaction.php?p=list_dealers&ftb=2',
success: function(data, status) {
		var suggestList = {};
		suggestList.dealerlist = data;
		$('input#mr-dealer').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
			$('#mr-dealer').val(item.dealer);
			$('#mr-did').val(item.id);
		}});				 
		}
});

};
var f = function() {
if (validateForm('frmModule')) confirmManualRemittance(id,src);
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
			url: 'manual-remittances-ajax.php?p=edit&mrid=' + id,
			dataType: 'json',
			success: function(data, status) {
				
				var d = data.editmr[0];
				$('#mr-date').val(d.jmrd);
				$('#mr-dealer').val(d.jmrdn);
				$('#mr-did').val(d.jmrdid);
				$('#mr-amount').val(d.jmramt);
				$('#mr-note').val(d.jmrnote);				
				/*
				$( "#mr-date" ).datepicker({
					showOn: "button",
					buttonImage: "images/calendar.gif",
					buttonImageOnly: true
				});				
				*/
				$.ajax({
				type: 'json',
				url: 'transaction.php?p=list_dealers&ftb=2',
				success: function(data, status) {
						var suggestList = {};
						suggestList.dealerlist = data;
						$('input#mr-dealer').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
							$('#mr-dealer').val(item.dealer);
							$('#mr-did').val(item.id);
						}});				 
						}
				});				
				
			}
		});		
	
		}	
		mainDialog(450,250,t,inForm,exe);
		mainDialogB('Update','Close',f,f2);
	
	}
} else {
mainDialog(450,250,t,inForm,exe);
mainDialogB('Add','Close',f);
}

}

function confirmManualRemittance(id,src) {

var m = 'Add this payment?';
var f = function() { manualRemittanceForm(id,src); };
confirmation(300,200,m,f);

}

function manualRemittanceForm(id,src) {

var mrdate = $('#mr-date').val();
var mrdealer = $('#mr-dealer').val();
var mrdid = $('#mr-did').val();
var mramnt = $('#mr-amount').val();
var mrnote = $('#mr-note').val();

switch (src) {

case 1:

$.ajax({
	url: 'manual-remittances-ajax.php?p=add',
	type: 'post',
	data: {pmrdate: mrdate, pmrdid: mrdid, pmramnt: mramnt, pmrnote: mrnote},
	success: function(data, status) {
	clearForm('frmModule');
	var f = function() { content(9,2,rManualRemittanceF()); manual_remittance(1); }
	notify(300,200,data,f);
	}
});

break;

case 2:

$.ajax({
	url: 'manual-remittances-ajax.php?p=update&mrid=' + id,
	type: 'post',
	data: {pmrdate: mrdate, pmrdid: mrdid, pmramnt: mramnt, pmrnote: mrnote},
	success: function(data, status) {
	clearForm('frmModule');
	closeMainDialog();	
	var f = function() { content(9,2,rManualRemittanceF()); }
	notify(320,200,data,f);
	}
});

break;

}

}

function filterManualRemittance() {

var par = '';

var sel_co = $('#sel-cutoff').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var fcustomer = $.trim($('#fcustomer').val());

par = '&selco=' + sel_co + '&fs=' + fs + '&fe=' + fe + '&fcustomer=' + fcustomer;

content(9,0,par);

}

function rManualRemittanceF() {

var par = '';

var sel_co = $('#sel-cutoff').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var fcustomer = $.trim($('#fcustomer').val());

par = '&selco=' + sel_co + '&fs=' + fs + '&fe=' + fe + '&fcustomer=' + fcustomer;

return par;

}

function confirmMRDelete() {

	if (count_checks('frmContent') == 0) {
		notify(300,200,'Please select one.');
	} else if (count_checks('frmContent') > 1) {
		notify(450,200,'Please select only one.');
	} else {
		id = getCheckedId('frmContent');
		var m = 'Are you sure you want to delete this manual remittance?';
		var f = function() { deleteMR(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(380,180,m,f,f2);
	}

}

function deleteMR(id) {

	$.ajax({
		url: 'manual-remittances-ajax.php?p=delete',
		type: 'post',
		data: {mrid: id},
		success: function(data, status) {
			var f = function() { content(9,2); }
			notify(300,200,data,f);
		}
	});

}

function confirmCutoff() {

var ccoh = $('#frmContent')[0];
var co = $('#hco').val();
co = parseInt(co);

if ( (ccoh == 0) || (ccoh == undefined) ) {
notify(300,180,'No manual remittance for this date.');
return;
}

var m = 'Process first cut-off?';
if (co == 2) m = 'Process end of the day?';
var f = function() { cutOff(co); };
confirmation(300,200,m,f);

}

function cutOff(co) {

var opt = 'first_cutoff';
if (co == 2) opt = 'end_of_the_day';

	$.ajax({
		url: 'manual-remittances-ajax.php?p=' + opt,
		type: 'get',
		success: function(data, status) {
			var f = function() { content(9,2); }
			notify(300,200,data,f);
		}
	});

}

function updateActualCash(co,d) {
var fc = $('#acash_fc').val();
var eod = $('#acash_eod').val();

var ac = 0;
if (co == 1) ac = fc;
if (co == 2) ac = eod;

$.ajax({
	url: 'manual-remittances-ajax.php?p=actual_cash&fdate=' + d + '&gco=' + co,
	type: 'post',
	data: {pac: ac},
	success: function(data, status) {
		filterManualRemittance();
	}
});

}

function deductionM(src) {

var t = (src == 1) ? 'Add Deduction' : 'Edit Deduction';
var inForm = 'forms/deduction-manual.php';
var id = 0;
var f = function() {
if (validateForm('frmModule')) confirmDeductionM(id,src);
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
		url: 'manual-remittances-ajax.php?p=edit_deduction&dedid=' + id,
		dataType: 'json',
		success: function(data, status) {
			var d = data.editded[0];
			$('#ddescm').val(d.jdd);
			$('#damtm').val(d.jda);
			$('#dnotem').val(d.jdn);
			$('#dedm-cat').val(d.jdc);
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

function confirmDeductionM(id,src) {

var m = (src == 1) ? 'Add this deduction?' : 'Update deduction\'s info?';
var f = function() { deductionFormM(id,src); };
confirmation(300,200,m,f);

}

function deductionFormM(id,src) {

var dcat = $('#dedm-cat').val();
var ddesc = $('#ddescm').val();
var damt = $('#damtm').val();
var dnote = $('#dnotem').val();

switch (src) {

case 1:

$.ajax({
	url: 'manual-remittances-ajax.php?p=add_deduction',
	type: 'post',
	data: {pdcat: dcat, pddesc: ddesc, pdamt: damt, pdnote: dnote},
	success: function(data, status) {
	clearForm('frmModule');
	$('#dnote').val('');
	var f = function() { content(9,2,rManualRemittanceF()); }
	notify(300,200,data,f);
	}
});

break;

case 2:

$.ajax({
	url: 'manual-remittances-ajax.php?p=update_deduction&dedid=' + id,
	type: 'post',
	data: {pdcat: dcat, pddesc: ddesc, pdamt: damt, pdnote: dnote},
	success: function(data, status) {
	closeMainDialog();	
	var f = function() { content(9,2,rManualRemittanceF()); }
	notify(320,200,data,f);
	}
});

break;

}

}

function confirmMDeductionDelete() {

	if (count_checks('frmContent2') == 0) {
		notify(300,200,'Please select one.');
	} else {
		id = getCheckedId('frmContent2');
		var m = 'Delete this deduction(s)?';
		var f = function() { deleteMDeduction(id); };
		var f2 = function() { uncheckMulti('frmContent'); }
		confirmation(350,150,m,f,f2);
	}

}

function deleteMDeduction(id) {

	$.ajax({
		url: 'manual-remittances-ajax.php?p=delete_deduction',
		type: 'post',
		data: {dedid: id},
		success: function(data, status) {
			var f = function() { content(9,2,rManualRemittanceF()); }
			notify(300,200,data,f);
		}
	});

}

function printMRemittance() {

var sel_co = $('#sel-cutoff').val();
var fs = $('#fs').val();
var fe = $('#fe').val();
var fcustomer = $.trim($('#fcustomer').val());
if (fs == '') {
	notify(300,150,'Please select start date.');
	return;
}
window.open('reports/manual-remittance.php?selco=' + sel_co + '&fs=' + fs + '&fe=' + fe + '&fcustomer=' + fcustomer, '', 'width=800px, scrollbars=yes, toolbar=no, menubar=yes');

}