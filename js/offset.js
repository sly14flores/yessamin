function returnOffsets(src,id,mo) {

var t = (src == 1) ? 'Add Offset' : 'Edit Offset';
var inForm = 'forms/offset.php';

var exe = function() {

$.ajax({
	url: 'offsets-ajax.php?p=last_offset_id',
	type: 'get',
	success: function(data, status) {
		$('#offset-id').val(data);
	}
});

/*$('#offset-date').datepicker({
showOn: "button",
buttonImage: "images/calendar.gif",
buttonImageOnly: true
});*/


$('#offset-item-date').datepicker({
showOn: "button",
buttonImage: "images/calendar.gif",
buttonImageOnly: true
});

$('#offset-item-add').button();
$('#offset-item-add').click(function() { addOffsetI(); });

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_suppliers',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#offset-co').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#offset-coid').val(item.id); $('#offset-cid').val(item.text + '-' + $('#offset-id').val()); }});            
		 }
});

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_members',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#offset-mn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
		 }
});

};

var f = function() {

var od = $('#offset-date').val();
var oa = $('#offset-amt').val();
if ( (od == '') || (oa == '') ) $('.validate').html('Please enter offset date.');
else confirmOffset(id,src);

};

if (src == 1) {

mainDialog(700,500,t,inForm,exe);
mainDialogB('Add','Close',f);

} else {

exe = function() {

$('#offset-date').datepicker({
showOn: "button",
buttonImage: "images/calendar.gif",
buttonImageOnly: true
});

$.ajax({
	url: 'offsets-ajax.php?p=edit&oid=' + id + '&mo=' + mo,
	dataType: 'json',
	success: function(data, status) {
		var d = data.editoffset[0];
		var isAdmin = d.jadmin;
		$('#offset-id').val(id);
		$('#offset-co').val(d.jco);
		$('#offset-cid').val(d.jcid);
		$('#offset-date').val(d.jod);
		$('#offset-mn').val(d.jmn);
		$('.offset-bal').html(d.jbal);
		$('#hoid').val(id);
		
		var inRow;
		var delr;
		$.each(data.offsetitems, function(i,d){
		
		inRow = '<tr id="oi-' + d.joffiid + '">';
		inRow += '<td class="odate-' + d.joffiid + '">' + d.joffd  + '</td>';
		inRow += '<td class="ono-' + d.joffiid + '">' + d.joffno + '</td>';	
		inRow += '<td><input class="oamt-' + d.joffiid  + '" type="text" size="10" value="' + d.joffamt  + '" /></td>';		
		delr = '<td><a href="javascript: delOffsetI(' + d.joffiid + ',2);"><img src="images/delete.png" /></a></td>';	
		if (isAdmin == 1) inRow += delr;
		else inRow += '<td>&nbsp;</td>';
		inRow += '</tr>';	

		$(inRow).appendTo('#tab-offset-item tbody');
		
		var h = $('#frmOffsetItem').height();
		if (h >= 100) $('#frmOffsetItem').addClass('fixh');		
		
		$('#loffiid').val(d.joffiid);
		$('#hic').val(d.joffiid);
		offsetTotal();
		
		});
	}
});

/*
$.ajax({
type: 'json',
url: 'stock-in.php?p=list_suppliers',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.supplierlist = data;
		 $('input#offset-co').jsonSuggest({data: suggestList.supplierlist, minCharacters: 2, onSelect: function(item){ $('#offset-coid').val(item.id); }});            
		 }
});
*/

$.ajax({
type: 'json',
url: 'stock-in.php?p=list_members',
success: function(data, status) {
		 var suggestList = {};
		 suggestList.productlist = data;
		 $('input#offset-mn').jsonSuggest({data: suggestList.productlist, minCharacters: 2, onSelect: function(item){ }});            
		 }
});

$('#offset-co').prop('disabled',true);

$('#offset-item-date').datepicker({
showOn: "button",
buttonImage: "images/calendar.gif",
buttonImageOnly: true
});

$('#offset-item-add').button();
$('#offset-item-add').click(function() { addOffsetI(); });

};

mainDialog(700,500,t,inForm,exe);
mainDialogB('Update','Close',f);

}

}

function confirmOffset(id,src) {

var m = (src == 1) ? 'Add this offset?' : 'Update offset info?';
var f = function() { offsetForm(id,src); };
confirmation(300,200,m,f);

}

