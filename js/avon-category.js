function avonCat(src,id) {

var t = (src == 1) ? 'Add Category' : 'Edit Category';
var inForm = 'forms/avon-category.php';

var f = function() {

var cn = $('#cat-name').val();
if (cn == '') $('.validate').html('Please enter category name.');
else confirmAvonCat(id,src);

};

if (src == 1) {

mainDialog(300,175,t,inForm);
mainDialogB('Add','Close',f);

} else {

var exe = function() {

$.ajax({
	url: 'avon-categories-ajax.php?p=edit&cid=' + id,
	dataType: 'json',
	success: function(data, status) {
		
		var d = data.editavoncat[0];		
		$('#main-cat').val(d.jmc);
		$('#cat-name').val(d.jav);
		
	}
});

};

mainDialog(300,175,t,inForm,exe);
mainDialogB('Update','Close',f);

}

}

function confirmAvonCat(id,src) {

var m = (src == 1) ? 'Add this category?' : 'Update category info?';
var f = function() { avonCatForm(id,src); };
confirmation(300,200,m,f);

}

function avonCatForm(id,src) {

var mc = $('#main-cat').val();
var cn = $('#cat-name').val();
cn = cn.replace(/\&amp;/g,'&');

switch(src) {

case 1:
$.ajax({
	url: 'avon-categories-ajax.php?p=add',
	type: 'post',
	data: {pmc: mc, pcn: cn},
	success: function(data, status) {
		var f = function() { content(12,0); avonCat(1,0); };
		notify(350,180,data,f);
	}
});
break;

case 2:
$.ajax({
	url: 'avon-categories-ajax.php?p=update',
	type: 'post',
	data: {pcid: id, pmc: mc, pcn: cn},
	success: function(data, status) {
		closeMainDialog();
		var f = function() { content(12,0); };
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

case 12: // avon categories
$.ajax({
	url: 'avon-categories-ajax.php?p=contents' + page + par,
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

function filterAvonCat() {

var par = '';

var cn = $('#fcat-name').val();

par = '&cn=' + cn;

content(12,0,par);

}

function rAvonCatF() {

var par = '';

var cn = $('#fcat-name').val();

par = '&cn=' + cn;

return par;

}

function delCat(id) {

var m = 'Are you sure you want to delete category?';
var f = function() { deleteCat(id); };
confirmation(380,200,m,f);
		
}

function deleteCat(id) {

$.ajax({
	url: 'avon-categories-ajax.php?p=delete',
	type: 'post',
	data: {cid: id},
	success: function(data, status) {
		var f = function() { content(12,2); }
		notify(300,180,data,f);
	}
});

}