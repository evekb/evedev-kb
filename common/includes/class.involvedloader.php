<?php


class involved
{
    function involved()
    {
        trigger_error('The class "involved" may only be invoked statically.', E_USER_ERROR);
    }

    function load(&$killlist, $type = 'kill')
    {
        if ($type == 'kill')
        {
            if (PILOT_ID)
                $killlist->addInvolvedPilot(PILOT_ID);
            elseif (CORP_ID)
                $killlist->addInvolvedCorp(CORP_ID);
            elseif (ALLIANCE_ID)
                $killlist->addInvolvedAlliance(ALLIANCE_ID);
        }
        elseif ($type == 'loss')
        {
            if (PILOT_ID)
                $killlist->addVictimPilot(PILOT_ID);
            elseif (CORP_ID)
                $killlist->addVictimCorp(CORP_ID);
            elseif (ALLIANCE_ID)
                $killlist->addVictimAlliance(ALLIANCE_ID);
        }
    }

}
?>