<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class Profiler
{
    function Profiler()
    {
    }

    function start()
    {
        $this->starttime_ = microtime(true);
    }

    function stop()
    {
        $this->stoptime_ = microtime(true);
    }

    function getTime()
    {
        return $this->stoptime_ - $this->starttime_;
    }
}