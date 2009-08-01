<?php
require_once('common/includes/class.kill.php');

$page = new Page("Administration - Deletion of Comment ID \"".$_GET['c_id']."\"");
$page->setAdmin();

if ($_GET['confirm'])
{
    $qry = new DBQuery();
    $qry->execute("delete from kb3_comments where id='".$_GET['c_id']."'");
    $html .= "Comment ID \"".$_GET['c_id']."\" deleted!";
    $html .= "<br><br><a href=\"javascript:window.close();\">[close]</a>";
}
else
{
    $html .= "Confirm deletion of Comment ID \"".$_GET['c_id']."\": ";
    $qry = new DBQuery();
    $qry->execute("SELECT id, name, comment FROM kb3_comments WHERE `id`='".$_GET['c_id']."'");
    if ($qry->recordCount() == 0)
    {
        // no commment
        $html .= "Error: comment does not exist<br>\n";
    }
    else
    {
        while ($data = $qry->getRow())
        {
            $name = $data['name'];
            $comment = $data['comment'];
            $html .= "<div class=\"comment-text\"><a href=\"?a=search&searchtype=pilot&searchphrase=".$name."\">".$name."</a>:<p>".$comment."</p>";
            $html .= "</div><br/>";
        }
        $html .= "<button onClick=\"window.location.href='?a=admin_comments_delete&confirm=yes&c_id=" . $_GET['c_id'] . "'\">Yes</button> ";
        $html .= "<button onClick=\"window.close();\">No</button>";
    }
}
$page->setContent($html);
$page->generate();
?>