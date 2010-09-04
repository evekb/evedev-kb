<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

//! Create a box to display information in.

//! Box will contain a title, an icon and an array of items.
class Box
{
    //! Create a box and set the title.
    function Box($title = '')
    {
        $this->title_ = $title;
        $this->box_array = array();
    }

    //! Set the Icon.
    function setIcon($icon)
    {
        $this->icon_ = $icon;
    }

    //! Add something to the contents array that we send to smarty later.

    /*!
	 * Images can have a width or height specified with a default of 145 pixels.
	 * Links have an optional onlick setting for javascript functions.
	 *
	 * \param $type type of link. Types can be caption, img, link, points.
	 * \param $name Name to display for option.
	 * \param $url URL to use for a link.
	 * \param $width Image width.
	 * \param $height Image height.
	 * \param $onclick optional javascript for links
	 */
    function addOption($type, $name, $url = '', $width = 145, $height = 145, $onclick = false)
    {
        $this->box_array[] = array('type' => $type, 'name' => $name,
			'url' => $url, 'width' => $width, 'height' => $height,
			'onclick' => $onclick);
    }
    //! Generate the html from the template.
    function generate()
    {
        global $smarty;

        $smarty->assign('count', count($this->box_array));
        if ($this->icon_)
        {
            $smarty->assign('icon', IMG_URL."/".$this->icon_);
        }
        $smarty->assign('title', $this->title_ );
        $smarty->assign('items', $this->box_array);

		return $smarty->fetch(get_tpl('box'));
    }
}
