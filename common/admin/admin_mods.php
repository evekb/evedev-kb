<?php
require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Mods');
$page->setAdmin();

if ($_POST['set_mods'] != '')
{
    foreach($_POST as $key => $val)
    {
        if (substr($key, 0, 4) == "mod_" && $val == "on")
        {
            if (substr($key, 4, strlen($key)-4) != 'item_values')
            {
                $activemods .= substr($key, 4, strlen($key)-4).",";
            }
        }
    }
    $activemods = substr($activemods, 0, strlen($activemods)-1);
    config::set("mods_active", $activemods);
}
$activemods = explode(",", config::get("mods_active"));
$html = <<<HTML
	<form action="?a=admin_mods" method="post">
		<input type="hidden" name="set_mods" value="1"/>
     <table class=kb-table width="99%" align=center cellspacing="1">
				<tr class=kb-table-header>
				<td class=kb-table-header>Name</td>
				<td class=kb-table-header align="center">Active</td>
				</tr>
HTML;
if ($handle = opendir('mods'))
{
	$modlist = array();
    while ($file = readdir($handle))
    {
        if (is_dir("mods/$file") && $file != ".." &$file != "." &$file != ".svn")
        {
            $modhtml = "<tr class=kb-table-row-odd style=\"height: 34px;\">";
            $id = $file;

            if (in_array($id, $activemods))
            {
                $checked = "checked=\"checked\"";
            }
            else
            {
                $checked = "";
            }
            if (file_exists("mods/$file/settings.php"))
            {
                $file .= " [<a href=\"?a=settings_$file\">settings</a>]";
            }
            $modhtml .= "<td>$file</td><td align=center><input name=\"mod_$id\" type=\"checkbox\"$checked/></td></tr>";
			$modlist[] = $modhtml;
        }
    }
	asort($modlist);
	$html.=implode($modlist);

    closedir($handle);
}
$html .= "<tr><td colspan=2 align=center><input type=submit name=submit value=\"Save\"></table></form>";
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>