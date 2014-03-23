<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Made by Agustino and HyperBeanie
 * If you use and like these tools, please donate some isk!
 */

$plt_id = $_GET['plt_id'];

// Make the pilot
$pilot = new Pilot($plt_id);
$page = new Page('Administration - Killmail export - ' . $pilot->getName());
$page->setAdmin();
$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();

// Do the check
if (!$pilot->exists())
{
    $html = "That pilot doesn't exist.";
    $page->generate($html);
    exit;
}
$html .= "<form><textarea class=killmail id=killmail name=killmail cols=\"55\" rows=\"35\" readonly=readonly>";

// Setup the lists
$klist = new KillList();
$klist->setOrdered(true);
$klist->addInvolvedPilot($pilot);
$klist->rewind();
while ($kll_id = $klist->getKill())
{
    $kill = new Kill($kll_id->getID());
    $html .= "\"";
    $html .= $kill->getRawMail();
    $html .= "\",\n\n";
}

// Losses
$llist = new KillList();
$llist->setOrdered(true);
// $list->setPodsNoobships( true ); // Not working!!
$llist->addVictimPilot($pilot);
$llist->rewind();
while ($lss_id = $llist->getKill())
{
    $html .= "\"";
    $loss = new Kill($lss_id->getID());
    $html .= $loss->getRawMail();
    $html .= "\",\n\n";
}
$html .= "</textarea><br>";
$html .= "<input type=\"button\" value=\"Select All\" onClick=\"this.form.killmail.select();this.form.killmail.focus(); document.execCommand('Copy')\"></form><br>";
$html .= "Copy content of textbox to another location (eg. a textfile)";
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>