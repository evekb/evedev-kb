<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/*
* This is the class which should make it easier to add new options to the admin menu
* It may only be invoked statically like: options::add(...);
* more functions will be added as needed, if you want one, add it or contact exi
*
* Note to exi: this pass-via-function stuff suckz, i should rather submit arrays
*/

class options
{
	private static $data = array();

	// i just want to make sure....
	function options()
	{
		trigger_error('The class "options" may only be invoked statically.', E_USER_ERROR);
	}

	// field = generic, subfield = look and feel, name = banner, type = select
	public static function add($field, $subfield, $set, $description, $name, $type, $buildcallback = '', $onchange = '', $hint = '')
	{
		self::$data[$field][$subfield][$set][] = array('descr' => $description, 'name' => $name, 'type' => $type,
			'callback' => $buildcallback,
			'onchange' => $onchange, 'hint' => $hint);
	}

	// fast add uses the last used category by options::cat so you don't have to retype everything
	public static function fadd($description, $name, $type, $buildcallback = '', $onchange = '', $hint = '')
	{
		global $options_faddcat;

		self::$data[$options_faddcat[0]][$options_faddcat[1]][$options_faddcat[2]][] = array('descr' => $description, 'name' => $name, 'type' => $type,
			'callback' => $buildcallback,
			'onchange' => $onchange, 'hint' => $hint);
	}

	// adds a new category of options, used by the menu
	public static function cat($field, $subfield, $set)
	{
		global $options_faddcat;

		$options_faddcat = array($field, $subfield, $set);
	}

	// this will emulate the old options menu
	public static function oldMenu($field, $subfield, $link)
	{
		if(is_array($link)) {
			self::$data[$field][$subfield] = edkURI::build($link);
		} else {
			self::$data[$field][$subfield] = $link;
		}
	}

	// this handles the submit from the optionspage
	public static function handlePost()
	{
		$current = &self::$data[urldecode($_POST['field'])][urldecode($_POST['sub'])];
		foreach ($current as &$elements)
		{
			foreach ($elements as &$element)
			{
				// Record the previous value
				$element['previous'] = config::get($element['name']);
				// for checkboxes we need to set the value to zero if the option is not there
				if ($element['type'] == 'checkbox')
				{
					if ($_POST['option_'.$element['name']] == 'on')
					{
						config::set($element['name'], '1');
					}
					else
					{
						config::set($element['name'], '0');
					}
				}
				elseif($element['type'])
				{
					// edits and options will be set directly
					config::set($element['name'], $_POST['option_'.$element['name']]);
				}
				// for callbacks we check their callback function on postdata to deal with it
				if ($element['onchange'])
				{
					if (!is_callable($element['onchange']))
					{
						trigger_error('Unable to callback to '.$element['onchange'][0].'::'.$element['onchange'][1], E_USER_ERROR);
						return false;
					}
					call_user_func($element['onchange'], $element['name']);
					//continue;
				}

			}
		}
	}

	public static function genOptionsPage()
	{
		$field = urldecode(edkURI::getArg('field', 1));
		$sub = urldecode(edkURI::getArg('sub',2));

		global $smarty, $page;

		if (is_object($page))
		{
			$page->setTitle('Administration - '.$sub);
		}

		// create the option field
		$smarty->assign('field', urlencode($field));
		$smarty->assign('sub', urlencode($sub));

		// save smarty compile_check state because we will call many templates
		// and leaving this enabled causes performance issues
		$cstate = $smarty->compile_check;
		$smarty->compile_check = false;
		$html = $smarty->fetch(get_tpl('admin_options_field_head'));

		// create all option sets
		foreach (self::$data[$field][$sub] as $set => $options)
		{
			$smarty->assign('set', $set);
			$html .= $smarty->fetch(get_tpl('admin_options_set_head'));

			// create all options in the set
			foreach ($options as $option)
			{
				$html .= options::assembleElement($option);
			}
			$html .= $smarty->fetch(get_tpl('admin_options_set_foot'));
		}
		$html .= $smarty->fetch(get_tpl('admin_options_field_foot'));

		// restore compile state
		$smarty->compile_check = $cstate;
		return $html;
	}

