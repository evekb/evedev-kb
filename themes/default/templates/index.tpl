<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="description" content="EDK Killboard - {$config->get('cfg_kbtitle')}" />
<meta name="keywords" content="EDK, killboard, {$config->get('cfg_kbtitle')}, {if $kb_owner}{$kb_owner}, {/if}Eve-Online, killmail" />
<title>{$kb_title}</title>
<link rel="stylesheet" type="text/css" href="{$theme_url}/{$style}.css" />
{$page_headerlines}
<script type="text/javascript" src="{$kb_host}/themes/generic.js"></script>
<!--[if lt IE 7]>
<script defer type="text/javascript" src="{$kb_host}/themes/generic.js"></script>
<![endif]-->
</head>
<body {$on_load} style="height: 100%">
{$page_bodylines}
<div align="center" id="popup" style="display:none;
	position:absolute;
    top:217px; width:99%;
	z-index:3;
    padding: 5px;"></div>
	<table class="main-table" align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
<tr style="height: 100%">
<td valign="top" height="100%" style="height: 100%">
<div id="header">
{if $bannerswf=='true'}
<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="1000" HEIGHT="200" id="{$banner}" ALIGN="">
<PARAM NAME=movie VALUE="{$kb_host}/banner/{$banner}"> <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=black> <EMBED src="banner/{$banner}" quality=high bgcolor=black WIDTH="1000" HEIGHT="200" NAME="{$banner}" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED> </OBJECT>
{else}
{if $banner_link}
<a href="{$banner_link}">
<img src="{$kb_host}/banner/{$banner}" border="0" alt="Banner" {if $banner_x && $banner_y}width = "{$banner_x}" height="{$banner_y}"{/if} /></a>
{else}
<a href="?a=home"><img src="{$kb_host}/banner/{$banner}" border="0" alt="Banner" {if $banner_x && $banner_y}width = "{$banner_x}" height="{$banner_y}"{/if} /></a>
{/if}
{/if}
</div>
<table class="navigation" width="100%" style="height:25px;" border="0" cellspacing="1">
<tr class="kb-table-row-odd">
{section name=item loop=$menu}
<td width="{$menu_w}" align="center"><a class="link" style="display: block;" href="{$menu[item].link}">{$menu[item].text}</a></td>
{/section}
</tr>
</table>
{if $message}
<table class="navigation" width="100%" height="25" border="0" cellspacing="1">
<tr class="kb-table-row-odd">
<td align="center"><b>{$message}</b></td>
</tr>
</table>
{/if}
<div id="page-title">{$page_title}</div>
<table cellpadding="0" cellspacing="0" width="100%" border="0">
<tr><td valign="top"><div id="content">
{$content_html}
</div></td>
{if $context_html}
<td valign="top" align="right">
<div id="context">{$context_html}</div>
</td>
{/if}
</tr></table>
{if $profile}
<table class="kb-subtable" width="99%" border="0">
<tr><td height="100%" align="right" valign="bottom"><!-- profile -->{$profile_sql} queries{if $profile_sql_cached} (+{$profile_sql_cached} cached) {/if} SQL time {$sql_time}s, Total time {$profile_time}s<!-- /profile --></td></tr>
</table>
{/if}
<div class="counter"></div>
</td></tr></table>
</body>
</html>
