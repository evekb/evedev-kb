<?php
/* ex: set ts=4: set sw=4: set expandtab */
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
define('IS_IMG_MAX', 256);

$page = new Page('Capture portrait');

$html .= '<html><head><title>Update portrait</title></head><body>';

if (!$page->igb())
{
    $html .= 'You need to access this page from the EVE ingame browser.';
}
else
{
    if (($_SERVER['HTTP_EVE_TRUSTED'] == 'no'))
    {
        Header('eve.trustme:http://'.$_SERVER['HTTP_HOST'].'/::Need trust to grab character portrait.');
        $html .= '<h1>Trust Required</h1>';
        $html .= 'This site needs to be trusted in order to grab your character portrait.';
    }
    else
    {
        $now = date('Y-m-d H:m:s');
        $alliance = new Alliance();
        $all_id = $alliance->add($_SERVER['HTTP_EVE_ALLIANCENAME']);
        $corp = new Corporation();
        $crp_id = $corp->add($_SERVER['HTTP_EVE_CORPNAME'], $alliance, $now);
        $pilot = new Pilot();
        $plt_id = $pilot->add($_SERVER['HTTP_EVE_CHARNAME'], $corp, $now);
        $id = intval($_SERVER['HTTP_EVE_CHARID']);
        $pilot->setCharacterID($id);
        if (file_exists("cache/portraits/".$id."_256.jpg") && 1 == $_REQUEST['force']) 
        {
            // Remove just in case.
            @unlink("cache/portraits/".$id."_32.jpg");
            @unlink("cache/portraits/".$id."_64.jpg");
            @unlink("cache/portraits/".$id."_128.jpg");
            @unlink("cache/portraits/".$id."_256.jpg");
            if (0 == create_portraits($id))
            {
                $message = "Character portrait uploaded.";
            }
            else
            {
                $message = "Character portrait update failed.";
            }
        }
        elseif (file_exists("cache/portraits/".$id."_256.jpg"))
        {
            $message = "Character portrait not updated, as it already exists. <a href='".KB_HOST."?a=portrait_grab&force=1'>Click</a> to force an update.";
        }
        else
        {
            if (0 == create_portraits($id))
            {
                $message = "Character portrait uploaded.";
            }
            else
            {
                $message = "Character portrait update failed.";
            }
            
        }
        $html .= "<img src='".$pilot->getPortraitURL(64)."' border='0' />";
        $html .= "<br />$message <br />$port_error_msg <br />";
        $html .= "<a href='".KB_HOST."?a=igb'>Return</a> to the killboard.<br />";
    }
}

$html .= "</body></html>";

$page->setContent($html);
$page->generate();

function create_portraits($id) {
    global $port_error_msg;
    if (1 != ini_get('allow_url_fopen')) {
        $port_error_msg = 'This web host does not allow PHP to create HTTP connections.  Check allow_url_fopen.';
        return -99;
    }
    if (! is_writable('cache/portraits/')) {
        $port_error_msg = 'The portraits directory is not writable.  Please fix this.';
        return -99;
    }

    $img = @imagecreatefromjpeg("http://img.eve.is/serv.asp?s=".IS_IMG_MAX."&c=".$id);
    if ($img)
    {
        $dims = array (32, 64, 128);
        foreach ($dims as $dim) {
            $newimg = @imagecreatetruecolor($dim,$dim);
            @imagecopyresampled($newimg, $img, 0,0,0,0,$dim,$dim,IS_IMG_MAX,IS_IMG_MAX);
            @imagejpeg($newimg, "cache/portraits/".$id."_".$dim.".jpg");
        }
        @imagejpeg($img, "cache/portraits/".$id . "_256.jpg");
        $return = 0;
    }
    else
    {
        $port_error_msg = 'Attempting to create an image failed, and it was not because the directory was not writable.';
        $return = -99;
    }

    if (filesize("cache/portraits/".$id."_256.jpg") == '3863')
    {
        $port_error_msg = 'CCP have not generated an image for you yet.  Be patient.';
    }
    return $return;
}
?>