	public static function assembleElement(&$element)
	{
		global $smarty;

		// this will extract all options into an array
		$options = array();
		if (strpos($element['type'], ':'))
		{
			$array = explode(':', $element['type']);
			$element['type'] = array_shift($array);

			$max = count($array);
			for ($i=0; $i<=$max; $i+=2)
			{
				// make sure we assign a value
				if (isset($array[$i+1]))
				{
					$options[$array[$i]] = $array[$i+1];
				}
			}
		}

		if ($element['type'] == 'select')
		{
			if (!is_callable($element['callback']))
			{
				trigger_error('Unable to callback to '.$element['callback'][0].'::'.$element['callback'][1], E_USER_ERROR);
				return false;
			}

			$option = call_user_func($element['callback']);
			$smarty->assign('options', $option);
			$smarty->assignByRef('opt', $element);
			return $smarty->fetch(get_tpl('admin_options_select'));
		}

		if ($element['type'] == 'checkbox')
		{
			$smarty->assignByRef('opt', $element);
			return $smarty->fetch(get_tpl('admin_options_checkbox'));
		}

		if ($element['type'] == 'edit')
		{
			$smarty->assignByRef('opt', $element);

			if (!(isset($options['size']) && $options['size']))
			{
				$options['size'] = 20;
			}
			if (!(isset($options['maxlength']) && $options['maxlength']))
			{
				$options['maxlength'] = 80;
			}
			$smarty->assignByRef('options', $options);
			return $smarty->fetch(get_tpl('admin_options_edit'));
		}

		if ($element['type'] == 'password')
		{
			$smarty->assignByRef('opt', $element);

			if (!(isset($options['size']) && $options['size']))
			{
				$options['size'] = 20;
			}
			if (!(isset($options['maxlength']) && $options['maxlength']))
			{
				$options['maxlength'] = 80;
			}
			$smarty->assignByRef('options', $options);
			return $smarty->fetch(get_tpl('admin_options_password'));
		}

		if ($element['type'] == 'textarea')
		{
			$smarty->assignByRef('opt', $element);

			if (!(isset($options['cols']) && !$options['cols']))
			{
				$options['cols'] = 70;
			}
			if (!(isset($options['rows']) && $options['rows']))
			{
				$options['rows'] = 24;
			}
			$smarty->assignByRef('options', $options);
			return $smarty->fetch(get_tpl('admin_options_textarea'));
		}

		// for a custom element we call the callback to get the html we want
		if ($element['type'] == 'custom')
		{
			if (!is_callable($element['callback']))
			{
				trigger_error('Unable to callback to '.$element['callback'][0].'::'.$element['callback'][1], E_USER_ERROR);
				return false;
			}

			$element['html'] = call_user_func($element['callback']);
			$smarty->assignByRef('opt', $element);
			return $smarty->fetch(get_tpl('admin_options_custom'));
		}

		// unknown/not implemented element type
		return $element['name'];
	}

	public static function genAdminMenu()
	{
		// sort the menu alphabetically
		ksort(self::$data);

		// create a standardbox to print all links into
		$menubox = new Box('Options');
		$menubox->setIcon('menu-item.gif');
		foreach (self::$data as $field => $subfields)
		{
			$menubox->addOption('caption', $field);

			foreach ($subfields as $subfield => $array)
			{
				// if this subfield has no options then it is a category
				if (!is_array($array))
				{
					$menubox->addOption('link', $subfield, $array);
					continue;
				}

				// we're not a category, make it clickable
				$menubox->addOption('link', $subfield, edkURI::build(
						array(array('a', 'admin', true),
							array('field', $field, true),
							array('sub', $subfield, true))));
			}
			$lastfield = $field;
		}
		return $menubox->generate();
	}

	// Return the value of an option before it was changed
	public static function getPrevious($key)
	{
		if(!isset(self::$data[urldecode($_POST['field'])][urldecode($_POST['sub'])]))
			return config::get($key);
		$current = self::$data[urldecode($_POST['field'])][urldecode($_POST['sub'])];
		foreach ($current as $element)
		{
			foreach ($element as $subelement)
			{
				if (isset($subelement['name']) && $subelement['name'] == $key)
				{
					if(isset($subelement['previous'])) return $subelement['previous'];
					else return config::get($subelement['name']);
				}
			}
		}
		return config::get($key);
	}
}
