<?php
/**
* Author: Doctor Z
* eMail:  east4now11@hotmail.com
* @package EDK
*/

/**
 * @package EDK
 */
class RSSTable extends KillListTable
{
    function RSSTable($kill_list)
    {
        $this->limit = 0;
        $this->offset = 0;

        $this->kill_list_ = $kill_list;
        $this->daybreak_ = true;
    }

    function generate()
    {
        $this->kill_list_->rewind();

        while ($kill = $this->kill_list_->getKill()) {
			if ($kill->isClassified()) {
				continue;
			}
            /* date in format:  Tue, 03 Jun 2003 09:39:21 GMT 
            Hack added because the time is not parsed correctly by strtotime()
            */
            $timestamp = $kill->getTimeStamp();
            $timestring = explode(" ", $timestamp);
            $datestring = strftime("%a, %d %b %Y " , strtotime($timestamp));
            $datestring .= $timestring[1];
            $datestring .= strftime(" %Z" , strtotime($timestamp));
            $html .= "<item>
    <title>".$kill->getVictimName()." was killed</title>
    <description>
    <![CDATA[
        <p><b>Ship:</b> ".$kill->getVictimShipName()."
            <br /><b>Victim:</b> ".$kill->getVictimName()."
            <br /><b>Corp:</b> ".$kill->getVictimCorpName()."
            <br /><b>Alliance:</b> ".$kill->getVictimAllianceName()."
            <br /><b>System:</b> ".$kill->getSolarSystemName()."
            <br /><b>Date:</b> ".$timestamp."
            <br />
            <br /><b>Killed By:</b>
            <br /><b>Final Blow:</b> ".$kill->getFBPilotName()."
            <br /><b>Corp:</b> ".$kill->getFBCorpName()."
            <br /><b>Alliance:</b> ".$kill->getFBAllianceName()."
        </p>
     ]]>
    </description>
    <guid>".edkURI::page('kill_detail', $kill->getID(), 'kll_id')."</guid>
    <pubDate>".$datestring."</pubDate>
</item>\n";
        }

        return $html;
    }
}
