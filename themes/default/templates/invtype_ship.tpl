{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table cellpadding=0 cellspacing=1 border=0>
    <tr>
        <td width=360 align=left valign=top>
            <div class="block-header">{$item->item.typeName}</div>
            <table class="kb-table" width=360 cellpadding=0 cellspacing=1 border=0>
                <tr class="kb-table-row-even">
                    <td><img style="float: left; margin-right: 10px;" src="{$img_url}/ships/64_64/{$item->item.typeID}.png">{$item->item.description|nl2br}</td>
                </tr>
            </table>
            <div class="block-header">Armor</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('armorHP','armorEmDamageResonance','armorExplosiveDamageResonance',
                                                'armorKineticDamageResonance','armorThermalDamageResonance'));{/php}
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{if strstr($key, 'Resonance')}{$item->attrib.$key.value*-100+100}
                                                            {else}{$item->attrib.$key.value}{/if} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
            </table>
            <div class="block-header">Shield</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('shieldCapacity','shieldRechargeRate','shieldEmDamageResonance','shieldExplosiveDamageResonance',
                                                'shieldKineticDamageResonance','shieldThermalDamageResonance'));{/php}
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{if strstr($key, 'Resonance')}{$item->attrib.$key.value*-100+100}
                                                            {else}{$item->attrib.$key.value}{/if} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
            </table>
            <div class="block-header">Astronautic</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('maxVelocity','agility','droneCapacity','capacitorCapacity',
                                                'rechargeRate'));{/php}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon03_13.png" border="0"></td>
                    <td class="kb-table-cell"><b>Cargo capacity</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.capacity} m3</td>
                </tr>
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon02_10.png" border="0"></td>
                    <td class="kb-table-cell"><b>Mass</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.mass|number_format} kg</td>
                </tr>
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon02_09.png" border="0"></td>
                    <td class="kb-table-cell"><b>Volume</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.volume} m3</td>
                </tr>
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{$item->attrib.$key.value} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon07_12.png" border="0"></td>
                    <td class="kb-table-cell"><b>Baseprice</b></td>
                    <td class="kb-table-cell" align="right">{$item->item.basePrice|number_format} ISK</td>
                </tr>
            </table>
            </td>
        <td width=50>&nbsp;</td>
        <td align=left valign=top width=360>
            <div class="block-header">Fitting</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('hiSlots','medSlots','lowSlots','rigSlots','upgradeCapacity','droneBandwidth','launcherSlotsLeft',
                                                'turretSlotsLeft','powerOutput','cpuOutput'));{/php}
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{$item->attrib.$key.value} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
            </table>
            <div class="block-header">Combat</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('maxTargetRange','scanResolution','maxLockedTargets','scanRadarStrength',
                                                'scanLadarStrength','scanMagnetometricStrength','scanGravimetricStrength',
                                                'signatureRadius'));{/php}
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{$item->attrib.$key.value} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
            </table>
            <div class="block-header">Misc</div>
            <table class="kb-table" width="360" border="0" cellspacing="1">
            {php}$this->assign('attribs', array('techLevel','propulsionFusionStrength','propulsionIonStrength','propulsionMagpulseStrength',
                                                'propulsionPlasmaStrength'));{/php}
            {foreach from=$attribs key=i item=key}
                <tr class="{cycle name=ccl}">
                    <td class="item-icon" width="32"><img src="{$img_url}/items/32_32/icon{$item->attrib.$key.icon}.png" border="0"></td>
                    <td class="kb-table-cell"><b>{$item->attrib.$key.displayName}</b></td>
                    <td class="kb-table-cell" align="right">{$item->attrib.$key.value} {$item->attrib.$key.unit}</td>
                </tr>
            {/foreach}
            </table>
        </td>
    </tr>
</table>