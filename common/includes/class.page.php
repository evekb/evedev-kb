<?php
require_once('common/includes/class.navigation.php');


//! Construct an output page.
class Page
{
    //! Construct a Page class with the given title.

    //! Page generation timer is started on Page creation.
    function Page($title = '', $cachable = true)
    {
        if (!config::get('public_stats'))
        {
            config::set('public_stats','do nothing');
        }

        $this->title_ = $title;
        $this->admin_ = false;
        $this->headlines = $this->bodylines = array();

        $this->igb_ = IS_IGB;

        $this->timestart_ = strtok(microtime(), ' ') + strtok('');

        $this->killboard_ = new Killboard(KB_SITE);

        $this->cachable_ = $cachable;
        $this->cachetime_ = 5;

        // if you have probs with missing tables uncomment this and
        // check_navigationtable();
    }
    //! Set the content html that is displayed in the main body panel.
    function setContent($html)
    {
        $this->contenthtml_ = $html;
    }
    //! Set the context html that is displayed in the sidebar.
    function addContext($html)
    {
        $this->contexthtml_ .= $html;
    }
    //! Create and display an error message.
    function error($message)
    {
        global $smarty;

        $smarty->assign('error', $message);
        $this->setContent($smarty->fetch(get_tpl('error')));
        $this->generate();
    }
    //! Add a line to the header html.
    function addHeader($line)
    {
        $this->headlines[] = $line;
    }
    //! Add a line to the body html.
    function addBody($line)
    {
        $this->bodylines[] = $line;
    }
    //! Generate the output html.

    //! Output is constructed from the variables passed in through the
    //! add methods and the index.tpl.
    function generate()
    {
        global $smarty;

        $smarty->assign('kb_title', KB_TITLE.' Killboard - '.$this->title_);

        $style = config::get('style_name');
        $smarty->assign('style', $style);

        if ($this->onload_)
        {
            $smarty->assign('on_load', ' onload="'.$this->onload_.'"');
        }

        // header
        event::call('page_assembleheader', $this);
        $smarty->assign('page_headerlines', join("\n", $this->headlines));

        event::call('page_assemblebody', $this);
        $smarty->assign('page_bodylines', join("\n", $this->bodylines));

        if (!$this->igb_)
        {
            if (MAIN_SITE)
            {
                $smarty->assign('banner_link', MAIN_SITE);
            }
            $banner = config::get('style_banner');
            if ($banner == 'custom')
            {
                $banner = 'kb-banner.jpg';
            }
            $smarty->assign('banner', $banner);

            $nav = new Navigation();
            $nav->setSite($_GET['a']);
            $menu = $nav->generateMenu();
            $w = floor(100 / count($menu->get()));

            $smarty->assign('menu_w',$w.'%');
            $smarty->assign('menu', $menu->get());
        }

        //check if banner is a swf
        $bannerExn = substr($banner,-3);
        if (strtoupper($bannerExn) == 'SWF')
        {
            $smarty->assign('bannerswf', 'true');
        }
        else
        {
            $smarty->assign('bannerswf', 'false');
        }

        $smarty->assign('page_title', $this->title_);

        $this->timeend_ = strtok(microtime(), ' ') + strtok('');
        $this->processingtime_ = $this->timeend_ - $this->timestart_;

        $qry = new DBQuery();
            $smarty->assign('profile_sql_cached', $qry->queryCachedCount());
        $smarty->assign('profile_sql', $qry->queryCount());
        $smarty->assign('profile_time', round($this->processingtime_,4));
        $smarty->assign('sql_time', round($qry->totalexectime_,4));
        if($this->isAdmin() || config::get('cfg_profile') || intval(KB_PROFILE)) $smarty->assign('profile', 1);
        $smarty->assign('content_html', $this->contenthtml_);
        if (config::get('user_showmenu'))
        {
            $this->contexthtml_ = user::menu().$this->contexthtml_;
        }
        $smarty->assign('context_html', $this->contexthtml_);
        event::call('smarty_displayindex', $smarty);
        $smarty->display(get_tpl('index'));
    }
    //! Return whether this will display as an igb page.
    function igb()
    {
        return $this->igb_;
    }
    //! Set the onload variable for Smarty.
    function setOnLoad($onload)
    {
        $this->onload_ = $onload;
    }
    // Set the page title.
    function setTitle($title)
    {
        $this->title_ = $title;
    }
    //! If this is not an admin session redirect to the login page.
    function setAdmin()
    {
        if (!Session::isAdmin())
        {
            header("Location: ?a=login");
            echo '<a href="?a=login">Login</a>';
            exit;
        }
    }
    //! Return whether this is an admin session.
    function isAdmin()
    {
        return Session::isAdmin();
    }
    //! Return whether this is a superadmin session.
    function isSuperAdmin()
    {
        return Session::isSuperAdmin();
    }
    //! If this is not a superadmin session redirect to the login page.
    function setSuperAdmin()
    {
        if (!Session::isSuperAdmin())
        Header("Location: ?a=login");
    }
    //! Set whether this page is cacheable.
    function setCachable($cachable)
    {
        $this->cachable_ = $cachable;
    }
    //! Set how long to cache this page.
    function setCacheTime($cachetime)
    {
        $this->cachetime_ = $cachetime;
    }

}
//! Construct a menu.

//! A Menu is a wrapper around an array of links and matching text.
class Menu
{
    //! Construct a blank side menu.
    function Menu()
    {
        $this->menu_ = array();
    }
    //! Return the array of menu options.
    function get()
    {
        return $this->menu_;
    }
    //! Add a link and text to the array of menu options.
    function add($link, $text)
    {
        $this->menu_[] = array('link' => $link, 'text' => $text);
    }
}
?>