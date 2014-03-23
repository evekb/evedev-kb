popup| <?php	
/**
 * @package EDK
 */
require_once('mods/forum_post/class.killsummarytable.php');

if(isset($_GET['ctr_id'])){
$ctr_id = intval($_GET['ctr_id']);
$contract = new Contract($ctr_id);
$klist = $contract->getKillList();
$llist = $contract->getLossList();
$killsummary = new KillSummaryTable($klist, $llist);
$killsummary->setBreak(6);
	if ($_GET['view'] == ""){
		$killsummary->setFilter(false);
	}
$name = $contract->getName();
}

if(isset($_GET['kll_id']))
{
$kll_id = intval($_GET['kll_id']);
// this is a fast query to get the system and timestamp
$rqry = DBFactory::getDBQuery();
$rsql = 'SELECT kll_timestamp, kll_system_id from kb3_kills where kll_id = '.$kll_id;
$rqry->execute($rsql);
$rrow = $rqry->getRow();
$system = new SolarSystem($rrow['kll_system_id']);

// now we get all kills in that system for +-12 hours
$query = 'SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id='.$rrow['kll_system_id'].'
            AND kll.kll_timestamp <= date_add( \''.$rrow['kll_timestamp'].'\', INTERVAL \'12\' HOUR )
            AND kll.kll_timestamp >= date_sub( \''.$rrow['kll_timestamp'].'\', INTERVAL \'12\' HOUR )
            ORDER BY kll.kll_timestamp ASC';
$qry = DBFactory::getDBQuery();
$qry->execute($query);
$ts = array();
while ($row = $qry->getRow())
{
    $time = strtotime($row['ts']);
    $ts[intval(date('H', $time))][] = $row['ts'];
}

// this tricky thing looks for gaps of more than 1 hour and creates an intersection
$baseh = date('H', strtotime($rrow['kll_timestamp']));
$maxc = count($ts);
$times = array();
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh+$i) % 24;
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh-$i) % 24;
    if ($h < 0)
    {
        $h += 24;
    }
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
unset($ts);
asort($times);

// we got 2 resulting timestamps
$firstts = array_shift($times);
$lastts = array_pop($times);

$kslist = new KillList();
$kslist->setOrdered(true);
$kslist->addSystem($system);
$kslist->setStartDate($firstts);
$kslist->setEndDate($lastts);
involved::load($kslist,'kill');

$lslist = new KillList();
$lslist->setOrdered(true);
$lslist->addSystem($system);
$lslist->setStartDate($firstts);
$lslist->setEndDate($lastts);
involved::load($lslist,'loss');

$killsummary = new KillSummaryTable($kslist, $lslist);
$killsummary->setBreak(6);
$name = $system->getName()." ".substr($firstts,0, 16)." ". substr($lastts,-8,5);
}
?>
<form>
<table class="popup-table" height="100%" width="355px">
<tr>
	<td align="center"><strong>Forum Post</strong></td>
</tr>
<tr>
	<td align="center"><input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>
<tr>
<td valign="top" align="center">
<textarea class="killmail" name="killmail" cols="100" rows="30" readonly="readonly">
<?php 
echo $name."\r\n";
echo $killsummary->forum();?></textarea></td></tr>
<tr><td align="center"><input type="button" value="Select All" onClick="this.form.killmail.select();this.form.killmail.focus(); document.execCommand('Copy')">&nbsp;<input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>

</table>
</form>

