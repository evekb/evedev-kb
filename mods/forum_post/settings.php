<?php
/**
 * @package EDK
 */

require_once( "common/admin/admin_menu.php" );

$colours =array("red" ,
"blue" ,
"pink" ,
"brown" ,
"black" ,
"orange" ,
"violet" ,
"yellow" ,
"green" ,
"gold" ,
"white" ,
"purple" ,
"limegreen" ,
"maroon" ,
"navy" ,
"teal" ,
"beige" );

$styles = array("strikethrough"=>"s","underline"=>"u","bold"=>"b","italic"=>"i");
if(isset($_POST['Reset']))
{
config::set('forum_post_colours',"");
config::set('forum_post_styles',"");
$confirm = "<strong>Settings Reset</strong><br/>";
}


if(isset($_POST['submit'])) //workings
{
	$sql = "select scl_id, scl_class
			from kb3_ship_classes
		   where scl_class not in ( 'Drone', 'Unknown' )
		  order by scl_class";

	$qry = DBFactory::getDBQuery();
	$qry->execute($sql) or die($qry->getErrorMsg());
	
	$forum_post_colours = array();
	$forum_post_styles = array();
	while ($row = $qry->getRow())
	{
		$value = str_replace(" ","",$row['scl_class']);
		//echo $_POST[$value.'_colour']."<br>";
		if($_POST[$value.'_colour'] != "None")
		{
			$forum_post_colours[$value] = $_POST[$value.'_colour'];
		}	
		if($_POST[$value.'_style'] != "None")
		{
			$forum_post_styles[$value] = $_POST[$value.'_style'];
		}	
	
	}
config::set('forum_post_colours',$forum_post_colours );
config::set('forum_post_styles',$forum_post_styles);
config::set('forum_post_isk',$_POST['isk']);
config::set('forum_post_order',$_POST['order']);
config::set('forum_post_miss_empty_class',$_POST['miss_empty_class']);
$confirm = "<strong>Settings Saved</strong><br/>";
} // end workings


$page = new Page( "Settings - Forum Post" );
$html .= $confirm;

$set_colours = config::get('forum_post_colours'); 	//load colour settings
if(!is_array($set_colours)) { $set_colours = array(); } 				// if the settings have been reset create an empty array so as not to brake the code later on
$set_styles = config::get('forum_post_styles');		//load style settings
if(!is_array($set_styles)) { $set_styles = array(); }					// if the settings have been reset create an empty array so as not to brake the code later on
$set_isk = config::get('forum_post_isk',$_POST['isk']);			// load isk setting
$miss_empty_class = config::get('forum_post_miss_empty_class');
//print_r($set_styles);

	$sql = "select scl_id, scl_class
			from kb3_ship_classes
		   where scl_class not in ( 'Drone', 'Unknown' )
		  order by scl_class";

	$qry = DBFactory::getDBQuery();
	$qry->execute($sql) or die($qry->getErrorMsg());
	
	$html .='<form action="" method="post"><table name="settings"><tr><td>Ship Class</td><td>Colour</td><td>Style</td></tr>';
	
	while ($row = $qry->getRow())
	{
	$html .="<tr>";
	$html .= '<td>'.$row['scl_class'].'</td><td>';
	
	$class = str_replace(" ","",$row['scl_class']);
	$html.='<select name="'.$class.'_colour">';
	
	if(array_key_exists($class,$set_colours)) //check to see if it is set. 
	{
		$html .= '<option value="None">None</option>';
	}	 //colour has been set previously
	else
	{
	$html .= '<option value="None" selected="selected">None</option>';
	}
	foreach($colours as $select)
		{
			$html .='<option value="'.$select.'"';
							if($select == $set_colours[$class]) { $html .= ' selected="selected"'; } //select this option

			$html .='>'.$select.'</option>';
		}
	$html .="</select></td><td>";
	
	
	$html.='<select name="'.str_replace(" ","",$row['scl_class']).'_style">';
	
	if(array_key_exists($class,$set_styles)) //check to see if it is set. 
	{
		$html .= '<option value="None">None</option>';
	}	 //colour has been set previously
	else
	{
	$html .= '<option value="None" selected="selected">None</option>';
	}

		foreach($styles as $select => $v)
		{
			$html .='<option value="'.$v.'"';
							if($v == $set_styles[$class]) { $html .= ' selected="selected"'; } //select this option

			$html .='>'.$select.'</option>';
		}
	
	$html .="</select></td></tr>";
	}
	
	$html .='
	<tr>
	<hr/>
    <td colspan="3"><hr/><input name="isk" type="checkbox" value="yes" ';
	if($set_isk == "yes") { $html .= "checked"; }
	$html .='> Include individual Isk Values?<br/><br/>
	
	<input name="miss_empty_class" type="checkbox" value="1" ';
	if($miss_empty_class == "1") { $html .= "checked"; }
	$html .='> Dont show classes with no kills or losses?<br/><br/>
	
	Place ship class at start or end of each line?<br/>';
	if(config::get('forum_post_order') != "last"){
	
	$html .= '<input name="order" type="radio" value="first" checked="checked"/>Start<br/>
	<input name="order" type="radio" value="last" />End<hr/>';
	}
	else
	{
	$html .= '<input name="order" type="radio" value="first" />Start<br/>
	<input name="order" type="radio" value="last" checked="checked" />End<hr/>';
	}
  	$html .= '</td></tr>';
	
	$html .='
	<tr>
    <td colspan="3"><input type="submit" value="submit" name="submit"> <input type="submit" value="Reset" name="Reset"></td>
  	</tr>
  </table></form>';
                                                      
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>