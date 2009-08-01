<?php
$ribbon_parent = array(
"Frigate",		// 0 - T1 frigs + unselected frig classes
"Destroyer",		// 1 - T1 dessys + dictor if unselected
"Cruiser",		// 2 - T1 cruisers + unselected cruiser classes
"Battlecruiser",	// 3 - T1 BCs + command if unselected
"Battleship",		// 4 - T1 BSs + unselected BS classes
"Capital",		// 5 - All capitals or null if all selected
"Industrial",		// 6 - T1 indys + unselected indy classes
"Kamikaze"		// 7 - Pod & shuttle + N00bship if unselected
);

$ribbon_child = array(
array('parent' => 0, 'class' => 'Assault Ship'),		// 0
array('parent' => 0, 'class' => 'Interceptor'),			// 1
array('parent' => 0, 'class' => 'Covert Ops'),			// 2
array('parent' => 0, 'class' => 'Electronic Attack Ship'),	// 3
array('parent' => 1, 'class' => 'Interdictor'),			// 4
array('parent' => 2, 'class' => 'Heavy Assault Cruiser'),	// 5
array('parent' => 2, 'class' => 'Heavy Interdictor'),		// 6
array('parent' => 2, 'class' => 'Logistic Cruiser'),		// 7
array('parent' => 2, 'class' => 'Recon Ship'),			// 8
array('parent' => 3, 'class' => 'Command Ship'),		// 9
array('parent' => 4, 'class' => 'Black Ops'),			// 10
array('parent' => 4, 'class' => 'Marauder'),			// 11
array('parent' => 5, 'class' => 'Dreadnought'),			// 12
array('parent' => 5, 'class' => 'Carrier'),			// 13
array('parent' => 5, 'class' => 'Mothership'),			// 14
array('parent' => 5, 'class' => 'Titan'),			// 15
array('parent' => 6, 'class' => 'Mining Barge'),		// 16
array('parent' => 6, 'class' => 'Exhumer'),			// 17
array('parent' => 6, 'class' => 'Transport Ship'),		// 18
array('parent' => 6, 'class' => 'Cap. Industrial'),		// 19
array('parent' => 7, 'class' => 'N00bship')			// 20
);
?>
