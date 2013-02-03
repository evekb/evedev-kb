<?php

	include_once('kbconfig.php');
	require_once('common/includes/globals.php');
	$config = new Config();
	$bg = config::get( 'style_background');
	$bgcolor = config::get( 'style_background_color');

	//config::get('style_background_x');
	//config::get('style_background_y');
	
	header("Content-Type: text/css");
	
?>
html, body
{
	background-color: <?php echo $bgcolor; ?>;
	<?php if( $bg != 0 ) { ?>
	background-attachment: fixed;
	background-image: url("<?php echo "background/" . $bg; ?>");
	background-position: center center;
	background-repeat: repeat-x left top;
	<?php } ?>
}