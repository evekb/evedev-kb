<table border="0" class="kb-table"><tr class="kb-table-header"><td colspan="3">Graphical Overview</td></tr><tr>
<td><img src="?a=mapview&amp;sys_id={$sys_id}&amp;mode=map&amp;size=250" border="0" width="250" height="250"></td>
<td><img src="?a=mapview&amp;sys_id={$sys_id}&amp;mode=region&amp;size=250" border="0" width="250" height="250"></td>
<td><img src="?a=mapview&amp;sys_id={$sys_id}&amp;mode=cons&amp;size=250" border="0" width="250" height="250"></td>
</tr></table><br/>
{if $sys_view == 'recentkills'}<div class=kb-kills-header>20 most recent kills</div>
{elseif $sys_view == 'losses'}<div class=kb-kills-header>All losses</div>
{else}<div class=kb-kills-header>All kills</div>
{/if}
{$sys_killlist}
{$sys_splitter}