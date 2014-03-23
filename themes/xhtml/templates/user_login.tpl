<!-- user_login.tpl -->
<form method="post" action="{$kb_host}/?a=login">
{if isset($error)}
<div class="block-header2">Error</div>
{$error}<br/><br/>
{/if}
<table class="kb-subtable">
{if !$config->get("user_regdisabled")}<tr>
  <td width="160"><b>Login:</b></td>
  <td><input type="text" name="usrlogin" maxlength="40"{if $user_name} value="{$user_name}"{/if}></td>
</tr>
{/if}
<tr>
  <td width="160"><b>Password:</b></td>
  <td><input type="password" name="usrpass" maxlength="32" /></td>
</tr>
<tr>
  <td width="160">&nbsp;</td>
  <td><input type="submit" name="submit" value="Login" /></td>
</tr>
</table>
</form>
<!-- /user_login.tpl -->