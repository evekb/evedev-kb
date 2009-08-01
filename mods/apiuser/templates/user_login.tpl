<form method="post" action="?a=login">
{if $error}
{include file=error.tpl}
<br/><br/>
{/if}
<table class="kb-subtable">
<tr>
  <td width="160"><b>Login:</b></td>
  <td><input type="text" name="usrlogin" maxlength="40"{if $user_name} value="{$user_name}"{/if}></td>
</tr>
<tr>
  <td width="160"><b>Password:</b></td>
  <td><input type="password" name="usrpass" maxlength="32"></td>
</tr>
<tr>
  <td width="160">&nbsp;</td>
  <td><input type="submit" name="submit" value="Login"></td>
</tr>
</table>
</form>