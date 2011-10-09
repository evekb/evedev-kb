<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$kll_id = (int)edkURI::getArg('kll_id', 1);
$kill = Cacheable::factory('Kill', $kll_id);
?>
popup|<form>
<table class="popup-table" height="100%" width="355px">
<tr>
	<td align="center"><strong>Original Killmail</strong></td>
</tr>
<tr>
	<td align="center"><input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>
<tr>
<td valign="top" align="center">
<textarea class="killmail" name="killmail" cols="60" rows="30" readonly="readonly">
<?php echo $kill->getRawMail();?></textarea></td></tr>
<tr><td align="center"><input type="button" value="Select All" onClick="this.form.killmail.select();this.form.killmail.focus(); document.execCommand('Copy')">&nbsp;<input type="button" value="Close" onClick="ReverseContentDisplay('popup');"></td>
</tr>

</table>
</form>


