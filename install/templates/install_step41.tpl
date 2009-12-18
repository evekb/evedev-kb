Upgraded your config file and chmodded ../kbconfig.php to 440. If there was a warning for chmod please change the permission manually.<br/>
The next query checks for abandoned items, save this list for your reference.<br/>
{$sql_error}
{$notice}
<p>Warning!</p><br/>
Once you progress to the next step, the following queries will be run:<br/>
<pre>
UPDATE
kb3_items_destroyed
LEFT JOIN kb3_items ON itd_itm_id = itm_id
LEFT JOIN kb3_invtypes ON itm_name = typeName
SET itd_itm_id=typeID<br/>

UPDATE
kb3_inv_detail
LEFT JOIN kb3_items ON ind_wep_id = itm_id
LEFT JOIN kb3_invtypes ON itm_name = typeName
SET ind_wep_id=typeID<br/>

INSERT INTO kb3_item_price
SELECT typeID, itm_value AS price
FROM kb3_items
LEFT JOIN kb3_invtypes ON itm_name = typeName
where typeID IS NOT NULL AND itm_value != 0 AND itm_value != basePrice<br/>
</pre>
Make sure you backed up those tables!<br/>

{if !$stoppage}
    <p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}