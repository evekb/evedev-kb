<?php
/**
 * @package EDK
 */

if (!class_exists('options'))
{
	exit('This killboard is not supported (options package missing)!');
}
options::cat('Modules', 'AJCron', 'Settings');
options::fadd('Reset next scheduled run to now', 'ajcron_resetNextRun', 'checkbox');
options::fadd('Reset running jobs', 'ajcron_resetRunning', 'checkbox');
options::fadd('Blocking Cronjobs', 'ajcron_blocking', 'checkbox');
options::fadd('Next scheduled run', 'none', 'custom', array('ajcron', 'getNextRunDisplay'), array('ajcron', 'resetNextRunCheckbox'));
options::fadd('Job Format', 'none', 'custom', array('ajcron', 'helpFormat'));
options::fadd('Jobs', 'ajcron_jobs', 'textarea:cols:70:rows:10');
options::fadd('Runtable', 'anone', 'custom', array('ajcron', 'getRuntable'));