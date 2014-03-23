<?php
/**
 * @package EDK
 */
if (!class_exists('options'))
{
    header('Location: '.KB_HOST.'/?a=admin&field=Modules&sub=Mail%20Forward');
}
options::cat('Modules', 'Mail Forward', 'Forwarding');
options::fadd('Forwarding active', 'forward_active', 'checkbox');
options::fadd('Forward site', 'forward_site', 'edit:size:50');
options::fadd('Forward password', 'forward_pass', 'edit');