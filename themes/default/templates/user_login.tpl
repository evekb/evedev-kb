<!-- user_login.tpl -->
<div class="login">
	<form name="login" method="post" action="">
		{if $error}
			<div class="block-header2">Error</div>
			{$error}<br/><br/>
		{/if}
		<table class="kb-subtable">
			{if !$config->get("user_regdisabled")}<tr>
					<td>Login:</td>
					<td><input type="text" name="usrlogin" maxlength="40"{if $user_name} value="{$user_name}"{/if}></td>
				</tr>
			{/if}
			<tr>
				<td>Password:</td>
				<td><input type="password" name="usrpass" maxlength="32" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="submit" value="Login" /></td>
			</tr>
		</table>
	</form>
</div>
<!-- /user_login.tpl -->