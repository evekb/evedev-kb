<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * Core ajax functions are included in this page. Registered functions are
 * called once all mods are initialised.
 * @package EDK
 */

require_once('common/xajax/xajax.php');

$xajax->register(XAJAX_FUNCTION, "doAjaxSearch");
$xajax->register(XAJAX_FUNCTION, "getComments");
$xajax->register(XAJAX_FUNCTION, "postComments");

edk_xajax::xajax();

/**
 * Search function for the search.php page.
 *
 * @param string $searchphrase
 * @param string $type
 * @param integer $limit
 * @return xajaxResponse
 */
function doAjaxSearch($searchphrase='', $type='pilot', $limit = 10)
{
	$qry = new DBPreparedQuery();

	switch($type)
	{
		case "pilot":
			$sql = "select plt.plt_name as name1, crp.crp_name as name2, plt.plt_id as id
				  from kb3_pilots plt, kb3_corps crp
				 where plt.plt_name  like ?
				   and plt.plt_crp_id = crp.crp_id
				 order by plt.plt_name LIMIT $limit";
			break;
		case "corp":
			$sql = "select crp.crp_name as name1, ali.all_name as name2, crp.crp_id as id
				  from kb3_corps crp, kb3_alliances ali
				 where crp.crp_name like  ?
				   and crp.crp_all_id = ali.all_id
				 order by crp.crp_name LIMIT $limit";
			break;
		case "alliance":
			$sql = "select ali.all_name as name1, '' as name2, ali.all_id as id
				  from kb3_alliances ali
				 where ali.all_name like  ?
				 order by ali.all_name LIMIT $limit";
			break;
		case "system":
			$sql = "select sys.sys_name as name1, reg.reg_name as name2, sys.sys_id as id
				  from kb3_systems sys, kb3_constellations con, kb3_regions reg
				 where sys.sys_name like  ?
					and con.con_id = sys.sys_con_id and reg.reg_id = con.con_reg_id
				 order by sys.sys_name LIMIT $limit";
			break;
		case "item":
			$sql = "select typeName as name1, '' as name2, typeID as id
				from kb3_invtypes where typeName like ? LIMIT $limit";
			break;
		default:
			$objResponse = new xajaxResponse();
			$objResponse->assign('searchresults', "innerHTML", 'Invalid type');
			return $objResponse;

	}
	$name1 = 'No result';
	$name2 = '';
	$id = 0;
	$qry->prepare($sql);
	$searchphrase2 = $searchphrase.'%';
	$qry->bind_param('s', $searchphrase2);
	$qry->bind_result($name1, $name2, $id);
	$result = '';

	if(!$qry->execute() )
	{
		$result = $qry->getErrorMsg();
	}

	else
	{
		if(!$qry->recordCount()) $result = "No results";
		else
		{
			$result = "<table class='kb-table' width='450'><tr class='kb-table-header'>";
				switch($type)
				{
					case "pilot":
						$result .= "<td>Pilot</td><td>Corporation</td></tr>";
						break;
					case "corp":
						$result .= "<td>Corporation</td><td>Alliance</td></tr>";
						break;
					case "alliance":
						$result .= "<td>Alliance</td><td></td></tr>";
						break;
					case "system":
						$result .= "<td>System</td><td>Region</td></tr>";
						break;
					case "item":
						$result .= "<td>Item</td><td></td></tr>";
						break;
				}
			while($qry->fetch())
			{
				$result .= "<tr class='kb-table-row-even'><td><a href='";
				switch($type)
				{
					case "pilot":
						$result .= edkURI::page('pilot_detail', $id, 'plt_id')."'>";
						break;
					case "corp":
						$result .= edkURI::page('corp_detail', $id, 'crp_id')."'>";
						break;
					case "alliance":
						$result .= edkURI::page('alliance_detail', $id, 'all_id')."'>";
						break;
					case "system":
						$result .= edkURI::page('system_detail', $id, 'sys_id')."'>";
						break;
					case "item":
						$result .= edkURI::page('invtype', $id)."'>";
						break;
				}
				$result .= $name1."</a></td><td>".$name2."</td></tr>";
			}
			$result .= "</table>";
		}
	}
	$objResponse = new xajaxResponse();
	$objResponse->assign('searchresults', "innerHTML", $result);
	return $objResponse;
}

/**
 * Return all comments for a given kill
 *
 * @global Smarty $smarty
 * @param integer $kll_id
 * @param string $message
 * @return xajaxResponse
 */
function getComments($kll_id, $message = '')
{
	if (config::get('comments'))
	{
		$kll_id = intval($kll_id);
		$comments = new Comments($kll_id);
		global $smarty;
		$config = new Config();
		if(!$smarty)
		{
			$smarty = new Smarty();
			$themename = config::get('theme_name');
			if(is_dir('./themes/'.$themename.'/templates'))
				$smarty->template_dir = './themes/'.$themename.'/templates';
			else $smarty->template_dir = './themes/default/templates';

			if(!is_dir(KB_CACHEDIR.'/templates_c/'.$themename))
				mkdir(KB_CACHEDIR.'/templates_c/'.$themename);
			$smarty->compile_dir = KB_CACHEDIR.'/templates_c/'.$themename;

			$smarty->cache_dir = KB_CACHEDIR.'/data';
			$smarty->assign('theme_url', THEME_URL);
			$smarty->assign('style', $stylename);
			$smarty->assign('img_url', config::get('cfg_img'));
			$smarty->assign('img_host', IMG_HOST);
			$smarty->assign('kb_host', KB_HOST);
			$smarty->assignByRef('config', $config);
			$smarty->assign('is_IGB', IS_IGB);
			$smarty->assign('kll_id', $kll_id);
		}
		$smarty->assignByRef('page', new Page("Comments"));
		$message = $message.$comments->getComments(true);
	}
	else $message = '';

	$objResponse = new xajaxResponse();
	$objResponse->assign('kl-detail-comment-list', "innerHTML", $message);
	return $objResponse;

}

/**
 * Post a new comment.
 * 
 * @global Smarty $smarty
 * @param integer $kll_id
 * @param string $author
 * @param string $comment
 * @param string $password
 * @return xajaxResponse
 */
function postComments($kll_id, $author, $comment, $password = '')
{
	if (config::get('comments'))
	{
		$kll_id = intval($kll_id);
		$comments = new Comments($kll_id);
		global $smarty;
		$config = new Config();
		$page = new Page("Comments");

		$comments = new Comments($kll_id);
		$pw = false;
		if (!config::get('comments_pw') || $page->isAdmin())
		{
			$pw = true;
		}
		if ($pw || crypt($password, config::get("comment_password")) == config::get("comment_password"))
		{
			if ($comment == '')
			{
				return getComments($kll_id, 'Error: The silent type, hey? Good for you, bad for a comment.');
			}
			else
			{
				if (!$author)
				{
					$author = 'Anonymous';
				}
				$comments->addComment($author, $comment);
				return getComments($kll_id);
			}
		}
		else
		{
			// Password is wrong
			return getComments($kll_id, 'Error: Wrong Password');
		}
	}
	else return false;
}