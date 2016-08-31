/**************
** Dialog Boxes
**************/
function mainDialog() { // width,height,title,content
    
    var args = mainDialog.arguments;
    
    var w = args[0]; // width
    var h = args[1]; // height
    var t = args[2]; // title
    var c = args[3]; // content
	
	if (args.length > 4) var f = args[4];
	
    $('#main_dialog').dialog({
	autoOpen: false,
	modal: true,
	draggable: true,
	resizable: false,
	closeOnEscape: true,
	width: w,
	height: h,
    title: t
    });    
    
    $('#main_dialog').dialog('open').load(c, function() { f(); } );
    
}

function mainDialogB() {

var args = mainDialogB.arguments;
var b1 = args[0];
var b2 = args[1];
var f1 = args[2];

if (args.length == 2) {

	$('#main_dialog').dialog('option', 'buttons', [
	   {
	   text: b1,
	   click: function() { $(this).dialog('destroy'); b2(); }
	   }
	]);

} else {

	if (args.length > 3) var f2 = args[3];

	$('#main_dialog').dialog('option', 'buttons', [
	   {
	   text: b1,
	   click: function() { f1(); }
	   },
	   {
	   text: b2,
	   click: function() { $(this).dialog('destroy'); if (args.length > 3) f2(); }
	   }
	]);
	
}	

}

function closeMainDialog() {
	
	$('#main_dialog').dialog('destroy');
	
}

function subDialog() { // width,height,title,content
    
    var args = subDialog.arguments;
    
    var w = args[0]; // width
    var h = args[1]; // height
    var t = args[2]; // title
    var c = args[3]; // content

	if (args.length > 4) var f = args[4];	
    
    $('#sub_dialog').dialog({
	autoOpen: false,
	modal: true,
	draggable: true,
	resizable: false,
	closeOnEscape: true,
	width: w,
	height: h,
    title: t
    });    
    
    $('#sub_dialog').dialog('open').load(c, function() { f(); });
    
}

function subDialogB() {

var args = subDialogB.arguments;
var b1 = args[0];
var b2 = args[1];
var f1 = args[2];

if (args.length == 2) {

	$('#sub_dialog').dialog('option', 'buttons', [
	   {
	   text: b1,
	   click: function() { $(this).dialog('destroy'); b2(); }
	   }
	]);

} else {

	if (args.length > 3) var f2 = args[3];

	$('#sub_dialog').dialog('option', 'buttons', [
	   {
	   text: b1,
	   click: function() { f1(); }
	   },
	   {
	   text: b2,
	   click: function() { $(this).dialog('destroy'); if (args.length > 3) f2(); }
	   }
	]);
	
}

}

function closeSubDialog() {
	
	$('#sub_dialog').dialog('destroy');
	
}

function confirmation() {

    var args = confirmation.arguments;
    
    var w = args[0]; // width
    var h = args[1]; // height
    var c = args[2]; // content
	var f1 = args[3]; // function arg
    var note = '<img src="images/confirm.png" />' + ' ' + c;

	if (args.length > 4) var f2 = args[4];
	
	$('#confirm_dialog').dialog({
		autoOpen: false,
		modal: true,
		draggable: false,
		resizable: false,
		closeOnEscape: true,
		width: w,
		height: h,
		title: 'Confirmation'
	});

    $('#confirm_dialog').dialog('open').html(note);
    $('#confirm_dialog').dialog('option', 'buttons', [
       {
       text: "Yes",
       click: function() { f1(); $(this).dialog('destroy'); }
       },
       {
       text: "No",
       click: function() { $(this).dialog('destroy'); if (args.length > 4) f2(); }
       }	   
    ]);	
	
}

function notify() { // width,height,content,function

    var args = notify.arguments;
    
    var w = args[0]; // width
    var h = args[1]; // height
    var c = args[2]; // content
	var f = args[3]; // optional function arg
    var note = '<img src="images/info.png" />' + ' ' + c;
    
    $('#notify_dialog').dialog({
	autoOpen: false,
	modal: true,
	draggable: false,
	resizable: false,
	closeOnEscape: true,
	width: w,
	height: h,
    title: 'Notification'
    });    
    
    $('#notify_dialog').dialog('open').html(note);
    $('#notify_dialog').dialog('option', 'buttons', [
       {
       text: "Ok",
       click: function() {$(this).dialog('destroy'); }
       }
    ]);    

	if (args.length > 3) f();
    
}
/******************
** End Dialog Boxes
******************/