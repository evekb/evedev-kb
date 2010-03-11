{if $conf_exists}
	<div class="block-header2"><img src="{$conf_image}" border="0" alt=""> Found the old config file</div>
	We will just reuse the data and create a new config file. You may adjust the values below.<br/>
	<br/>
{/if}

<form id="options" name="options" method="post" action="?step=3">
	<input type="hidden" name="step" value="3">
	<div class="block-header2">MySQL Database</div>
	<table class="kb-subtable">
	<tr><td width="120"><b>MySQL Host:</b></td><td><input type=text name=host id=host size=20 maxlength=80 value="{$db_host}"></td></tr>
	<tr><td width="120"><b>User:</b></td><td><input type=text name=user id=user size=20 maxlength=80 value="{$db_user}"></td></tr>
	<tr><td width="120"><b>Password:</b></td><td><input type=password name=dbpass id=pass size=20 maxlength=80 value="{$db_pass}"></td></tr>
	<tr><td width="120"><b>Database:</b></td><td><input type=text name=db id=db size=20 maxlength=80 value="{$db_db}"></td></tr>
	<tr><td width="120"><b>Engine:</b></td><td>
	<input type="radio" name="engine" value="InnoDB"{if $db_engine != "MyISAM"} checked{/if}>InnoDB
	<input type="radio" name="engine" value="MyISAM"{if $db_engine == "MyISAM"} checked{/if}>MyISAM
	</tr>
	<tr><td width="120"></td><td><input type=submit name=submit value="Test"></td></tr>
	</table>
</form>

{if $db_db}
	<div class="block-header2"><img src="{$db_image}" border="0" alt=""> Testing Settings</div>
	Trying to connect to the provided MySQL server now...<br/>
	{if $test_db}
	Connected to MySQL server
	{if $test_sql}
		running Version '{$test_version}'.<br/>
		{if $version_ok}
			{if $test_select}
			Successfully selected database '{$db_db}', everything seems fine. Please continue.<br/>
			{else}
			Could not select the database: '{$test_error}'<br/>
			{/if}
			{if $test_inno}
			<br/>Checking database engine InnoDB... <br/>
				{if !$test_error_inno}
					InnoDB is supported on your MySQL Server.<br />
				{else}
					Error: InnoDB is not supported on your MySQL Server.<br />
				{/if}
			{/if}
		{else}
		This version of MySQL is not supported. You must ask your host to upgrade.
		{/if}
	{else}
		... something went wrong:<br/>{$test_error}<br/>
	{/if}
	{else}
	Could not connect to the server: '{$test_error}'<br/>
	{/if}
{/if}

{if !$stoppage}
<p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}