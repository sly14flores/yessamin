function clearForm(frm) {

var f = $('#' + frm)[0];
var e = f.elements;
var id = '';

for (i=0; i<e.length; ++i) {
	if ((e[i].type == 'text') || (e[i].type == 'password')) {
		id = document.getElementById(e[i].name);
		if (id.className != 'iclear') $(id).val('');
	}
}

}

function validateForm(frm) {

var f = $('#' + frm)[0];
var e = f.elements;
var c = '';
var id = '';
var r = true;
var p1 = '';
var p2 = '';
var pid = '';
var pid1 = '';
var pid2 = '';
var pc1 = '';
var pc2 = '';

$('.validate').html('');
$('.password').html('');

for (i=0; i<e.length; ++i) {
	if ((e[i].type == 'text') || (e[i].type == 'password')) {
		id = document.getElementById(e[i].name);
		c = $(id).val();
		
		if (c == '') {
			if (id.className != 'iclear') {
			$(id).css('border', '2px groove #dd4b39');
			r = false;
			$('.validate').html('All field(s) are required.');
			}		
		} else {
		$(id).css('border','');
		}
		
	}
	if (e[i].type == 'password') {
		pid = document.getElementById(e[i].name);
		if (pid.name == 'password') {
		pc1 = $(pid).val();
		pid1 = pid;
		}
		if (pid.name == 'ppassword') {
		pc2 = $(pid).val();
		pid2 = pid;
		}
	}
}

if ((pid1 != '') && (pid2 != '')) {
	if ((pc1 != '') && (pc2 != '')) {
	if (pc1 != pc2) { r = false; $('.password').html('Password does not match.'); }
	} 
}

return r;

}

function Check_all(theForm, theParentCheck){
	elem = theForm.elements;
		
	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			elem[i].checked	= theParentCheck.checked;
		}
	}
}

function Uncheck_Parent(ParentCheckboxName, me){
	var theParentCheckbox = document.getElementById(ParentCheckboxName);
	
	if(!me.checked && theParentCheckbox.checked){
		theParentCheckbox.checked = false;		
	}
}

function uncheckSelected(id) {

	$('#chk_' + id).prop('checked',false);

}

function uncheckMulti(frm) {

	var f = $('#' + frm)[0];
	var e = f.elements;

	for (i=0; i<e.length; ++i) {
		if (e[i].type == "checkbox") {
			if (e[i].checked) e[i].checked = false;
		}
	}

}

function getCheckedId(theFormName){
var theForm		= document.getElementById(theFormName);
var	elem		= theForm.elements;
var tmp_arr, rec_id;

	rec_id	= "";

	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			if (elem[i].checked && elem[i].name != 'chk_checkall'){
				tmp_arr	= elem[i].name.split('_');
				rec_id	+= tmp_arr[1] + ',';
			}
		}
	}

	if (rec_id.length > 0){
		rec_id = rec_id.substr(0, rec_id.length-1);
	}
	return rec_id;
}

function count_checks(theFormName){
var theForm		= document.getElementById(theFormName);
var	elem		= theForm.elements;
var int_count	= 0;
		
	for(i=0; i<elem.length; ++i){
		if(elem[i].type == "checkbox"){
			if (elem[i].checked  && elem[i].name != 'chk_checkall') ++int_count;
		}
	}
	
	return int_count;
}

function highl() {

$('.highlight').click(function() {
	$(this).select();
});

}

function chkRow(row) {

var gi = row.getElementsByTagName('input')[0];
gi.checked = !gi.checked;

}

function roundToTwo(value) { // round off float to 2 decimal places
    return(Math.round(value * 100) / 100);
}

function adjConH() {

var h = $('#content').height();
if (h < 450) $('#content').css('height','450px');
else $('#content').css('height','');

}