{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{if count($page_error) > 0}
    <div class="block-header2">Error</div>
    {section name=idx loop=$page_error}
    {$page_error[idx]}<br/>
    {/section}
    <br/>
{/if}
<div class="block-header2">Code [This Killboard is Version {$codeversion}]</div>
{if $codemessage != ''}
    <div class="block-header">Message from the devs</div>
    <p>{$codemessage}</p>
{/if}
<table class="kb-table" style="width:100%">
    <tr class="kb-table-header">
	<td>Version</td>
	<td>SVN</td>
	<td>File</td>
	<td>Description</td>
	<td>Action</td>
    </tr>
    {if count($codeList) > 0}
	{section name=idx loop=$codeList}
	    <tr class="{cycle name=ccl}" style="height: 20px">
		<td>
		    {$codeList[idx].version}<br/>
		</td>
		<td>
		    {$codeList[idx].svnrev}<br/>
		</td>
		<td>
		    {$codeList[idx].short_name}<br/>
		</td>
		<td width="50%">
		    {$codeList[idx].desc}<br/>
		</td>
		<td>
		    {if !$codeList[idx].cached || !$codeList[idx].hash_match}
			<a href="{$kb_host}/?a=admin_upgrade&amp;code_dl_ref={$codeList[idx].version}">Download</a>
			{if !$codeList[idx].hash_match}
			    <span style="text-decoration: blink">!!</span><br/>
			{/if}
		    {/if}
		    {if $codeList[idx].hash_match && $codeList[idx].lowest}
			<a href="{$kb_host}/?a=admin_upgrade&amp;code_apply_ref={$codeList[idx].version}">Apply</a>
		    {else}
			^<br/>
		    {/if}
		</td>
	    </tr>
	{/section}
    </table>
    <br/><span style="text-decoration: blink">!!</span> - The downloaded file's hash doesn't match the expected one or the file hasn't been downloaded yet.<br/>
    ^ - This patch relies on the one above it.<br/>
    <br/>
{else}
    <tr class="{cycle name=ccl}" style="height: 20px">
	<td colspan="6">No new updates.</td>
    </tr>
    </table>
    <br/>
{/if}
<br/>
<br/>
The upgrade description file will be retrieved again at: <b>{$update_time} GMT</b>.<br/>
You can force the update description file to retrieved now by clicking on the <a href="{$kb_host}/?a=admin_upgrade&amp;refresh">link</a>.<br/>
Alternatively, you can refresh the page by clicking on the <a href="{$kb_host}/?a=admin_upgrade">link</a>.<br/><br />

<a href="{$kb_host}/?a=admin_upgrade&amp;reset_db">Reset stored database level.</a><br />
