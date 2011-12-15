<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Store and retrieve comments for each killmail.
 *
 * This class is used when the details of a kill are viewed.
 * @package EDK
 */
class Comments
{
	/** @var int Kill ID */
	protected $id_ = 0;
	/** @var boolean Whether to store raw text */
	protected $raw_ = false;
	/** @var array Array of comments */
	protected $comments_ = array();
	/**
	 * Create a Comments object for a particular kill.
	 *
	 * @param integer $kll_id The kill id to attach comments to or retrieve for.
	 */
	function Comments($kll_id)
	{
		$this->id_ = (int)$kll_id;
	}

	/**
	 * Retrieve comments for a kill.
	 *
	 * The kill id is set when the Comments object is constructed.
	 *
	 * @global Smarty $smarty
	 * @param boolean $commentsOnly
	 * @return string
	 */
	function getComments($commentsOnly = false)
	{
		global $smarty;

		$qry = DBFactory::getDBQuery();
		// NULL site id is shown on all boards
		$qry->execute("SELECT *,id FROM kb3_comments WHERE `kll_id` = '".
				$this->id_."' AND (site = '".KB_SITE
				."' OR site IS NULL) order by posttime asc");
		while ($row = $qry->getRow()) {
			$this->comments_[] = array(
					'time' => $row['posttime'],
					'name' => trim($row['name']),
					'encoded_name' => urlencode(trim($row['name'])),
					'comment' => $row['comment'],
					'id' => $row['id'],
					'ip' => $row['ip']);
		}
		$smarty->assignByRef('comments', $this->comments_);
		$smarty->assign('norep', time() % 3700);
		if ($commentsOnly) {
			return $smarty->fetch(get_tpl('comments_comments'));
		} else {
			return $smarty->fetch(get_tpl('block_comments'));
		}
	}

	/**
	 * Add a comment to a kill.
	 *
	 * The kill id is set when the Comments object is constructed.
	 * @param string $name The name of the comment poster.
	 * @param string $text The text of the comment to post.
	 */
	function addComment($name, $text)
	{
		$comment = $this->bbencode(trim($text));
		$name = trim($name);

		$qryP = new DBPreparedQuery();

		$sql = "INSERT INTO kb3_comments (`kll_id`,`site`, `comment`,`name`,`posttime`, `ip`)
                       VALUES (?, ?, ?, ?, ?, ?)";
		$qryP->prepare($sql);
		$site = KB_SITE;
		$date = kbdate('Y-m-d H:i:s');
		$ip = logger::getip();
		$params = array('isssss', &$this->id_, &$site, &$comment, &$name, &$date, &$ip);
		$qryP->bind_params($params);
		$qryP->execute();

		$id = $qryP->getInsertID();
		$this->comments_[] = array('time' => kbdate('Y-m-d H:i:s'),
				'name' => $name, 'comment' => $comment, 'id' => $id);

		// create comment_added event
		event::call('comment_added', $this);
	}

	/**
	 * Delete a comment.
	 * @param integer $c_id The id of the comment to delete.
	 */
	function delComment($c_id)
	{
		$qry = DBFactory::getDBQuery();
		$c_id = (int) $c_id;
		$qry->execute("DELETE FROM kb3_comments WHERE id='".$c_id);
	}

	/**
	 * Set whether to post the raw comment text or bbencode it.
	 *
	 * @param integer $bool
	 */
	function postRaw($bool)
	{
		$this->raw_ = (boolean)$bool;
	}

	/**
	 * bbencode a string.
	 * Used before posting a comment.
	 *
	 * @param string $string
	 * @return string
	 */
	function bbencode($string)
	{
		if (!$this->raw_) {
			$string = htmlspecialchars(strip_tags(stripslashes($string)));
		}
		$string = str_replace(array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]'),
				array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>'), $string);
		$string = preg_replace('^\[color=(.*?)](.*?)\[/color]^',
				'<span style="color:\1">\2</span>', $string);
		$string = preg_replace('^\[kill=(.*?)](.*?)\[/kill]^',
				'<a href="'.KB_HOST.'/?a=kill_detail&amp;kll_id=\1">\2</a>', $string);
		$string = preg_replace('^\[pilot=(.*?)](.*?)\[/pilot]^',
				'<a href="'.KB_HOST.'/?a=pilot_detail&amp;plt_id=\1">\2</a>', $string);
		return nl2br($string);
	}
	
	/**
	 * Get the ID for the kill these comments relate to.
	 * @return integer The ID for the kill these comments relate to.
	 */
	function getID()
	{
		return $this->id_;
	}

}

