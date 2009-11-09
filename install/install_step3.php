<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;

if (!empty($_REQUEST['submit']))
{
    $_SESSION['sql']['host'] = $_POST['host'];
    $_SESSION['sql']['user'] = $_POST['user'];
    $_SESSION['sql']['pass'] = $_POST['dbpass'];
    $_SESSION['sql']['db'] = $_POST['db'];
    $_SESSION['sql']['engine'] = $_POST['engine'];
}

if (empty($_SESSION['sql']['host']))
{
    $host = 'localhost';
}
else $host = $_SESSION['sql']['host'];
if (file_exists('../config.php'))
{
    echo '<div class="block-header2">Found old config</div>';
    echo 'We will just reuse the data and create a new one.<br/>';
    include_once('../config.php');
    $_SESSION['sql'] = array();
    $_SESSION['sql']['host'] = DB_HOST;
    $_SESSION['sql']['user'] = DB_USER;
    $_SESSION['sql']['pass'] = DB_PASS;
    $_SESSION['sql']['db'] = DB_NAME;
	$_SESSION['sql']['engine'] = '';
}
else
{
	if(!isset($_SESSION['sql']['db']))
	{
		$_SESSION['sql'] = array();
		$_SESSION['sql']['host'] = '';
		$_SESSION['sql']['user'] = '';
		$_SESSION['sql']['pass'] = '';
		$_SESSION['sql']['db'] = '';
		$_SESSION['sql']['engine'] = '';
	}
?>
<form id="options" name="options" method="post" action="?step=3">
<input type="hidden" name="step" value="3">
<div class="block-header2">MySQL Database</div>
<table class="kb-subtable">
<tr><td width="120"><b>MySQL Host:</b></td><td><input type=text name=host id=host size=20 maxlength=80 value="<?php echo $host; ?>"></td></tr>
<tr><td width="120"><b>User:</b></td><td><input type=text name=user id=user size=20 maxlength=80 value="<?php echo $_SESSION['sql']['user']; ?>"></td></tr>
<tr><td width="120"><b>Password:</b></td><td><input type=password name=dbpass id=pass size=20 maxlength=80 value="<?php echo $_SESSION['sql']['pass']; ?>"></td></tr>
<tr><td width="120"><b>Database:</b></td><td><input type=text name=db id=db size=20 maxlength=80 value="<?php echo $_SESSION['sql']['db']; ?>"></td></tr>
<tr><td width="120"><b>Engine:</b></td><td><input type=radio name=engine id=engine value="InnoDB"  <?php if ($_SESSION['sql']['engine'] != "MyISAM") echo "CHECKED"; ?>> InnoDB <input <?php if ($_SESSION['sql']['engine'] == "MyISAM") echo "CHECKED"; ?> type=radio name=engine id=engine value="MyISAM">MyISAM</tr>
<tr><td width="120"></td><td><input type=submit name=submit value="Test"></td></tr>
</table>
<?php
}

if ($_SESSION['sql']['db'])
{
    echo '<div class="block-header2">Testing Settings</div>';
    echo 'Got the data you supplied, trying to connect to that sql server now...<br/>';
    $db = mysql_pconnect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
    if (is_resource($db))
    {
        echo 'Connected to MySQl';
        $result = mysql_query('SELECT VERSION() AS version');
        $result = mysql_fetch_assoc($result);
        if (!$result)
        {
            echo '<br/>Something went wrong:<br/>';
            echo mysql_error();
        }
        else
        {
            echo ' running Version '.$result['version'].'.<br/>';
            if (mysql_select_db($_SESSION['sql']['db']))
            {
                echo 'Successfully selected database "'.$_SESSION['sql']['db'].'", everything seems fine to continue.<br/>';
                $stoppage = false;

                //InnoDB check
                if ($stoppage == false && $_SESSION['sql']['engine'] == 'InnoDB'){
                	echo "</br>Checking Database Engine InnoDB.. <br/>";
                	$stoppage = true;
                	$result = mysql_query('SHOW ENGINES;');
                	while (($row = mysql_fetch_row($result)) &&  $stoppage == true){
                		if ($row[0] == 'InnoDB'){
                			if ($row[1] == 'YES' || $row[1] == 'DEFAULT'){ // (YES / NO / DEFAULT)
                				$stoppage = false;
                			}
                		}
                	}
                	if ($stoppage){
                		echo 'Error: InnoDB is not supported on your MySQL Server.</br>';
                	}else{
                		echo 'InnoDB is supported on your MySQL Server.</br>';
                	}
                }


            }
            else
            {
                echo 'Could not select the database: '.mysql_error().'<br/>';
            }
        }
    }
    else
    {
        echo 'Could not connect to the server: '.mysql_error().'<br/>';
    }
}
?>

<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>
