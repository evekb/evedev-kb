<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Create a box to display information in.
 *
 * Box will contain a title, an icon and an array of items.
 * @package EDK
 */
class Box
{
    /**
     * Create a box and set the title.
	 *
	 * @param string $title 
	 */
    function Box($title = '')
    {
        $this->title_ = $title;
        $this->box_array = array();
    }

    /**
     * Set the Icon.
	 *
	 * @param string $icon
	 */
    function setIcon($icon)
    {
        $this->icon_ = $icon;
    }

    /**
     * Add something to the contents array that we send to smarty later.
     *
	 * Images can have a width or height specified with a default of 145 pixels.
	 * Links have an optional onlick setting for javascript functions.
	 *
	 * @param string $type type of link. Types can be caption, img, link, points.
	 * @param string $name Name to display for option.
	 * @param string $url URL to use for a link.
	 * @param integer $width Image width.
	 * @param integer $height Image height.
	 * @param string|boolean $onclick optional javascript for links
	 */
    function addOption($type, $name, $url = '', $width = 145, $height = 145, $onclick = false)
    {
        $this->box_array[] = array('type' => $type, 'name' => $name,
			'url' => $url, 'width' => $width, 'height' => $height,
			'onclick' => $onclick);
    }
    /**
     * Generate the html from the template.
	 *
	 * @global Smarty $smarty
	 * @return string
	 */
    function generate()
    {
        global $smarty;

        $smarty->assign('count', count($this->box_array));
        if ($this->icon_)
        {
            $smarty->assign('icon', config::get('cfg_img')."/".$this->icon_);
        }
        $smarty->assign('title', $this->title_ );
        $smarty->assign('items', $this->box_array);

		return $smarty->fetch(get_tpl('box'));
    }
}
