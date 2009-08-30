{if $home_summary}{$home_summary}{/if}
{if $home_campaigns}{$home_campaigns}{/if}
{if $home_contracts}{$home_contracts}{/if}
{if $rss_feed}<div class="kb-kills-header"><a href="{$kb_host}/?a=rss"><img src="{$kb_host}/mods/rss_feed/rss_icon.png" alt="RSS-Feed" border="0" /></a>&nbsp;{$kill_count} most recent kills</div>
{else}<div class="kb-kills-header">{$kill_count} most recent kills</div>
{/if}
{if $home_kills}{$home_kills}{/if}
