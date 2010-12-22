<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF8" />
		{if $refresh}<meta http-equiv="refresh" content="{if $refresh > 5}5{else}{$refresh}{/if};url='{$url}'" />{/if}
		<title>EVE Development Network Killboard Update Script</title>
		<link rel="stylesheet" type="text/css" href="update.css" />
	</head>
	<body>
		<table align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
			<tr style="height: 100%">
				<td valign="top" style="height: 100%">
					<img src="quantum_rise.jpg" border="0" alt="banner"/>
						<div id="page-title">Update</div>
						<table cellpadding="0" cellspacing="0" width="100%" border="0">
							<tr>
								<td valign="top">
									<div id="content">{$content}</div>
								</td>
							</tr>
						</table>
						<div class="counter">
							<font style="font-size: 9px;">&copy;2006-2010 <a href="http://www.eve-id.net/" target="_blank">EVE Development Network</a></font>
						</div>
				</td>
			</tr>
		</table>
	</body>
</html>