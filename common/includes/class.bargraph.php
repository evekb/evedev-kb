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
class BarGraph
{
	private $value;
	private $width;
	private $text;
	private $max;
	private $class = "bar";

	function BarGraph($value = 0, $max = 100, $width = 75, $text = "")
	{
		$this->value = $value;
		$this->width = $width;
		$this->text = $text;
		$this->max = $max;

		$this->class = "bar";
	}

	function generate()
	{
		if ($this->text == "") $this->text = "&nbsp;";

		if ($this->value) $width = $this->width / ($this->max / $this->value);
		else $width = 0;

		global $smarty;
		$smarty->assign('class', $this->class);
		$smarty->assign('width', $width);
		$smarty->assign('maxwidth', $this->width);
		$smarty->assign('text', $this->text);

		return $smarty->fetch(get_tpl("bargraph"));
	}

	function setLow($low, $class)
	{
		if ($this->value <= $low) $this->class = "bar-".$class;
	}
}