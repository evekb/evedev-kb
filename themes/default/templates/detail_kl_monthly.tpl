<!-- detail_kl_monthly -->
<div class="killlist">
	<div class="block-header2">{$title}</div>
	<div style="float: left; width: 306px; margin-left:10px">
		<div class="block-header">{$month} {$year}</div>
		{$monthly_stats}
		<div style="float: left; margin-left: 5px">
					<a href='{$url_previous}'>previous</a>
		</div>
		<div style="float: right; margin-right: 5px">
			<a href='{$url_next}'>next</a>
		</div>
	</div>
	<div style="float: right; width: 306px; margin-right:10px">
		<div class="block-header">All time</div>
		{$total_stats}
	</div>
</div>
<!-- /detail_kl_monthly -->