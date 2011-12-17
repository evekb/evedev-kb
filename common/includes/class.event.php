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
class event
{
	private static $events = array();

	/**
	 * Register a callback for an event. Callbacks are of the form
	 * array('Class', 'Method');
	 *
	 * @param string $event Event name.
	 * @param array $callback Callback to be called when the event is triggered
	 *
	 * @return boolean True on success, false on failure. 
	 */
	public static function register($event, $callback)
	{
		if (is_array($callback)) {
			if (is_object($callback[0])) {
				trigger_error('The supplied callback has to point to a static method.',
								E_USER_WARNING);
				return false;
			}
		} else if (!strpos($callback, '::')) {
			trigger_error('The supplied callback "'.$callback.'" has to point to a static method.',
							E_USER_WARNING);
			return false;
		} else {
			// Accept callback in string form 'Class::method' for legacy events.
			$callback = explode('::', $callback);
		}
		if (!is_callable($callback, false, $callable_name)) {
			trigger_error('The stored event handler "'.$event
					.'" is not callable (CB: "'.join("::", $callback).'").',
							E_USER_WARNING);
			return false;
		}

		event::_put($event, $callback);
		return true;
	}

	/**
	 * Call an event. Trigger any callbacks registered for this event.
	 *
	 * @param string $event event name.
	 * @param mixed $object Object to pass to the callback.
	 */
	public static function call($event, &$object)
	{
		if (isset(self::$events[$event])) {
			foreach (self::$events[$event] as $callback){
				// if the callback registered to the calling event we'll try to use his callback
				call_user_func_array($callback, array(&$object));
			}
		}
	}

	private static function _put($key, $data)
	{
		self::$events[$key][] = $data;
	}
}
