<?php
/*
 * $Id $
 */

require_once('common/includes/class.kill.php');

$page = new Page("Administration - Deletion of Comment ID \"".$_GET['c_id']."\"");
$page->setAdmin();

if ($_POST['confirm'])
{
    $qry = DBFactory::getDBQuery();;
    $qry->execute("delete from kb3_comments where id='".$_GET['c_id']."'");
    $html .= "Comment ID \"".$_GET['c_id']."\" deleted!";
    $html .= "<br /><br /><a href=\"javascript:window.close();\">[close]</a>";
}
else
{
    $html .= "Confirm deletion of Comment ID \"".$_GET['c_id']."\": ";
    $qry = DBFactory::getDBQuery();;
    $qry->execute("SELECT id, name, comment FROM kb3_comments WHERE `id`='".$_GET['c_id']."'");
    if ($qry->recordCount() == 0)
    {
        // no commment
        $html .= "Error: comment does not exist<br />\n";
    }
    else
    {
        while ($data = $qry->getRow())
        {
            $name = $data['name'];
            $comment = $data['comment'];
            $html .= "<div class=\"comment-text\"><a href=\"?a=search&amp;searchtype=pilot&amp;searchphrase=".$name."\">".$name."</a>:<p>".$comment."</p>";
            $html .= "</div><br />";
        }
        $html .= "<form action='' method='post'>";
        $html .= "<input type='submit' name='confirm' value='Yes' /> ";
        $html .= "<button onclick=\"window.close();\">No</button>";
        $html .= "</form>";        
    }
}
$page->setContent($html);
$page->generate();
?>