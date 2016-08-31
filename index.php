<?php

require 'verify_session.php';
require 'grants.php';
$fullname = $_SESSION['fullname'];

$branch = $_SESSION['branch'];
$sbranch = "Francey";

switch ($branch) {

case 1:
$title = "Francey Boutique & Gen. MSDE";
break;

case 2:
$title = "Yessamin Boutique & Gen. MSDE";
$sbranch = "Yessamin";
break;

case 3:
$title = "Francey Boutique & Gen. MSDE Branch 1";
$sbranch = "Sta. Maria";
break;

}

require_once 'sql_mode.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $title; ?></title>
<style type="text/css">
@import url(css/clearfix.css) screen;
@import url(css/styles.css) screen;
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/jquery.blockUI.js?ver=1.0.1"></script>
<script type="text/javascript" src="js/dialogbox.js?ver=1.0.1"></script>
<script type="text/javascript" src="js/content.js?ver=1.0.1"></script>
<script type="text/javascript" src="js/stock-in.js?ver=1.0.5"></script>
<script type="text/javascript" src="js/discount.js?ver=1.0.1"></script>
<script type="text/javascript" src="js/customer.js?ver=1.0.1"></script>
<script type="text/javascript" src="js/transaction.js?ver=1.0.3"></script>
<script type="text/javascript" src="js/cashier.js?ver=1.0.1"></script>
<script type="text/javascript">
var tabn = <?php echo ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) ? 9 : 7; ?>;
</script>
</head>
<body>
<div id="top">
<div id="menu">
	<ul>
	<li id="menu-1"><a href="javascript: loadMenu(1);">STOCK-IN</a></li>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-2"><a href="javascript: loadMenu(2);">INVENTORY</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-3"><a href="javascript: loadMenu(3);">DEALERS</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-4"><a href="javascript: loadMenu(4);">STOCK-OUT</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-5"><a href="javascript: loadMenu(5);">INPUTS</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-6"><a href="javascript: loadMenu(6);">DAILY INPUTS</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<li id="menu-7"><a href="javascript: loadMenu(7);">DISCOUNTS</a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<li id="menu-8"><a href="javascript: loadMenu(8);">CASHIERS</a></li>'; ?>
	<?php if (((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) && ($branch == 2)) || ((substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) && ($branch == 2))) echo '<li id="menu-9"><a href="javascript: loadMenu(9);"><span style="font-size: 12px; display: inline-block; margin-top: -8px;">MANUAL REMITTANCES</span></a></li>'; ?>
	<?php if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<li id="menu-10"><a href="javascript: loadMenu(10);"><span style="font-size: 12px; display: inline-block;">DEPOSITS</span></a></li>'; ?>
	</ul>
</div>
</div>
<div id="user-setting" class="clearfix">
<div id="sys-time"><?php echo date("l - F j, Y"); echo " - Branch: $sbranch"; ?></div>
<div id="in-user-setting">
<span><?php echo $fullname; ?></span>
<a href="javascript: settings();" class="tooltip-min"><span>Settings</span><img src="images/settings.png" /></a>
<a href="javascript: confirmLogOut();" class="tooltip-min"><span>Logout</span><img src="images/user.png" /></a>
</div>
</div>
<div id="sub-menu-search">
</div>
<div id="content">
<div id="in-content">
</div>
</div>
<div id="footer"><span><?php echo $title; ?> &copy; <?php echo date("Y"); ?></span>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
<div id="loadingModal" style="z-index: 2147483647;"><div class="loading-msg">Updating stocks please wait...</div><div class="ajax-loader"><img src="images/ajax-loader.gif" /></div></div>
<div id="processModal" style="z-index: 2147483647;"><div class="loading-msg">Processing please wait...</div><div class="ajax-loader"><img src="images/ajax-loader.gif" /></div></div>
<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
</body>
</html>