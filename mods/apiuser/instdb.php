<?php

//delete  from kb3_config where cfg_key like 'apiuser_%'


//used once to create the 3 table needed for the mod or for future version of eveapi
if (A_INSTALLER<>1)
	exit;
if (floatval(config::get('apiuser_version'))==0)
{
$qry->execute("CREATE TABLE kb3_all_corp (
  all_id bigint(3) unsigned default '0',
  corp_id bigint(3) unsigned default '0',
  all_name varchar(200) default NULL
) ");
$qry->execute("CREATE TABLE kb3_api_user (
  userID int(3) unsigned default '0',
  apiKey varchar(64) default NULL,
  charID int(3) unsigned default '0',
  charName varchar(150) default '0',
  corpName varchar(150) default '0',
  allianceName varchar(150) default NULL,
  password varchar(60) default NULL,
  ban tinyint(3) unsigned default NULL
) ");
$qry->execute("CREATE TABLE kb3_kill_poster (
  kill_id int(3) unsigned default '0',
  charID int(3) unsigned default '0'
)");
config::set('apiuser_version','0.2');
$html.='<span class="losscount">[instdb] Creation of tables table kb3_all_corp,kb3_api_user,kb3_kill_poster </span><br>';
}

if (floatval(config::get('apiuser_version')<'0.3'))
{
		//Migrate all the userApi Users
	$qry->execute('select plt_id,charName,charID,password from kb3_pilots a,kb3_api_user b where plt_name=charName');
	while ($l= $qry->getRow())
		$ligne[]=$l;
	foreach ($ligne as $char)
	{
		user::register(slashfix($char['charName']), slashfix($char['password']), $char['plt_id'],$char['charID']);
		$qry->execute("select usr_id from kb3_user where usr_login='".slashfix($char['charName'])."'");
		$tmp= $qry->getRow();
		$qry->execute('update kb3_api_user set password ='.$tmp['usr_id'].' where charID='.intval($char['charID']));
	}
	$qry->execute('ALTER TABLE `kb3_api_user` CHANGE `password` `usr_id` INT(10)');
	$html.='<span class="losscount">[instdb] Migration of all the users from V0.3=>V0.4</span><br>';
config::set('apiuser_version','0.3');
}

if (floatval(config::get('apiuser_version')<'0.4'))
{
		//Add a key
	$qry->execute('ALTER TABLE kb3_all_corp  ADD PRIMARY KEY (all_id,corp_id)');
	config::set('apiuser_version','0.4');

}
if (floatval(config::get('apiuser_version')<'0.5'))
{
		// Add standard role
	$qry->execute("delete from  kb3_roles where rol_site='".KB_SITE."'");
	$qry->execute('INSERT INTO `kb3_roles` VALUES("1", "'.KB_SITE.'", "admin", "Administrator level")');
	$qry->execute('INSERT INTO `kb3_roles` VALUES("2", "'.KB_SITE.'", "comment", "Allow to post Comment");');
	$qry->execute('INSERT INTO `kb3_roles` VALUES("3", "'.KB_SITE.'", "post_killmail", "Allow to post Killmail");');
	$qry->execute('INSERT INTO `kb3_roles` VALUES("4", "'.KB_SITE.'", "access", "Allow to consult the Killboard");');
	$qry->execute('INSERT INTO `kb3_roles` VALUES("5", "'.KB_SITE.'", "autopilot", "Allow Acces to the Autopilot Mod");');
	$html.='<span class="losscount">[instdb] Adding Standard Roles</span><br>';
    config::set('apiuser_version','0.5');
}