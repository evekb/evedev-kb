popup| <?php
/**
 * @package EDK
 */
$kll_id = (int)edkURI::getArg('kll_id', 1);
$kill = Cacheable::factory('Kill', $kll_id);
$html = $kill->getRawMail();

event::call("killmail_popup", $html);
$smarty->assign('rawMail', $html);
echo $smarty->fetch(get_tpl("kill_mail"));
?>
