<?php
$installrunning = true;

session_start();

if (!isset($_SESSION['state']))
{
    $_SESSION['state'] = 1;
}
elseif (isset($_GET['step']) && $step = intval($_GET['step']))
{
    $_SESSION['state'] = $step;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF8">
<title>EVE Development Network Killboard Install Script</title>
<link rel="stylesheet" type="text/css" href="common.css">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body bgcolor="#222222"  style="height: 100%">
<table class="main-table" height="100%" align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
<tr style="height: 100%">
<td valign="top" height="100%" style="height: 100%">
<div id="header">
<img src="quantum_rise.jpg" border="0">
</div>
<div id="page-title">Install Step <?php echo $_SESSION['state']; ?></div>
<table cellpadding="0" cellspacing="0" width="100%" border="0">
<tr><td valign="top"><div id="content">
<?php
if(file_exists('install.lock'))
{
	?>
	<p>Remove install/install.lock before attempting to reinstall.</p>
	<?php	
}
else include('install_step'.$_SESSION['state'].'.php');

?>
</div></td>
</tr></table>
<div class="counter"><font style="font-size: 9px;">&copy;2006-2009 <a href="http://www.eve-id.net/" target="_blank">EVE Development Network</a></font></div>
</td></tr></table>
</body>
</html>