<?php
require_once('common/includes/class.contract.php');
require_once('common/includes/class.http.php');
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();

if ($_REQUEST['opt'] == 'search')
{
    $page->setTitle('Administration - Shipvalues - Add a Shipvalue');

    if ($id = intval($_REQUEST['searchid']))
    {
        $query = 'select count(shp_id) as cnt from kb3_ships_values where shp_id='.$id;
        $qry = new DBQuery();
        $qry->execute($query);
        $data = $qry->getRow();
        if ($data['cnt'] >= 1)
        {
            // value already added
            unset($_REQUEST['opt']);
            $html .= 'Error: That id is already on the list.<br/>';
        }
        else
        {
            $search = true;
            $searchstr = ' where ksb.shp_value is null and shp.shp_class != 17 and shp.shp_id='.$id;
        }
    }
    elseif ($name = slashfix($_REQUEST['searchname']))
    {
        $search = true;
        $searchstr = " where ksb.shp_value is null and shp.shp_class != 17 and shp.shp_name like '%{$name}%'";
    }
    else
    {
        unset($_REQUEST['opt']);
        $html .= 'Error: No id or name specified.<br/>';
    }
    if ($search)
    {
        $query = 'select shp.shp_id as id, shp.shp_externalid as ext, shp.shp_name, shp.shp_class,
                         shp.shp_baseprice, scl.scl_class, shp.shp_techlevel, scl.scl_value, ksb.shp_value
                         from kb3_ships shp inner join kb3_ship_classes scl on (shp.shp_class = scl.scl_id)
                         left join kb3_ships_values ksb on (shp.shp_id = ksb.shp_id)';
        $order = ' order by shp.shp_name asc';
        $qry = new DBQuery();
        $qry->execute($query.$searchstr.$order);
        while ($data = $qry->getRow())
        {
            if (!$c)
            {
                $html .= '<form id="search" action="?a=admin_shp_val" method=post>';
                $html .= '<table class=kb-table width="99%" align=center cellspacing="1">';
                $html .= '<input type="hidden" name="opt" value="add"/>';
                $html .= '<tr class=kb-table-header>';
                $html .= '<td class=kb-table-header align="center">Ship</td>';
                $html .= '<td class=kb-table-header align="center">Ship id</td>';
                $html .= '<td class=kb-table-header>Ship Name</td>';
                $html .= '<td class=kb-table-header>Ship type</td>';
                $html .= '<td class=kb-table-header align="center">Techlevel</td>';
                $html .= '<td class=kb-table-header align="right">Baseprice</td>';
                $html .= '<td class=kb-table-header align="right">Classvalue</td>';
                $html .= '<td class=kb-table-header align="right">Shipvalue</td></tr>';
            }
            $c++;
            if (!$odd)
            {
                $odd = true;
                $class = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $class = 'kb-table-row-even';
            }
            $html .= "<tr class=".$class." style=\"height: 66px;\">";
            $html .= '<td width="64" align="center"><img src="'.IMG_URL.'/ships/64_64/'.$data['ext'].'.png"></td>';
            $html .= '<td align="center">'.$data['id'].'</td>';
            $html .= '<td>'.$data['shp_name'].'</td>';
            $html .= '<td>'.$data['scl_class'].'</td>';
            $html .= '<td width="64" align="center">'.$data['shp_techlevel'].'</td>';
            $html .= '<td align="right">'.number_format($data['shp_baseprice'], 0, ',', '.').'</td>';
            $html .= '<td align="right">'.number_format($data['scl_value'], 0, ',', '.').'</td>';
            $html .= '<td width="180" align="right"><input type="text" name="ship['.$data['id'].']" value="'.$data['scl_value'].'"></td></tr>';
        }
        if ($c)
        {
            $html .= '</table>';
            $html .= '<br/><input type="submit" name="submit" value="Save">&nbsp;Note: Only values different from classvalue and zero will be saved.';
            $html .= '</form>';
        }
        else
        {
            $html .= 'No Ships found to be added.<br/>';
            unset($_REQUEST['opt']);
        }
    }
}
if ($_REQUEST['opt'] == 'add')
{
    $qry = new DBQuery();
    if (!isset($_POST['ship']))
    {
        $_POST['ship'] = array();
    }
    foreach ($_POST['ship'] as $id => $value)
    {
        $id = intval($id);
        // kill everything thats not a number
        $value = preg_replace('/[^0-9]/', '', $value);
        if ($value == 0 || $id == 0)
        {
            continue;
        }
        $query = 'select shp.shp_id as id, scl.scl_value
                         from kb3_ships shp inner join kb3_ship_classes scl on (shp.shp_class = scl.scl_id)
                         where shp.shp_id='.$id;
        $qry->execute($query);
        $data = $qry->getRow();
        if ($data['scl_value'] == $value)
        {
            $qry->execute('delete from kb3_ships_values where shp_id='.$id.' limit 1');
            continue;
        }

        $query = 'select count(shp_id) as cnt from kb3_ships_values where shp_id='.$id;
        $qry->execute($query);
        $data = $qry->getRow();
        if ($data['cnt'] >= 1)
        {
            $qry->execute('update kb3_ships_values set shp_value=\''.$value.'\' where shp_id='.$id);
        }
        else
        {
            $qry->execute('insert into kb3_ships_values (shp_id, shp_value) values ('.$id.',\''.$value.'\')');
        }
    }
    $html .= 'Shipvalues added/changed<br/>';
    unset($_REQUEST['opt']);
}
if (!isset($_REQUEST['opt']))
{
    $page->setTitle("Administration - Shipvalues");

    $html .= '<div class=block-header2>Add a Shipvalue</div>';
    $html .= '<form id="search" action="?a=admin_shp_val" method=post>';
    $html .= '<input type="hidden" name="opt" value="search"/>';
    $html .= '<table class="kb-subtable"><tr>';
    $html .= '<td>ShipID</td><td>or Shipname</td>';
    $html .= '</tr><tr>';
    $html .= '<td><input id="searchid" name="searchid" type="text" size="4"/></td>';
    $html .= '<td><input id="searchname" name="searchname" type="text" size="30"/></td>';
    $html .= '<td><input type="submit" name="submit" value="Search"/></td>';
    $html .= '</tr></table>';
    $html .= '</form><br/>';

    $html .= "<div class=block-header2>View/Change Shipvalues</div>";
    $qry = new DBQuery();
    $query = 'select kbs.shp_id as id, shp.shp_externalid as ext, shp.shp_name, shp.shp_class, kbs.shp_value,
                     shp.shp_baseprice, scl.scl_class, shp.shp_techlevel, scl.scl_value
                     from kb3_ships_values kbs
                     inner join kb3_ships shp on (kbs.shp_id = shp.shp_id)
                     inner join kb3_ship_classes scl on (shp.shp_class = scl.scl_id) order by shp.shp_name asc';
    $qry->execute($query);
    while ($data = $qry->getRow())
    {
        if (!$c)
        {
            $html .= '<form id="search" action="?a=admin_shp_val" method=post>';
            $html .= '<table class="kb-table" width="99%" align=center cellspacing="1">';
            $html .= '<input type="hidden" name="opt" value="add"/>';
            $html .= '<script language="javascript">
                        function geninput(object,id,value,orgval)
                        {
                          if (document.getElementById(\'ship_\'+id)) return;
                          object.innerHTML = \'<input type="text" id="ship_\'+id+\'" name="ship[\'+id+\']" value="\'+value+\'" onblur="checkinput(this,\'+value+\',\\\'\'+orgval+\'\\\',\'+id+\');">\';
                          document.getElementById(\'ship_\'+id).focus();
                        }
                        function checkinput(object,value,oldvalue,id)
                        {
                          if (object.value == value)
                          {
                              document.getElementById(\'tbrid_\'+id).innerHTML = oldvalue;
                          }
                        }
                        </script>';
            $html .= '<tr class=kb-table-header>';
            $html .= '<td class=kb-table-header align="center">Ship</td>';
            $html .= '<td class=kb-table-header align="center">Ship id</td>';
            $html .= '<td class=kb-table-header>Ship Name</td>';
            $html .= '<td class=kb-table-header>Ship type</td>';
            $html .= '<td class=kb-table-header align="center">Techlevel</td>';
            $html .= '<td class=kb-table-header align="right">Baseprice</td>';
            $html .= '<td class=kb-table-header align="right">Classvalue</td>';
            $html .= '<td class=kb-table-header align="right">Shipvalue</td></tr>';
        }
        $c++;
        if (!$odd)
        {
            $odd = true;
            $class = 'kb-table-row-odd';
        }
        else
        {
            $odd = false;
            $class = 'kb-table-row-even';
        }
        $html .= "<tr class=".$class." style=\"height: 34px;\">";
        $html .= '<td width="32" align="center"><img src="'.IMG_URL.'/ships/32_32/'.$data['ext'].'.png"></td>';
        $html .= '<td align="center">'.$data['id'].'</td>';
        $html .= '<td>'.$data['shp_name'].'</td>';
        $html .= '<td>'.$data['scl_class'].'</td>';
        $html .= '<td width="64" align="center">'.$data['shp_techlevel'].'</td>';
        $html .= '<td align="right">'.number_format($data['shp_baseprice'], 0, ',', '.').'</td>';
        $html .= '<td align="right">'.number_format($data['scl_value'], 0, ',', '.').'</td>';
        $html .= '<td width="180" align="right" id="tbrid_'.$data['id'].'" onClick="geninput(this,'.$data['id'].','.$data['shp_value'].',\''.number_format($data['shp_value'], 0, ',', '.').'\');">'
                 .number_format($data['shp_value'], 0, ',', '.').'</td></tr>';
    }
    if ($c)
    {
        $html .= '</table>';
        $html .= '<br/><input type="submit" name="submit" value="Save">&nbsp;Note: Only values different from classvalue and zero will be saved. Hint: click into the Shipvalue field.';
        $html .= '</form>';
    }
    else
    {
        $html .= 'No Data.<br/>';
    }
}
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>