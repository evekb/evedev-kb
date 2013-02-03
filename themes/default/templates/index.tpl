<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="description" content="EDK Killboard - {$config->get('cfg_kbtitle')}" />
	<meta name="keywords" content="EDK, killboard, {$config->get('cfg_kbtitle')}, {if $kb_owner}{$kb_owner}, {/if}Eve-Online, killmail" />
	<title>{$kb_title}</title>
	<link rel="stylesheet" type="text/css" href="jquery.dataTables_themeroller.css">
	<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="{$kb_host}/css.php" />
	<link rel="stylesheet" type="text/css" href="{$kb_host}/themes/default/default.css" />
	{if $theme_name != 'default'}<link rel="stylesheet" type="text/css" href="{$theme_url}/default.css" />{/if}
	<link id="edkid_theme" rel="stylesheet" type="text/css" href="{$kb_host}/jquerythemes/{$jqtheme_name}/jquery-ui.css" />
	<script type='text/javascript' src='{$kb_host}/js/jquery-1.9.0.js'></script>
	<script type='text/javascript' src='{$kb_host}/js/jquery-ui-1.10.0.custom.js'></script>
	<script type='text/javascript' src='{$kb_host}/js/ui.js'></script>
	<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>


	<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		$('.kl-table').dataTable({
            "bFilter": false,
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bLengthChange": false,
			"bSortClasses": false,
        });
	} );
	</script>
{$page_headerlines}
	<script type="text/javascript" src="{$kb_host}/themes/generic.js"></script>
</head>
<body {if isset($on_load)}{$on_load}{/if}>
{$page_bodylines}
	<div id="popup"></div>
	<div id="stuff1"></div>
	<div id="stuff2"></div>
	<div id="stuff3"></div>
	<div id="stuff4"></div>
	<div id="main" class="ui-widget ui-widget-content">
{if $banner}
		<div id="header">
{if $bannerswf=='true'}
			<object type="application/x-shockwave-flash" data="{$kb_host}/banner/{$banner}" height="200" width="1000">
				<param name="movie" value="myFlashMovie.swf" />
			</object>
{else}
		<a href="{if isset($banner_link)}{$banner_link}{else}?a=home{/if}">
			<img src="{$kb_host}/banner/{$banner}" alt="Banner" {if $banner_x && $banner_y}width = "{$banner_x}" height="{$banner_y}"{/if} />
		</a>
{/if}
		</div>
{/if}
		<div id="edkid_navigation" class="navigation">
			<table>
				<tr>
		{section name=item loop=$menu}
					<td class="ui-state-default"><a class="link" href="{$menu[item].link}">{$menu[item].text}</a></td>
		{/section}
				</tr>
			</table>
		</div>
{if isset($message)}
		<div id="boardmessage">{$message}</div>
{/if}
		<div id="page-title"><h2>{$page_title}</h2></div>
		<div id="content">
{$content_html}
		</div>
{if $context_html}
		<div id="context">
{section name=item loop=$context_divs}
		<div class="context_element" id="context_{$smarty.section.item.index}">{$context_divs[item]}</div>
{/section}
		</div>
{/if}
{if $profile}
		<div id="profile"><!-- profile -->{$profile_sql} queries{if $profile_sql_cached} (+{$profile_sql_cached} cached) {/if} SQL time {$sql_time}s, Total time {$profile_time}s<!-- /profile --></div>
{/if}
		<div class="counter"></div>
	</div>
</body>
</html>
