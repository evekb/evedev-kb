{if $stoppage}
<p>You can now search for your pilot, corporation, or alliance.<br/><br/>
    <b>Note:</b> Make sure you spell your corporation/alliance <b>correctly</b> (including capitalisation), else you cannot post any mails!<br/>
</p>
<form id="options" name="options" method="post" action="?step=5">
	<input type="hidden" name="step" value="5">
	<div class="block-header2">Search</div>
	<table class="kb-subtable">
		<tr><td width="120">
				<select id="searchtype" name="searchtype">
					<option value="pilot">Pilot</option>
					<option value="corp">Corporation</option>
					<option value="alliance">Alliance</option>
				</select>
			</td><td><input id="searchphrase" name="searchphrase" type="text" size="30"/>
			</td><td><input type="submit" name="submit" value="Search"/></td></tr>
	</table>
</form>
<br/>
    {if $res_check}
<table class="kb-table" width="400">
	<tr class="kb-table-header">
		<td colspan="2">Results</td></tr>
	{section name=result loop=$results}
	    {strip}
	<tr><td>{$results[result].descr}</td><td><a href="{$results[result].link}</a></td></tr>
	    {/strip}
	{/section}
												</table>
    {/if}
												<br/>
{else}
												<p>Your selection has been saved, please proceed.</p>
{/if}

{if !$stoppage}
												<p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}