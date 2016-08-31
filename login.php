<?php

$m = "";
$chkm = (isset($_GET['m'])) ? $_GET['m'] : 0;
if ($chkm == 1) $m = "Username or password incorrect.";

$branch = 2;

switch ($branch) {

case 1:
$title = "Francey Boutique & Gen. MSDE";
$logo = "<h1>Francey <span>Boutique</span> <em>&amp;</em> <span>Gen. MSDE</span></h1>";
break;

case 2:
$title = "Yessamin Boutique & Gen. MSDE";
$logo = "<h1>Yessamin <span>Boutique</span> <em>&amp;</em> <span>Gen. MSDE</span></h1>";
break;

case 3:
$title = "Francey Boutique & Gen. MSDE Branch 1";
$logo = "<h1>Francey <span>Boutique</span> <em>&amp;</em> <span>Gen. MSDE</span> Branch 1</h1>";
break;

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Login - <?php echo $title; ?></title>
<style type="text/css">
@import url(css/clearfix.css) screen;

* {
	margin: 0;
	padding: 0;
}

body {
	font: 1em sans-serif;
}

#logo {
	width: 60%;
	margin: 5% auto 0 auto;
	text-align: center;
}

#logo h1 {
	color: #e83cb0;
	font-family: Verdana, san-serif;
	padding: 20px 0 8px 0;
	border-bottom: 1px solid #4c8efa;
}

#logo h1 em {
	color: #fe8576;
	font-size: .8em;
}

#logo h1 span {
	color: #4c8efa;
}

#logo h2 {
	margin-top: 15px;
	color: #fb7261;
}

#login-form {
	width: 315px;
	background-color: #f1f1f1;
	margin: 10px auto 0 auto;
}

#frmSignIn {
	border: 1px solid #e5e5e5;
	padding: 5px 15px 15px 15px;
}

#tab-login {
	border-collapse: collapse;
	width: 100%;
}

#tab-login td {
	padding: 5px;
}

#tab-login tr:first-child td {
	padding-bottom: 10px;
	font-variant: small-caps;
}


#tab-login input {
	font-size: 1.1em;
}

#tab-login span {
	font-weight: bold;
}

.login-message {
	color: #dd4b39;
}

#submit-login {
	width: 58px;
	height: 33px;
	background: transparent url(images/login_b.png) no-repeat top left;
	border: 0;
}

input#submit-login:hover {
	background: transparent url(images/login_b_active.png) no-repeat top left;
	cursor: pointer;
}

</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">
$(function() {
	$('#username')[0].focus();
});
</script>
</head>
<body>
<div id="logo">
<?php echo $logo; ?>
<!-- <h2>POS &amp; Inventory System</h2> -->
</div>
<div id="login-form">
<form name="frmSignIn" id="frmSignIn" method="post" action="user.php">
<table id="tab-login">
<tr><td>Login</td></tr>
<tr><td><span>Username:</span></td></tr>
<tr><td><input type="text" name="username" id="username" size="25" /></td></tr>
<tr><td><span>Password:</span></td></tr>
<tr><td><input type="password" name="password" id="password" size="25" /></td></tr>
<tr><td class="login-message"><?php echo $m; ?></td></tr>
<tr><td><input type="submit" name="submit-login" id="submit-login" value="" /></td></tr>
</table>
</form>
</div>
</body>
</html>