function offsetForm(id,src) {

var nr = $("#tab-offset-item tbody tr").size();

if (parseInt(nr) == 0) {
	notify(300,200,'Plase add item(s).');
	return;
}

var coid = $('#offset-coid').val(); // company id
var mn = $('#offset-mn').val();
var cid = $('#offset-cid').val(); // company-prefixed id
var od = $('#offset-date').val();
var offi = '';

switch(src) {

case 1:

$('#tab-offset-item tbody').children('tr').each(function() {

var oi = this.id.split("-");
var odate = $('.odate-' + oi[1]).html();
var oamt = $('.oamt-' + oi[1]).html();
offi += odate + ',' + oamt + '|';

});

$.ajax({
	url: 'offsets-ajax.php?p=add',
	type: 'post',
	data: {pcoid: coid, pcid: cid, pod: od, pmn: mn, poffi: offi, pnr: nr},
	success: function(data, status) {
		var f = function() { returnOffsets(1); content(13,0); };
		notify(350,180,data,f);
	}
});
break;

case 2:

var loffiid = $('#loffiid').val();
var doiids = $('#del-oiids').val();

$('#tab-offset-item tbody').children('tr').each(function() {

var oi = this.id.split("-");
var odate = $('.odate-' + oi[1]).html();
var oamt = $('.oamt-' + oi[1]).val();
offi += odate + ',' + oamt + ',' + oi[1] + '|';

});

$.ajax({
	url: 'offsets-ajax.php?p=update',
	type: 'post',
	data: {poid: id, pod: od, pmn: mn, poffi: offi, pnr: nr, ploffiid: loffiid, pdoiids: doiids},
	success: function(data, status) {
		closeMainDialog();
		var f = function() { content(13,0); };
		notify(350,180,data,f);
	}
});
break;

}

}

function content() {

var args = content.arguments;
var src = args[0];
var dir = args[1];
var par = '';

if (args.length > 2) par = args[2];

var ld = '<div class="content-loading" style="text-align: center;"><img src="images/ajax-loader.gif" /><p>Loading...</p></div>';
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

case 13:
$.ajax({
	url: 'offsets-ajax.php?p=contents' + page + par,
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

function filterOffsets() {

var par = '';

var oid = $('#foffset-id').val();
var co = $('#foffset-coid').val();
var mn = $('#foffset-mn').val();
var od = $('#foffset-date').val();

var chk = $('#foffset-co').val();
if (chk == '') co = 0;

par = '&foid=' + oid + '&fco=' + co + '&fmn=' + mn + '&fod=' + od;

content(13,0,par);

}

function rOffsetsF() {

var par = '';

var oid = $('#foffset-id').val();
var co = $('#foffset-coid').val();
var mn = $('#foffset-mn').val();
var od = $('#foffset-date').val();

var chk = $('#foffset-co').val();
if (chk == '') co = 0;

par = '&foid=' + oid + '&fco=' + co + '&fmn=' + mn + '&fod=' + od;

return par;

}

function delOffset(id) {

var m = 'Are you sure you want to delete this offset?';
var f = function() { offsetDelete(id); };
confirmation(380,200,m,f);
		
}

function offsetDelete(id) {

$.ajax({
	url: 'offsets-ajax.php?p=delete',
	type: 'post',
	data: {oid: id},
	success: function(data, status) {
		var f = function() { content(13,2); }
		notify(300,180,data,f);
	}
});

}

function addOffsetI() {

$('.validate').html('');
var ic = $('#hic').val();
var oid = $('#offset-item-date').val();
var oia = $('#offset-item-amount').val();

if ( (oid != '') && (oia != '') ) {

	var h = $('#frmOffsetItem').height();
	if (h >= 100) $('#frmOffsetItem').addClass('fixh');
	
	var inRow;
	ic = parseInt(ic) + 1;
	
	inRow = '<tr id="oi-' + ic + '">';
	inRow += '<td class="odate-' + ic + '">' + oid  + '</td>';
	inRow += '<td class="ono-' + ic + '">&nbsp;</td>';	
	inRow += '<td><input class="oamt-' + ic  + '" type="text" size="10" value="' + oia  + '" /></td>';
	inRow += '<td><a href="javascript: delOffsetI(' + ic + ',1);"><img src="images/delete.png" /></a></td>';	
	inRow += '</tr>';	

	$(inRow).appendTo('#tab-offset-item tbody');
	$('#hic').val(ic);
	offsetTotal();
	
$('#offset-item-date').val('');
$('#offset-item-amount').val('');
	
} else {
	$('.validate').html('Please fill up; date, amount.');
}

}

function offsetTotal() {

var offs = 0;
var toffs = 0;

$('#tab-offset-item tbody').children('tr').each(function() {

var oi = this.id.split("-");
offs = $.trim($('.oamt-' + oi[1]).val());
toffs = toffs + parseFloat(offs);
});

$('.offset-total').html(toffs.toFixed(2));

}

function delOffsetI(item,src) {
	var hic = $('#hic').val();
	$('#oi-' + item).remove();
	if (src == 1) {
		if (parseInt(item) == parseInt(hic)) $('#hic').val(parseInt(item) -1);
	}
	if (src == 2) {
		var ids = $('#del-oiids').val();
		ids += item + ',';
		$('#del-oiids').val(ids);
	}
offsetTotal();	
}

function offsetPerMonth() {

var mo = $('#sel-month-total').val();
var moyr = $('#moyr').val();
var hoid = $('#hoid').val();

closeMainDialog();
returnOffsets(2,hoid,moyr + '-' + mo);
setTimeout(function() {
$('#sel-month-total').val(mo);
$('#moyr').val(moyr);
},100);

}

function viewRefNo(cid) {

$.ajax({
	url: 'offsets-ajax.php?p=view_ref_no',
	type: 'post',
	data: {pcid: cid},
	success: function(data, status) {
		var t = cid + ' - Reference No(s)';
		var inForm = 'forms/offset-refno.php';
		var exe = function() {
			$('#tab-refno tbody').html(data);
		};
		mainDialog(800,620,t,inForm,exe);
		mainDialogB('Close',null);
	}
});

}