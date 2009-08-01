<?php

class dogma
{
    function dogma($itemID)
    {
        $qry = new DBQuery();
        $query = 'select * from kb3_invtypes
                    left join kb3_item_types on itt_id = groupID
                    where typeID = '.$itemID;
        $qry->execute($query);

        if (!$row = $qry->getRow())
        {
            $this->_valid = false;
            return;
        }
        $this->_valid = true;
        $this->_id = $row['typeID'];
        $this->item = $row;

        $this->attrib = array();
        $query = 'select kb3_dgmtypeattributes.*,kb3_dgmattributetypes.*,kb3_eveunits.displayName as unit
          from kb3_dgmtypeattributes
          inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
          left join kb3_eveunits on kb3_dgmattributetypes.unitID = kb3_eveunits.unitID
          where typeID = '.$itemID;

        $qry->execute($query);
        while ($row = $qry->getRow())
        {
            if ($row['displayName'] == '')
            {
                $row['displayName'] = $row['attributeName'];
            }
            $this->attrib[$row['attributeName']] = $row;
        }

        $this->effects = array();
        $query = 'select * from kb3_dgmtypeeffects
          inner join kb3_dgmeffects on kb3_dgmtypeeffects.effectID = kb3_dgmeffects.effectID
          where typeID = '.$itemID;

        $qry->execute($query);
        while ($row = $qry->getRow())
        {
            if (!$row['effectName'])
            {
                var_export($row);
                continue;
            }
            if ($row['displayName'] == '')
            {
                $row['displayName'] = $row['effectName'];
            }
            $this->effects[$row['effectName']] = $row;
        }
    }

    function get($string)
    {
        if (isset($this->item[$string]))
        {
            return $this->item[$string];
        }

        return false;
    }

    function isValid()
    {
        return $this->_valid;
    }

    function resolveTypeID($id)
    {
        $id = intval($id);
        $qry = new DBQuery();
        $qry->execute('select typeName from kb3_invtypes where typeID='.$id);
        $row = $qry->getRow();
        return $row['typeName'];
    }

    function resolveGroupID($id)
    {
        $id = intval($id);
        $qry = new DBQuery();
        $qry->execute('select itt_name from kb3_item_types where itt_id='.$id);
        $row = $qry->getRow();
        return $row['itt_name'];
    }

    function resolveAttributeID($id)
    {
        $id = intval($id);
        $qry = new DBQuery();
        $qry->execute('select displayName from kb3_dgmattributetypes where attributeID='.$id);
        $row = $qry->getRow();
        return $row['displayName'];
    }}
?>