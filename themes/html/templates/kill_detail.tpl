{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div class=block-header>Victim/Quick Info</div>
<table cellpadding=0 cellspacing=1 border=0>
    <tr>
        <td width=360 align=left valign=top><table class=kb-table width=360 cellpadding=0 cellspacing=1 border=0>
                <tr class= {cycle name=ccl}>
                    <td rowspan=4 width="64"><a href="{$VictimCorpURL}"><img src="{$VictimPortrait}" border="0" width="64" height="64" alt="{$VictimName}"></a></td>
					<td width="64" height="64" rowspan=4><a href="?a=invtype&amp;id={$ShipID}"><img src="{$VictimShipImg}" border="0" width="64" height="64" alt="{$ShipName}"></a></td>
                    <td class=kb-table-cell width=64><b>Victim:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimURL}">{$VictimName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell width=64><b>Corp:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimCorpURL}">{$VictimCorpName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                {if $config->get('faction')}
					<td class=kb-table-cell width=64><b>Faction:</b></td>
					<td class=kb-table-cell><b>{$VictimFaction}</b></td>
				{else}
					<td class=kb-table-cell width=64><b>Alliance:</b></td>
					<td class=kb-table-cell><b><a href="{$VictimAllianceURL}">{$VictimAllianceName}</a></b></td>
                {/if}
				</tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Ship:</b></td>
                    <td class=kb-table-cell><b><a href="?a=invtype&amp;id={$ShipID}">{$ShipName}</a></b> ({$ClassName})</td>
                </tr>
            </table>
			
			<!--MapMod -->
			{if  $loc_active}
			{if $config->get('map_mod_killdet_active') }
			<br />
			<div class="block-header">Location</div>
            		<table class="kb-table" border="0" cellspacing="1" width="360">
			<tr><td align="center">
			<img src="map.php?mode=sys&sys_id={$SystemID}&size=300" border="0" alt="map">
			<br />
			</td></tr></table>
			<br />
			{/if}
			{/if}
			<!--End MapMod -->
			
            <div class=block-header>Involved parties</div>
            <table class=kb-table width=360 border=0 cellspacing="1">
{foreach from=$involved key=key item=i}
                <tr class={cycle name=ccl}>
                    <td rowspan=5 width="64"><a href="{$i.PilotURL}"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.portrait}" border="0" alt="inv portrait"></a></td>
                    <td rowspan=5 width="64"><a href="?a=invtype&amp;id={$i.ShipID}"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.shipImage}" border="0" alt="{$i.ShipName}"></a></td>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}"><b>{$i.PilotName} {if $i.FB == "true"}(Final Blow){/if}</b></a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><b><a href="?a=invtype&amp;id={$i.ShipID}">{$i.ShipName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">{if $i.weaponID}<a href="?a=invtype&amp;id={$i.weaponID}">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan=2 class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">Damage done:</td><td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">{$i.damageDone|number_format} {if $VictimDamageTaken > 0}({$i.damageDone/$VictimDamageTaken*100|number_format}%){/if}</td>
                </tr>
{/foreach}
            </table>
{if $config->get('comments')}{$comments}{/if}
        </td>
        <td width=50>&nbsp;</td>
        <td align=left valign=top width=398><table class=kb-table width=398 cellspacing="1">
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Location:</b></td>
                    <td class=kb-table-cell><b><a href="{$SystemURL}">{$System}</a></b> ({$SystemSecurity})</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Date:</b></td>
                    <td class=kb-table-cell>{$TimeStamp}</td>
                </tr>
				<tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Total ISK Loss:</b></td>
                    <td class=kb-table-cell>{$TotalLoss}</td>
                </tr>
				<tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Total Damage Taken:</b></td>
                    <td class=kb-table-cell>{$VictimDamageTaken|number_format}</td>
                </tr>
            </table>

          <br />

{if $config->get('fp_show')}
          <div id="fitting" style="position:relative; height:398px; width:398px; background-image:url({$img_url}/{$themedir}/{$panel_style}.png)" title="fitting">
		<div id="high0" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$ssc->attrib.hiSlots.value}h.gif" border="0" alt=""></div>
		<div id="highc" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$hic}ch.gif" border="0" alt=""></div>
		<div id="mid0" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$ssc->attrib.medSlots.value}m.gif" border="0" alt=""></div>
		<div id="midx" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$medc}cm.gif" border="0" alt=""></div>
		<div id="low0" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$ssc->attrib.lowSlots.value}l.gif" border="0" alt=""></div>
		<div id="lowx" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$lowc}cl.gif" border="0" alt=""></div>
		<div id="rig0" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$ssc->attrib.rigSlots.value}r.gif" border="0" alt=""></div>

		<div id="mask" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img border="0" style="position:absolute; height:398px; width:398px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
     			src='{$img_url}/{$themedir}/{$panel_colour}.png', sizingMethod='image');" src="{$img_url}/{$themedir}/{$panel_colour}.png" alt=""></div>
		
		<div id="highx" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$hic}ph.gif" border="0" alt=""></div>
		<div id="high1" style="position:absolute; left:40px; top:278px; width:48px; height:48px; z-index:1;">{$fitting_high.0.Icon}</div>
		<div id="high1a" style="position:absolute; left:63px; top:304px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.0.show}</div>
		<div id="high1l" style="position:absolute; left:67px; top:308px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.0.type}</div>
	       	<div id="high2" style="position:absolute; left:9px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_high.1.Icon}</div>
        	<div id="high2a" style="position:absolute; left:18px; top:209px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.1.show}</div>
		<div id="high2l" style="position:absolute; left:22px; top:213px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.1.type}</div>
          	<div id="high3" style="position:absolute; left:40px; top:83px; width:48px; height:48px; z-index:1;">{$fitting_high.2.Icon}</div>
          	<div id="high3a" style="position:absolute; left:36px; top:109px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.2.show}</div>
		<div id="high3l" style="position:absolute; left:40px; top:113px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.2.type}</div>
          	<div id="high4" style="position:absolute; left:124px; top:22px; width:48px; height:48px; z-index:1;">{$fitting_high.3.Icon}</div>
          	<div id="high4a" style="position:absolute; left:113px; top:38px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.3.show}</div>
		<div id="high4l" style="position:absolute; left:117px; top:42px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.3.type}</div>
        	<div id="high5" style="position:absolute; left:227px; top:22px; width:48px; height:48px; z-index:1;">{$fitting_high.4.Icon}</div>
          	<div id="high5a" style="position:absolute; left:255px; top:38px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.4.show}</div>
		<div id="high5l" style="position:absolute; left:259px; top:42px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.4.type}</div>
          	<div id="high6" style="position:absolute; left:310px; top:83px; width:48px; height:48px; z-index:1;">{$fitting_high.5.Icon}</div>
          	<div id="high6a" style="position:absolute; left:330px; top:110px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.5.show}</div>
		<div id="high6l" style="position:absolute; left:334px; top:114px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.5.type}</div>
          	<div id="high7" style="position:absolute; left:342px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_high.6.Icon}</div>
          	<div id="high7a" style="position:absolute; left:348px; top:210px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.6.show}</div>
		<div id="high7l" style="position:absolute; left:352px; top:214px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.6.type}</div>
          	<div id="high8" style="position:absolute; left:310px; top:278px; width:48px; height:48px; z-index:1;">{$fitting_high.7.Icon}</div>
          	<div id="high8a" style="position:absolute; left:305px; top:302px; width:32px; height:32px; z-index:2;">{$fitting_ammo_high.7.show}</div>
		<div id="high8l" style="position:absolute; left:309px; top:306px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.7.type}</div>
		

		<div id="midx" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$medc}pm.gif" border="0" alt=""></div>
          	<div id="mid1" style="position:absolute; left:76px; top:253px; width:48px; height:48px; z-index:1;">{$fitting_med.0.Icon}</div>
		<div id="mid1a" style="position:absolute; left:100px; top:277px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.0.show}</div>
		<div id="mid1l" style="position:absolute; left:104px; top:281px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.0.type}</div>
          	<div id="mid2" style="position:absolute; left:52px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_med.1.Icon}</div>
		<div id="mid2a" style="position:absolute; left:65px; top:210px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.1.show}</div>
		<div id="mid2l" style="position:absolute; left:69px; top:214px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.1.type}</div>
          	<div id="mid3" style="position:absolute; left:77px; top:108px; width:48px; height:48px; z-index:1;">{$fitting_med.2.Icon}</div>
		<div id="mid3a" style="position:absolute; left:74px; top:136px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.2.show}</div>
		<div id="mid4l" style="position:absolute; left:78px; top:140px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.2.type}</div>
          	<div id="mid4" style="position:absolute; left:138px; top:66px; width:48px; height:48px; z-index:1;">{$fitting_med.3.Icon}</div>
		<div id="mid4a" style="position:absolute; left:124px; top:81px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.3.show}</div>
		<div id="mid4l" style="position:absolute; left:128px; top:85px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.3.type}</div>
          	<div id="mid5" style="position:absolute; left:213px; top:66px; width:48px; height:48px; z-index:1;">{$fitting_med.4.Icon}</div>
		<div id="mid5a" style="position:absolute; left:241px; top:81px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.4.show}</div>
		<div id="mid5l" style="position:absolute; left:245px; top:85px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.4.type}</div>
          	<div id="mid6" style="position:absolute; left:274px; top:108px; width:48px; height:48px; z-index:1;">{$fitting_med.5.Icon}</div>
		<div id="mid6a" style="position:absolute; left:292px; top:137px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.5.show}</div>
		<div id="mid6l" style="position:absolute; left:296px; top:141px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.5.type}</div>
          	<div id="mid7" style="position:absolute; left:298px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_med.6.Icon}</div>
		<div id="mid7a" style="position:absolute; left:302px; top:210px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.6.show}</div>
		<div id="mid7l" style="position:absolute; left:306px; top:214px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.6.type}</div>
          	<div id="mid8" style="position:absolute; left:275px; top:253px; width:48px; height:48px; z-index:1;">{$fitting_med.7.Icon}</div>
		<div id="mid8a" style="position:absolute; left:267px; top:276px; width:32px; height:32px; z-index:2;">{$fitting_ammo_mid.7.show}</div>
		<div id="mid8l" style="position:absolute; left:271px; top:280px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.7.type}</div>


		<div id="lowx" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img src="{$img_url}/{$themedir}/{$lowc}pl.gif" border="0" alt=""></div>
          	<div id="low1" style="position:absolute; left:114px; top:226px; width:48px; height:48px; z-index:1;">{$fitting_low.0.Icon}</div>
          	<div id="low2" style="position:absolute; left:98px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_low.1.Icon}</div>
          	<div id="low3" style="position:absolute; left:114px; top:135px; width:48px; height:48px; z-index:1;">{$fitting_low.2.Icon}</div>
          	<div id="low4" style="position:absolute; left:151px; top:110px; width:48px; height:48px; z-index:1;">{$fitting_low.3.Icon}</div>
          	<div id="low5" style="position:absolute; left:198px; top:110px; width:48px; height:48px; z-index:1;">{$fitting_low.4.Icon}</div>
          	<div id="low6" style="position:absolute; left:236px; top:135px; width:48px; height:48px; z-index:1;">{$fitting_low.5.Icon}</div>
          	<div id="low7" style="position:absolute; left:250px; top:180px; width:48px; height:48px; z-index:1;">{$fitting_low.6.Icon}</div>
          	<div id="low8" style="position:absolute; left:236px; top:226px; width:48px; height:48px; z-index:1;">{$fitting_low.7.Icon}</div>


          	<div id="rig1" style="position:absolute; left:66px; top:355px; width:32px; height:32px; z-index:1;">{$fitting_rig.0.Icon}</div>
          	<div id="rig2" style="position:absolute; left:100px; top:355px; width:32px; height:32px; z-index:1;">{$fitting_rig.1.Icon}</div>
          	<div id="rig3" style="position:absolute; left:134px; top:355px; width:32px; height:32px; z-index:1;">{$fitting_rig.2.Icon}</div>
          </div>
{/if}
           
          	<div class="block-header">Ship details</div>
            <table class="kb-table" width="398" border="0" cellspacing="1">
{foreach from=$slots item=slot key=slotindex}
{* set to true to show empty slots *}
{if $destroyed.$slotindex or $dropped.$slotindex}
                <tr class="kb-table-row-slot">
                    <td class="item-icon" width="32"><img width="32" height="32" src="{$img_url}/{$slot.img}" alt="{$slot.text}" border="0"></td>
                    <td colspan="2" class="kb-table-cell"><b>{$slot.text}</b> </td>
    {if $config->get('item_values')}
                    <td align="center" class="kb-table-cell"><b>Value</b></td>
    {/if}
                </tr>
    {foreach from=$destroyed.$slotindex item=i}
                <tr class="kb-table-row-destroyed">
                    <td class="item-icon" width="32" height="34" valign="top" onClick="window.location.href='?a=invtype&amp;id={$i.itemID}'">{$i.Icon}</td>
                    <td class="kb-table-cell">{$i.Name}</td>
                    <td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
                    <td align="center">{$i.Value}</td>
        {/if}
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current single Item Value:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="8">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
        {if $admin and $i.slotID < 4 and $fixSlot}
                    <tr class="kb-table-row-even">
                      <form method="post" action="">
                        <td height="34" colspan="3" valign="top">
                            <div align="right">
                                Fix slot: 
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="KID" value="{$KillId}" type="hidden">
								<input name="TYPE" value="destroyed" type="hidden">
								<input name="OLDSLOT" value="{$i.slotID}" type="hidden">				
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6">
                            </div>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button"></td>
                      </form>
                    </tr>
        {/if}
    {/foreach}
    {foreach from=$dropped.$slotindex item=i}
                <tr class="kb-table-row-dropped">
                    <td class="item-dropped-icon" onClick="window.location.href='?a=invtype&amp;id={$i.itemID}'" width="32" height="34" valign="top">{$i.Icon}</td>
                    <td class="kb-table-cell">{$i.Name}</td>
                    <td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
                    <td align="center">{$i.Value}</td>
        {/if}
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current single Item Value:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="6">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
	{if $admin and $i.slotID < 4 and $fixSlot}
                    <tr class="kb-table-row-even">
                      <form method="post" action="">
                        <td height="34" colspan="3" valign="top">
                            <div align="right">
                                Fix slot: 
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="KID" value="{$KillId}" type="hidden">
								<input name="TYPE" value="dropped" type="hidden">
								<input name="OLDSLOT" value="{$i.slotID}" type="hidden">				
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6">
                            </div>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button"></td>
                      </form>
                    </tr>
        {/if}
    {/foreach}
{/if}
{/foreach}
{if $item_values}
                <tr class={cycle name=ccl}>
                    <td align="right" colspan="3"><b>Damage taken:</b></td>
                    <td align="right">{$VictimDamageTaken|number_format}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Total Module Loss:</strong></div></td>
                    <td align="right">{$ItemValue}</td>
                </tr>
                <tr class="kb-table-row-dropped">
                    <td colspan="3"><div align="right"><strong>Total Module Drop:</strong></div></td>
                    <td align="right">{$DropValue}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Ship Loss:</strong></div></td>
                    <td align="right">{$ShipValue}</td>
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current Ship Value:
                                <input name="SID" value="{$Ship->getID()}" type="hidden">
                                <input name="{$Ship->getID()}" type="text" class="comment-button" value="{$Ship->getPrice()}" size="10">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
                <tr class="kb-table-row-dropped">
                    <td colspan="3"><div align="right"><strong>Total Loss:</strong></div></td>
                    <td align="right">{$TotalLoss}</td>
                </tr>
{/if}
            </table>
        </td>
    </tr>
</table>
