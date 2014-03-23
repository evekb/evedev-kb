This script is designed to verify your killboard files to make sure that everything is present and correct. This may take a while to run. Verifying the images may cause timeouts if your server isn't fast enough.
<br /><br />
<form action="{$kb_host}/?a=admin_verify" method="POST">
	Include Images: <input type="checkbox" name="images" /><br />
	<input type="submit" value="Verify" name="submit" />
</form>