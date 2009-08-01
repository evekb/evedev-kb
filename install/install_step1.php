<?php
if(!$installrunning) {header('Location: index.php');die();}
?>
<p>Welcome to the installer and updater, it will help you to set up everything correctly.<br/>
<br/>
This software requires:<br/>
- Webserver (Apache)<br/>
- Mysql 4.1+<br/>
- PHP 4.0+<br/>
- - GD 2 or higher<br/>
- - PHP safe mode off<br/>
- - allow_url_fopen option on (Recommended)<br/>
<br/>
The next step will test if your server has the needed modules to run the Killboard with all features.
</p>

<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>