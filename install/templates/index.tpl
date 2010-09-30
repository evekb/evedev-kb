<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
	<meta http-equiv="content-type" content="text/html; charset=UTF8">
	<title>EVE Development Network Killboard Install Script</title>
	<link rel="stylesheet" type="text/css" href="common.css">
	<link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body bgcolor="#222222"  style="height: 100%">
    <table class="main-table" align="center" bgcolor="#111111" border="0" cellspacing="1" style="height:100%">
    <tr style="height: 100%">
    <td valign="top" style="height: 100%">
    <div id="header">
    <img src="quantum_rise.jpg" border="0" alt="banner" />
    </div>
    <div id="page-title">Install Step {$stepnumber} / 8</div>
    <table cellpadding="0" cellspacing="0" width="100%" border="0">
    <tr>
    <td valign="top">
    <div id="content">
    {if $inst_locked}
    <p>Remove install/install.lock before attempting to reinstall.</p>
    {/if}	