{if $length}
    <b>Error:</b> Your site identification string is way too long.<br/>
{/if}


<p>You have to enter/edit some settings now. I will generate a config file based on this data for you.<br/>
    To be able to continue you have to enter at least an admin password and a site identification key.<br/>
    <br/>
    <b>Tips:</b><br/>
    Title is used as title attribute for every page so your corp/alliance name could be a good idea.<br/>
    Site identification should be 1-12 chars, for the average killboard 4 characters should be fine. They will be used to reference your settings inside the database, something like 'GKB' will be sufficient.
    This will uniquely identify your killboard settings in the case of a shared database between multiple hosts or a shared host.<br/>
    The URLs are guessed on the location of this installscript, you might need to correct them for some installations.<br/>
</p>
<form id="options" name="options" method="post" action="?step=6">
    <input type="hidden" name="step" value="6">
    <div class="block-header2">Settings</div>
    <table class="kb-subtable">
	{section name=table loop=$settings}
	    {strip}
		<tr><td width="120"><b>{$settings[table].descr}:</b></td><td><input type=
		{if $settings[table].name == 'adminpw'}
		    "password"
		{else}
		    "text"
		{/if}
		name="set[{$settings[table].name}]" size=60 maxlength=80 value="{$settings[table].value}"></td></tr>
	    {/strip}
	{/section}
	<tr><td width="120"></td><td><input type=submit name=submit value="Save"></td></tr>
    </table>
</form>

{if !$stoppage}
    <p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}