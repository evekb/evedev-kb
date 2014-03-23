<form method="post" action="{$kb_host}/?a=register">
{if $error}
<div class="block-header2">Error</div>
{$error}<br/><br/>
{/if}
<table class="kb-subtable">
<tr>
  <td width="160"><b>Login:</b></td>
  <td><input type="text" name="usrlogin" maxlength="40"{if $user_name} value="{$user_name}" disabled="true" readonly="readonly"{/if}></td>
</tr>
<tr>
  <td width="160"><b>Password:</b></td>
  <td><input type="password" name="usrpass" maxlength="32"></td>
</tr>
{if $config->get('user_regpass')}
<tr>
  <td width="160"><b>Registration Password:</b></td>
  <td><input type="password" name="regpass" maxlength="32"></td>
</tr>
{/if}
<tr>
  <td width="160">&nbsp;</td>
  <td><input type="submit" name="submit" value="Create"></td>
</tr>
</table>
</form>