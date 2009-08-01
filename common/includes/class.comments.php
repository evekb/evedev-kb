<?php 
//! Store and retrieve comments for each killmail.

//! This class is used when the details of a kill are viewed.
class Comments 
{ 
    //! Create a Comments object for a particular kill.
    
    /*
     * \param $kll_id The kill id to attach comments to or retrieve for.
     */
    function Comments($kll_id) 
    {
        $this->id_ = $kll_id; 
        $this->raw_ = false; 

        $this->comments_ = array(); 
    } 
    //! Retrieve comments for a kill.
    
    //! The kill id is set when the Comments object is constructed.
    function getComments() 
    { 
        global $smarty;
        
        $qry = new DBQuery(true); 
        $qry->execute("SELECT *,id FROM kb3_comments WHERE `kll_id` = '".
            $this->id_."' order by posttime asc");
        while ($row = $qry->getRow()) 
        { 
            $this->comments_[] = array('time' => $row['posttime'], 'name' => $row['name'],
              'comment' => stripslashes($row['comment']), 'id' => $row['id']); 
        } 
        $smarty->assign_by_ref('comments', $this->comments_);
		$smarty->assign('norep', time()%3700);
        return $smarty->fetch(get_tpl('block_comments')); 
    } 
    //! Add a comment to a kill.
    
    /*!
     * The kill id is set when the Comments object is constructed.
     * \param $name The name of the comment poster.
     * \param $text The text of the comment to post.
     */
    function addComment($name, $text) 
    { 
        $comment = $this->bbencode($text); 

        $name = slashfix(strip_tags($name)); 
        $qry = new DBQuery(true); 
        $qry->execute("INSERT INTO kb3_comments (`kll_id`,`comment`,`name`,`posttime`) 
                       VALUES ('".$this->id_."','".$comment."','".$name."','".kbdate('Y-m-d H:i:s')."')"); 
        $id = $qry->getInsertID(); 
        $this->comments_[] = array('time' => kbdate('Y-m-d H:i:s'), 
            'name' => $name, 'comment' => stripslashes($comment), 'id' => $id); 

        // create comment_added event 
        event::call('comment_added', $this); 
    } 
    //! Delete a comment.
    
    /*
     * \param $c_id The id of the comment to delete.
     */
    function delComment($c_id) 
    { 
        $qry = new DBQuery(true); 
        $qry->execute("DELETE FROM kb3_comments WHERE id='".$c_id); 
    } 
    //! Set whether to post the raw comment text or bbencode it.
    function postRaw($bool) 
    { 
        $this->raw_ = $bool; 
    } 
    //! bbencode a string.
    
    //! Used before posting a comment.
    function bbencode($string) 
    { 
        if (!$this->raw_) 
        { 
            $string = strip_tags(stripslashes($string)); 
        } 
        $string = str_replace(array('[b]','[/b]','[i]','[/i]','[u]','[/u]'), 
                              array('<b>','</b>','<i>','</i>','<u>','</u>'), $string); 
        $string = preg_replace('^\[color=(.*?)](.*?)\[/color]^', '<font color="\1">\2</font>', $string); 
        $string = preg_replace('^\[kill=(.*?)](.*?)\[/kill]^', '<a href="\?a=kill_detail&kll_id=\1">\2</a>', $string); 
        $string = preg_replace('^\[pilot=(.*?)](.*?)\[/pilot]^', '<a href="\?a=pilot_detail&plt_id=\1">\2</a>', $string); 
        return nl2br(addslashes($string)); 
    } 
} 
?>