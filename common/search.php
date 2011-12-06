<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// An ajax search function for this page is in common/includes/xajax.functions.php
require_once('common/includes/xajax.functions.php');

/*
 * @package EDK
 */
class pSearch extends pageAssembly
{
	/** @var Page */
	public $page;
	/** @var string The phrase to search for. */
	public $searchphrase;
	/** @var string The type of result to search for. Valid types are 
	 * alliance, corp, pilot, system, or item
	 */
	public $searchtype;
    /**
     * Construct the Alliance Details object.
     *
     *  Set up the basic variables of the class and add the functions to the
     *  build queue.
     */
    function __construct()
    {
        parent::__construct();

        $this->queue("start");
		$this->queue("newSearch");
        $this->queue("checkSearch");
    }
    function start()
    {
        $this->page = new Page('Search');
        $this->searchphrase = is_null($_POST['searchphrase']) ? slashfix($_GET['searchphrase']) : slashfix($_POST['searchphrase']);
        $this->searchphrase = preg_replace('/\*/', '%', $this->searchphrase);
        $this->searchphrase = trim($this->searchphrase);
        $this->searchtype = is_null($_POST['searchtype']) ? $_GET['searchtype'] : $_POST['searchtype'];
    }

    function checkSearch()
    {
        global $smarty;
        if ($this->searchphrase != "" && strlen($this->searchphrase) >= 3)
        {
            switch ($this->searchtype)
            {
                case "pilot":
                    $sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                          from kb3_pilots plt, kb3_corps crp
                         where plt.plt_name  like '%".$this->searchphrase."%'
                           and plt.plt_crp_id = crp.crp_id
                         order by plt.plt_name";
                    $smarty->assign('result_header', 'Pilot');
                    $smarty->assign('result_header_group', 'Corporation');
                    break;
                case "corp":
                    $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                          from kb3_corps crp, kb3_alliances ali
                         where lower( crp.crp_name ) like lower( '%".$this->searchphrase."%' )
                           and crp.crp_all_id = ali.all_id
                         order by crp.crp_name";
                    $smarty->assign('result_header', 'Corporation');
                    $smarty->assign('result_header_group', 'Alliance');
                    break;
                case "alliance":
                    $sql = "select ali.all_id, ali.all_name
                          from kb3_alliances ali
                         where lower( ali.all_name ) like lower( '%".$this->searchphrase."%' )
                         order by ali.all_name";
                    $smarty->assign('result_header', 'Alliance');
                    $smarty->assign('result_header_group', '');
                    break;
                case "system":
                    $sql = "select sys.sys_id, sys.sys_name
                          from kb3_systems sys
                         where lower( sys.sys_name ) like lower( '%".$this->searchphrase."%' )
                         order by sys.sys_name";
                    $smarty->assign('result_header', 'System');
                    $smarty->assign('result_header_group', '');
                    break;
                case "item":
                    $sql = "select typeID, typeName from kb3_invtypes where typeName like ('%".$this->searchphrase."%')";
                    break;
            }
			$qry = DBFactory::getDBQuery();;
            if (!$qry->execute($sql))
            {
                die ($qry->getErrorMsg());
            }
            $smarty->assign('searched', 1);
            if ($qry->recordCount() == 0)
            {
                $smarty->assign('results', 0);
            }
            else
            {
                $results = array();
                while ($row = $qry->getRow())
                {
                    $result = array();
                    switch ($this->searchtype)
                    {
                        case "pilot":
                            $result['link'] = edkURI::page('pilot_detail', $row['plt_id'], 'plt_id');
                            $result['name'] = $row['plt_name'];
                            $result['type'] = $row['crp_name'];
                            $results[] = $result;
                            break;
                        case "corp":
                            $result['link'] = edkURI::page('corp_detail', $row['crp_id'], 'crp_id');
                            $result['name'] = $row['crp_name'];
                            $result['type'] = $row['all_name'];
                            $results[] = $result;
                            break;
                        case "alliance":
                            $result['link'] = edkURI::page('alliance_detail', $row['all_id'], 'all_id');
                            $result['name'] = $row['all_name'];
                            $result['type'] = '';
                            $results[] = $result;
                            break;
                        case "system":
                            $result['link'] = edkURI::page('system_detail', $row['sys_id'], 'sys_id');
                            $result['name'] = $row['sys_name'];
                            $result['type'] = '';
                            $results[] = $result;
                            break;
                        case 'item':
                            $result['link'] = edkURI::page('invtype', $row['typeID']);
                            $result['name'] = $row['typeName'];
                            $result['type'] = '';
                            $results[] = $result;
                            break;
                    }
                    if ($qry->recordCount() == 1)
                    {
                        // if there is only one entry we redirect the user
                        header("Location: ".html_entity_decode($result['link']));
                        die;
                    }
                }
				$smarty->assignByRef('results', $results);
            }
        }
		$smarty->assign('nonajax', true);
        return $smarty->fetch(get_tpl('search_result'));
    }
    function newSearch()
    {
        global $smarty;
        return $smarty->fetch(get_tpl('search_new'));
    }
}

$searchDetail = new pSearch();
event::call("search_assembling", $searchDetail);
$html = $searchDetail->assemble();
$searchDetail->page->setContent($html);

$searchDetail->page->generate();
