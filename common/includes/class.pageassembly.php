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
class pageAssembly
{

	function __construct()
	{
		$this->assemblyQueue = array();
	}

	/**
	 * Assemble the page and return the HTML.
	 *
	 * @return string
	 */
	function assemble()
	{
		event::call('pageAssembly_assemble', $this);

		$output = '';
		foreach ($this->assemblyQueue as $id => $object) {
			usort($object['addBefore'], array(&$this, 'prioSortHelper'));
			foreach ($object['addBefore'] as $callback) {
				$output .= $this->call($callback['callback']);
			}

			$text = $this->call($object['callback']);
			foreach ($object['filter'] as $callback) {
				$text = $this->callFilter($callback['callback'], $text);
			}
			$output .= $text;

			foreach ($object['addBehind'] as $callback) {
				$output .= $this->call($callback['callback']);
			}
		}

		return $output;
	}

	private function prioSortHelper($a, $b)
	{
		return ($a['prio'] < $b['prio']) ? -1 : 1;
	}

	/**
	 * Call the callback function and return the HTML generated.
	 * @param callback $callback
	 * @return string|false The resulting HTML from the component or false on
	 * error.
	 */
	private function call($callback)
	{
		// self registered
		if (strpos($callback, '->')) {
			$cb = explode('->', $callback);
			if ($cb[0] == 'this') {
				if (is_callable(array($this, $cb[1]))) {
					return call_user_func_array(array($this, $cb[1]),
							array(&$this));
				}
				return false;
			}
		}

		// static calls
		if (strpos($callback, '::')) {
			$cb = explode('::', $callback);
			if (is_callable($cb)) {
				return call_user_func_array($cb, array(&$this));
			}
			return false;
		}

		// rest
		if (is_callable($callback)) {
			return call_user_func($callback, $this);
		}
		return false;
	}

	private function callFilter($callback, $argument)
	{
		if (is_callable($callback)) {
			return call_user_func($callback, $argument);
		}
		return $argument;
	}

	/**
	 * Add a component to the queue. The component is called as $this->$id.
	 *
	 * @param string $id
	 */
	function queue($id)
	{
		$this->assemblyQueue[$id] = array('id' => $id, 'addBehind' => array(),
				'addBefore' => array(), 'filter' => array(),
				'callback' => 'this->'.$id);
	}

	/**
	 * Add a component after an existing component.
	 *
	 * @param string $id The id of the component to add the new one after.
	 * @param callback $callback The callback function to show a component.
	 * @param integer $priority The priority with which to add the new
	 * component.
	 */
	function addBehind($id, $callback, $priority = 5)
	{
		$this->assemblyQueue[$id]['addBehind'][] = array('prio' => $priority,
				'callback' => $callback);
	}

	/**
	 * Add a component before an existing component.
	 *
	 * @param string $id The id of the component to add the new one before.
	 * @param callback $callback The callback function to show a component.
	 * @param integer $priority The priority with which to add the new
	 * component.
	 */
	function addBefore($id, $callback, $priority = 5)
	{
		$this->assemblyQueue[$id]['addBefore'][] = array('prio' => $priority,
				'callback' => $callback);
	}

	/**
	 * Replace a component from a page.
	 *
	 * @param string $id The id of the component to replace.
	 * @param callback $callback The callback function to show a component.
	 */
	function replace($id, $callback)
	{
		$this->assemblyQueue[$id]['callback'] = $callback;
	}

	function filter($id, $callback, $priority = 5)
	{
		$this->assemblyQueue[$id]['filter'][] = array('prio' => $priority,
				'callback' => $callback);
	}

	/**
	 * Delete a component from a page.
	 *
	 * @param string $id The id of the component to delete.
	 */
	function delete($id)
	{
		if (isset($this->assemblyQueue[$id])) {
			unset($this->assemblyQueue[$id]);
		}
	}

}