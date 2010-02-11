<?php

class event
{
	private static $events = array();

	function event()
	{
		trigger_error('The class "event" may only be invoked statically.', E_USER_ERROR);
	}

	public static function register($event, $callback)
	{
		if (is_array($callback))
		{
			if (is_object($callback[0]))
			{
				trigger_error('The supplied callback has to point to a static method.', E_USER_WARNING);
				return;
			}

			// store callbacks as 'object::function'
			$callback = $callback[0].'::'.$callback[1];
		}
		if (!strpos($callback, '::'))
		{
			trigger_error('The supplied callback "'.$callback.'" has to point to a static method.', E_USER_WARNING);
			return;
		}

		// we store the event callbacks reverse so you need one function for every event
		event::_put($callback, $event);
	}

	public static function call($event, &$object)
	{
		foreach (self::$events as $callback => $c_event)
		{
			// if the callback registered to the calling event we'll try to use his callback
			if ($event == $c_event)
			{
				$cb = explode('::', $callback);
				if (is_callable($cb))
				{
					// TODO: If nothing is breaking the array form should be
					// standard so references are passed.
					if (true || is_object($object))
					{
						//call_user_func($cb, &$object);
						call_user_func_array($cb, array(&$object));
					}
					else
					{
						call_user_func($cb, $object);
					}
				}
				else
				{
					trigger_error('The stored event handler "'.$c_event.'" is not callable (CB: "'.$callback.'").', E_USER_WARNING);
				}
			}
		}
	}

	private static function _put($key, $data)
	{
		self::$events[$key] = $data;
	}
}
