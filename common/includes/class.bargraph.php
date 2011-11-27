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
	private $text;
	private $max;
	private $class = "bar";

	/**
	 * Set up the BarGraph with set parameters. Graph displays a 100% bar
	 * and a proportion. e.g. a max of 20 and a value of 10 shows a background bar
	 * 100% long and a proportion bar 50% long.
	 *
	 * @param type $value Value for this graph.
	 * @param type $max Maximum value
	 * @param type $width Unused
	 * @param type $text Optional text to display after graph.
	 */
	function BarGraph($value = 0, $max = 100, $width = null, $text = "")
	{
		$this->value = $value;
		$this->text = $text;
		$this->max = $max;

		$this->class = "bar";
	}

	/**
	 * Generate the HTML for this BarGraph.
	 *
	 * @global type $smarty
	 * @return string HTML for this bar.
	 */
	function generate()
	{
		if ($this->text == "") {
			$this->text = "&nbsp;";
		}
		if ($this->value) {
			$width = (int) (100 * $this->value / $this->max);
		} else {
			$width = 0;
		}

		global $smarty;
		$smarty->assign('class', $this->class);
		$smarty->assign('width', $width);
		$smarty->assign('text', $this->text);

		return $smarty->fetch(get_tpl("bargraph"));
	}

	function setLow($low, $class)
	{
		if ($this->value <= $low) {
			$this->class = "bar-".$class;
		}
	}
}