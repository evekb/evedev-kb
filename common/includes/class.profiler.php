<?php
class Profiler
{
    function Profiler()
    {
    }

    function start()
    {
        $this->starttime_ = strtok(microtime(), ' ') + strtok('');
    }

    function stop()
    {
        $this->stoptime_ = strtok(microtime(), ' ') + strtok('');
    }

    function getTime()
    {
        return $this->stoptime_ - $this->starttime_;
    }
}
?>