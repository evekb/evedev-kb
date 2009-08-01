<?php

class pageAssembly
{
    function __construct()
    {
        $this->assemblyQueue = array();
    }

    function assemble()
    {
        event::call('pageAssembly_assemble', &$this);

        $output = '';
        foreach ($this->assemblyQueue as $id => $object)
        {
            usort($object['addBefore'], array(&$this, 'prioSortHelper'));
            foreach ($object['addBefore'] as $callback)
            {
                $output .= $this->call($callback['callback']);
            }

            $text = $this->call($object['callback']);
            foreach ($object['filter'] as $callback)
            {
                $text = $this->callFilter($callback['callback'], $text);
            }
            $output .= $text;

            foreach ($object['addBehind'] as $callback)
            {
                $output .= $this->call($callback['callback']);
            }
        }

        return $output;
    }

    function prioSortHelper($a, $b)
    {
        return ($a['prio'] < $b['prio']) ? -1 : 1;
    }

    function call($callback)
    {
        // self registered
        if (strpos($callback, '->'))
        {
            $cb = explode('->', $callback);
            if ($cb[0] == 'this')
            {
                if (is_callable(array($this, $cb[1])))
                {
                    return call_user_func(array($this, $cb[1]));
                }
                return false;
            }
        }

        // static calls
        if (strpos($callback, '::'))
        {
            $cb = explode('::', $callback);
            if (is_callable($cb))
            {
                return call_user_func($cb);
            }
            return false;
        }

        // rest
        if (is_callable($callback))
        {
            return call_user_func($callback);
        }
        return false;
    }

    function callFilter($callback, $argument)
    {
        if (is_callable($callback))
        {
            return call_user_func($callback, $argument);
        }
        return $argument;
    }

    function queue($id)
    {
        $this->assemblyQueue[$id] = array('id' => $id, 'addBehind' => array(),
                                          'addBefore' => array(), 'filter' => array(),
                                          'callback' => 'this->'.$id);
    }

    function addBehind($id, $callback, $priority = 5)
    {
        $this->assemblyQueue[$id]['addBehind'][] = array('prio' => $priority,
                                                         'callback' => $callback);
    }

    function addBefore($id, $callback, $priority = 5)
    {
        $this->assemblyQueue[$id]['addBefore'][] = array('prio' => $priority,
                                                         'callback' => $callback);
    }

    function replace($id, $callback)
    {
        $this->assemblyQueue[$id]['callback'] = $callback;
    }

    function filter($id, $callback, $priority = 5)
    {
        $this->assemblyQueue[$id]['filter'][] = array('prio' => $priority,
                                                      'callback' => $callback);
    }

    function delete($id)
    {
        if (!empty($this->assemblyQueue[$id]))
        {
            unset($this->assemblyQueue[$id]);
        }
    }
}