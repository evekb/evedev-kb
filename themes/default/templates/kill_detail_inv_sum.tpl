            <div class="block-header">Involved parties: {$involvedPartyCount}</div>

            {if $showext && $involvedPartyCount > 4}
            <table class="kb_table_involved" width="360" border="0" cellspacing="1">
                <tr class="kb-table-header">
                    {if $alliesCount > 1 || !$kill}<th>Alliances</th> {/if}<th>Corporations</th> <th>Ships</th>
                </tr>

                {assign var="first" value="true"}

                {foreach from=$invAllies key="key" item="l"}
                    <tr class="kb-table-row-even">
                        {if $alliesCount > 1 || !$kill}
                        <td class="kb-table-cell">
                            ({$l.quantity}) {$key|truncate:30:"...":true} <br/>
                        </td>
                        {/if}
                        <td class="kb-table-cell">
                            {if $alliesCount > 1 || !$kill}
                                {foreach from=$l.corps key="key1" item="l1"}
                                    ({$l1}) {$key1|truncate:21:"...":true} <br/>
                                {/foreach}
                            {else}
                                {foreach from=$l.corps key="key1" item="l1"}
                                    ({$l1}) {$key1|truncate:35:"...":true} <br/>
                                {/foreach}
                            {/if}
                        </td>
                        {if $first == "true"}
                            <td rowspan="{$alliesCount}" class="kb-table-cell" style="white-space: nowrap">
                            {if $alliesCount > 1 || !$kill}
                                {foreach from=$invShips key="key" item="l"}
                                    ({$l}) {$key|truncate:16:"...":true} <br/>
                                {/foreach}
                            {else}
                                {foreach from=$invShips key="key" item="l"}
                                    ({$l}) {$key|truncate:22:"...":true} <br/>
                                {/foreach}
                            {/if}
                           </td>

                            {assign var="first" value="false"}
                        {/if}
                    </tr>
                {/foreach}

            </table>
            <br/>
            {/if}
