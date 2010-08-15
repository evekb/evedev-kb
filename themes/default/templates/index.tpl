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
</head>
<body {$on_load} style="height: 100%">
{$page_bodylines}
	<div id="popup">
	</div>
	<div id="main">
		<div id="header">
{if $bannerswf=='true'}
		<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="1000" HEIGHT="200" id="{$banner}" ALIGN="">
		<PARAM NAME=movie VALUE="{$kb_host}/banner/{$banner}"> <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=black> <EMBED src="banner/{$banner}" quality=high bgcolor=black WIDTH="1000" HEIGHT="200" NAME="{$banner}" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED> </OBJECT>
{else}
		<a href="{if $banner_link}{$banner_link}{else}?a=home{/if}">
			<img src="{$kb_host}/banner/{$banner}" style="border:0px" alt="Banner" {if $banner_x && $banner_y}width = "{$banner_x}" height="{$banner_y}"{/if} />
		</a>
{/if}
		</div>
		<div class="navigation">
			<table class="navigation" width="100%" style="height:25px;" border="0" cellspacing="1">
				<tr class="kb-table-row-odd">
		{section name=item loop=$menu}
					<td style="width:{$menu_w}; text-align:center"><a class="link" style="display: block;" href="{$menu[item].link}">{$menu[item].text}</a></td>
		{/section}
				</tr>
			</table>
		</div>
{if $message}
		<div id="boardmessage">{$message}</div>
{/if}
		<div id="page-title">{$page_title}</div>
		<div id="content">
{$content_html}
		</div>
{if $context_html}
{section name=item loop=$context_divs}
		<div class="context_element" id="context_{$smarty.section.item.index}">{$context_divs[item]}</div>
{/section}
{/if}
{if $profile}
		<div id="profile"><!-- profile -->{$profile_sql} queries{if $profile_sql_cached} (+{$profile_sql_cached} cached) {/if} SQL time {$sql_time}s, Total time {$profile_time}s<!-- /profile --></div>
{/if}
		<div class="counter"></div>
	</div>
</body>
</html>
