<html>
<head>
<title>{$kb_title}</title>
</head>
<body bgcolor="#222222">
<table heigth="100%" width="100%" bgcolor="#111111" border="0" cellspacing="1">
<tr>
<td valign="top">
<div>{$page_title}</div>
<hr>
{$content_html}
{if $context_html}
<td valign="top" align="right">
<div id="context">{$context_html}</div>
</td>
{/if}
<hr>
</td></tr></table>
</body>
</html>