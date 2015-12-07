<?php
/**
 * @package EDK
 */

/**
 * Create a box to display a TopList of Locations.
 * @package EDK
 */
class AwardBoxLocation extends AwardBox
{
    
        protected $limit_;
	/**
	 * Create an AwardBox from the given TopList and descriptions.
	 */
	function AwardBoxLocation($list, $title, $comment, $entity, $award, $limit = 10)
	{
            parent::AwardBox($list, $title, $comment, $entity, $award, $limit);
	}

        
        protected function getEntityImageUrl($row, $size)
        {
            $Location = new Location($row['itemID']);
            return $Location->getIcon($size, false);
        }
        
        protected function getEntityName($row)
        {
            return $row['itemName'];
        }
        
        protected function getEntitytUrl($row)
        {
            return null;
        }
                

}