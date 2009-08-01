{if $summary}{$summary}{/if}
{if $campaigns}{$campaigns}{/if}
{if $contracts}{$contracts}{/if}
{if $rss_feed}<div class=kb-kills-header><a href="{$kb_host}/?a=rss"><img src="{$kb_host}/mods/rss_feed/rss_icon.png" alt="RSS-Feed" border="0"></a>&nbsp;{$kill_count} most recent kills</div>
{else}<div class=kb-kills-header>{$kill_count} most recent kills</div>
{/if}
{if $kills}{$kills}{/if}
