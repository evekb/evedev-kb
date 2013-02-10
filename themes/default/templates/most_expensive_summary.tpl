{if $header}
{literal} 

<script type="text/javascript" language="javascript">
//<![CDATA[
$(document).ready(function() {
    $('div.kb-mostexpensive-list a.kb-mostexpensive-kill').each(function() {
        $(this).mouseenter(function() {
            $('div.kb-mostexpensive-killinfodetail', this).stop(true, false).animate({
                height: '80px'
                }, 250, function() {
                // Animation complete.
            });
        }).mouseleave(function() {
            $('div.kb-mostexpensive-killinfodetail', this).stop(true, false).animate({
                height: '0px'
                }, 250, function() {
                // Animation complete.
            });
        });
    });
});
//]]>
</script> 
{/literal}
{/if}

<div class="edk-section-main ui-widget ui-helper-reset">
{if $header}
<h3>Most expensive {$displaytype} for {$displaylist}.</h3>
{/if}
{if $killlist}
	<div class="kb-mostexpensive-listcontainer">
		<div class="kb-mostexpensive-list">
		
		{foreach from=$killlist item=k name=kl}
	  
			<a class="kb-mostexpensive-kill" href="?a=kill_detail&amp;kll_id={$k.id}" style="left:{130 * $smarty.foreach.kl.index}px;background:url('{$k.victimimageurl}') repeat scroll 0 0 transparent;">

				<div class="kb-mostexpensive-killinfo ui-state-default" style="background-repeat: repeat-x; background-position: center top; background-image: none;">
					
					<div class="kb-mostexpensive-killinfoicon" style="background:url('{$k.victimallimage}') no-repeat scroll 0 0 transparent;">
					</div>
					
					<div class="kb-mostexpensive-killinfoheader">
					{$k.victim}				
					<br />
					{$k.isklost}
					</div>
					
					<div class="kb-mostexpensive-killinfodetail">
					<p><strong>{$k.victimship}</strong></p>
					<p><strong>{$k.victimallname}</strong></p>
					<p><strong>{$k.victimcorp}</strong></p>
					<p>
					<strong>{$k.system|truncate:10}</strong>
					{if $k.systemsecurity < 0.05}
						(<span class="kb-mostexpensive-system-null">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>)
					{elseif $k.systemsecurity < 0.45}
						(<span class="kb-mostexpensive-system-low">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>)
					{else}
						(<span class="kb-mostexpensive-system-high">{$k.systemsecurity|max:0|string_format:"%01.1f"}</span>)
					{/if}
					</p>
					
					</div>
			
				</div>
			</a>
		{/foreach}
		</div>
	</div>

{else}
<p class="ui-state-highlight">No Data.</p>
{/if}
</div>