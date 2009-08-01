<?php
/**
 * Import kills from a csv formatted text
 * Used together with export tool!
 */

require_once('common/includes/class.parser.php');
require_once('common/includes/class.killlist.php');

$page = new Page('Administration - Killmail import');
$page->setAdmin();

if (!$_POST['killmail'])
{
    $html .= '<b>Killmails in same format as export (Comma Seperated - csv):</b><br>';
    $html .= '<form id=postform name=postform class=f_killmail method=post action="?a=admin_kill_import_csv">';
    $html .= '<textarea class=killmail id=killmail name=killmail cols="55" rows="35"></textarea><br><br>';
    $html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=submit name=submit type=submit value="Process !"></input>';
    $html .= '</form>';
}
else
{
    // Set delimiter
    $splitter = ',\n\n';
    $killmail = $_POST['killmail'];

    // Replace double quotes with single
    $killmail = str_replace('""', "'", $killmail);

    // Replace \ with nothing
    $killmail = str_replace('\\', "", $killmail);

    // Explodes to array
    $getstrings = explode('"', $splitter . $killmail . $splitter);

    // Set lenght of delimiter
    $delimlen = strlen($splitter);

    // Default
    $instring = 0;

    // String magic :)
    while (list($arg, $val) = each($getstrings))
    {
        if ($instring == 1)
        {
            $result[] = $val;
            $instring = 0;
        }
        else
        {
            if ((strlen($val) - $delimlen - $delimlen) >= 1)
            {
                $temparray = split($splitter, substr($val, $delimlen, strlen($val) - $delimlen - $delimlen));
                while (list($iarg, $ival) = each($temparray))
                {
                    $result[] = trim($ival);
                }
            }
            $instring = 1;
        }
    }
    // Parses killmails one by one.
    foreach ($result as $killmail)
    {
        $parser = new Parser($killmail);
        $killid = $parser->parse(false);
        // Make response
        if ($killid == 0)
        {
            $html .= "Killmail is malformed.<br>";
        }
        elseif ($killid == -1)
        {
            $html .= "That killmail has already been posted <a href=\"?a=kill_detail&kll_id=" . $parser->dupeid_ . "\">here</a>.<br>";
        }
        elseif ($killid == -2)
        {
            $html .= "You are not authorized to post this killmail.<br>";
        }
        elseif ($killid >= 1)
        {
            $html .= "Killmail imported succesfully <a href=\"?a=kill_detail&kll_id=" . $parser->dupeid_ . "\">here</a>.<br>";
        }
    }
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>