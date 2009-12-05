<?php
$stoppage = true;
?>
<div class="block-header2">Installation Complete</div>
<p>Congratulations, you have successfully installed the EVE Development Network Killboard v2.0!<br/>
Please check <a href="http://www.eve-id.net/">eve-id.net</a> for updates from time to time.<br/>
<br/>
You can now take a look at <a href="../">your new Killboard</a>.<br/><br/>
<b>Don't forget to delete the install folder now or restrict the access to it!</b><br/>
</p>
<?php if ($stoppage)
{
	// Block further attempts to run the install in case the installer
	// forgets to delete the install folder.
	touch("install.lock");
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>