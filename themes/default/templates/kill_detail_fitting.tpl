<div class="kl-detail-fitting">
	<div class="fitting-panel" style="position:relative; height:398px; width:398px;" title="fitting">
		<div id="mask" class="fit-slot-bg">
			<img style="height:398px; width:398px;" src='{$img_url}/panel/{$panel_colour}.png' alt='' /></div>

		<div id="highx" class="fit-slot-bg">
			<img src="{$img_url}/panel/{$hic}h.png" alt="" /></div>
		{if $fitting_high.0.Icon}<div id="high1" class="fit-module {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:73px; top:60px;">{$fitting_high.0.Icon}</div>{/if}
		{if $fitting_high.1.Icon}<div id="high2" class="fit-module {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:102px; top:42px;">{$fitting_high.1.Icon}</div>{/if}
		{if $fitting_high.2.Icon}<div id="high3" class="fit-module {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:134px; top:27px;">{$fitting_high.2.Icon}</div>{/if}
		{if $fitting_high.3.Icon}<div id="high4" class="fit-module {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:169px; top:21px;">{$fitting_high.3.Icon}</div>{/if}
		{if $fitting_high.4.Icon}<div id="high5" class="fit-module {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:203px; top:22px;">{$fitting_high.4.Icon}</div>{/if}
		{if $fitting_high.5.Icon}<div id="high6" class="fit-module {if $fitting_high.5.destroyed}fit-destroyed{/if}" style="left:238px; top:30px;">{$fitting_high.5.Icon}</div>{/if}
		{if $fitting_high.6.Icon}<div id="high7" class="fit-module {if $fitting_high.6.destroyed}fit-destroyed{/if}" style="left:270px; top:45px;">{$fitting_high.6.Icon}</div>{/if}
		{if $fitting_high.7.Icon}<div id="high8" class="fit-module {if $fitting_high.7.destroyed}fit-destroyed{/if}" style="left:295px; top:64px;">{$fitting_high.7.Icon}</div>{/if}

		<div id="midx" class="fit-slot-bg">
			<img src="{$img_url}/panel/{$medc}m.png" alt="" /></div>
		{if $fitting_med.0.Icon}<div id="mid1" class="fit-module {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:26px; top:140px;">{$fitting_med.0.Icon}</div>{/if}
		{if $fitting_med.1.Icon}<div id="mid2" class="fit-module {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:24px; top:176px;">{$fitting_med.1.Icon}</div>{/if}
		{if $fitting_med.2.Icon}<div id="mid3" class="fit-module {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:23px; top:212px;">{$fitting_med.2.Icon}</div>{/if}
		{if $fitting_med.3.Icon}<div id="mid4" class="fit-module {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:30px; top:245px;">{$fitting_med.3.Icon}</div>{/if}
		{if $fitting_med.4.Icon}<div id="mid5" class="fit-module {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:46px; top:278px;">{$fitting_med.4.Icon}</div>{/if}
		{if $fitting_med.5.Icon}<div id="mid6" class="fit-module {if $fitting_high.5.destroyed}fit-destroyed{/if}" style="left:69px; top:304px;">{$fitting_med.5.Icon}</div>{/if}
		{if $fitting_med.6.Icon}<div id="mid7" class="fit-module {if $fitting_high.6.destroyed}fit-destroyed{/if}" style="left:100px; top:328px;">{$fitting_med.6.Icon}</div>{/if}
		{if $fitting_med.7.Icon}<div id="mid8" class="fit-module {if $fitting_high.7.destroyed}fit-destroyed{/if}" style="left:133px; top:342px;">{$fitting_med.7.Icon}</div>{/if}

		<div id="lowx" class="fit-slot-bg">
			<img src="{$img_url}/panel/{$lowc}l.png" alt="" /></div>
		{if $fitting_low.0.Icon}<div id="low1" class="fit-module {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:344px; top:143px;">{$fitting_low.0.Icon}</div>{/if}
		{if $fitting_low.1.Icon}<div id="low2" class="fit-module {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:350px; top:178px;">{$fitting_low.1.Icon}</div>{/if}
		{if $fitting_low.2.Icon}<div id="low3" class="fit-module {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:349px; top:213px;">{$fitting_low.2.Icon}</div>{/if}
		{if $fitting_low.3.Icon}<div id="low4" class="fit-module {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:340px; top:246px;">{$fitting_low.3.Icon}</div>{/if}
		{if $fitting_low.4.Icon}<div id="low5" class="fit-module {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:323px; top:277px;">{$fitting_low.4.Icon}</div>{/if}
		{if $fitting_low.5.Icon}<div id="low6" class="fit-module {if $fitting_high.5.destroyed}fit-destroyed{/if}" style="left:300px; top:304px;">{$fitting_low.5.Icon}</div>{/if}
		{if $fitting_low.6.Icon}<div id="low7" class="fit-module {if $fitting_high.6.destroyed}fit-destroyed{/if}" style="left:268px; top:324px;">{$fitting_low.6.Icon}</div>{/if}
		{if $fitting_low.7.Icon}<div id="low8" class="fit-module {if $fitting_high.7.destroyed}fit-destroyed{/if}" style="left:234px; top:338px;">{$fitting_low.7.Icon}</div>{/if}

		<div id="rigxx" class="fit-slot-bg">
			<img src="{$img_url}/panel/{$rigc}r.png" alt="" /></div>
		{if $fitting_rig.0.Icon}<div id="rig1" class="fit-module {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:148px; top:259px;">{$fitting_rig.0.Icon}</div>{/if}
		{if $fitting_rig.1.Icon}<div id="rig2" class="fit-module {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:185px; top:267px;">{$fitting_rig.1.Icon}</div>{/if}
		{if $fitting_rig.2.Icon}<div id="rig3" class="fit-module {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:221px; top:259px;">{$fitting_rig.2.Icon}</div>{/if}

		<div id="subx" class="fit-slot-bg">
			<img src="{$img_url}/panel/{$subc}s.png" alt="" /></div>
		{if $fitting_sub.0.Icon}<div id="sub1" class="fit-module {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:117px; top:131px;">{$fitting_sub.0.Icon}</div>{/if}
		{if $fitting_sub.1.Icon}<div id="sub2" class="fit-module {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:147px; top:108px;">{$fitting_sub.1.Icon}</div>{/if}
		{if $fitting_sub.2.Icon}<div id="sub3" class="fit-module {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:184px; top:98px;">{$fitting_sub.2.Icon}</div>{/if}
		{if $fitting_sub.3.Icon}<div id="sub4" class="fit-module {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:221px; top:107px;">{$fitting_sub.3.Icon}</div>{/if}
		{if $fitting_sub.4.Icon}<div id="sub5" class="fit-module {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:250px; top:131px;">{$fitting_sub.4.Icon}</div>{/if}

	{if $showammo}
		{if $fitting_ammo_high.0.type}<div id="high1l" class="fit-ammo {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:94px; top:88px;">{$fitting_ammo_high.0.type}</div>{/if}
		{if $fitting_ammo_high.1.type}<div id="high2l" class="fit-ammo {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:119px; top:70px;">{$fitting_ammo_high.1.type}</div>{/if}
		{if $fitting_ammo_high.2.type}<div id="high3l" class="fit-ammo {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:146px; top:58px;">{$fitting_ammo_high.2.type}</div>{/if}
		{if $fitting_ammo_high.3.type}<div id="high4l" class="fit-ammo {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:175px; top:52px;">{$fitting_ammo_high.3.type}</div>{/if}
		{if $fitting_ammo_high.4.type}<div id="high5l" class="fit-ammo {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:204px; top:52px;">{$fitting_ammo_high.4.type}</div>{/if}
		{if $fitting_ammo_high.5.type}<div id="high6l" class="fit-ammo {if $fitting_high.5.destroyed}fit-destroyed{/if}" style="left:232px; top:60px;">{$fitting_ammo_high.5.type}</div>{/if}
		{if $fitting_ammo_high.6.type}<div id="high7l" class="fit-ammo {if $fitting_high.6.destroyed}fit-destroyed{/if}" style="left:258px; top:72px;">{$fitting_ammo_high.6.type}</div>{/if}
		{if $fitting_ammo_high.7.type}<div id="high8l" class="fit-ammo {if $fitting_high.7.destroyed}fit-destroyed{/if}" style="left:280px; top:91px;">{$fitting_ammo_high.7.type}</div>{/if}

		{if $fitting_ammo_mid.0.type}<div id="mid1l" class="fit-ammo {if $fitting_high.0.destroyed}fit-destroyed{/if}" style="left:59px; top:154px;">{$fitting_ammo_mid.0.type}</div>{/if}
		{if $fitting_ammo_mid.1.type}<div id="mid2l" class="fit-ammo {if $fitting_high.1.destroyed}fit-destroyed{/if}" style="left:54px; top:182px;">{$fitting_ammo_mid.1.type}</div>{/if}
		{if $fitting_ammo_mid.2.type}<div id="mid3l" class="fit-ammo {if $fitting_high.2.destroyed}fit-destroyed{/if}" style="left:56px; top:210px;">{$fitting_ammo_mid.2.type}</div>{/if}
		{if $fitting_ammo_mid.3.type}<div id="mid4l" class="fit-ammo {if $fitting_high.3.destroyed}fit-destroyed{/if}" style="left:62px; top:238px;">{$fitting_ammo_mid.3.type}</div>{/if}
		{if $fitting_ammo_mid.4.type}<div id="mid5l" class="fit-ammo {if $fitting_high.4.destroyed}fit-destroyed{/if}" style="left:76px; top:265px;">{$fitting_ammo_mid.4.type}</div>{/if}
		{if $fitting_ammo_mid.5.type}<div id="mid6l" class="fit-ammo {if $fitting_high.5.destroyed}fit-destroyed{/if}" style="left:94px; top:288px;">{$fitting_ammo_mid.5.type}</div>{/if}
		{if $fitting_ammo_mid.6.type}<div id="mid7l" class="fit-ammo {if $fitting_high.6.destroyed}fit-destroyed{/if}" style="left:118px; top:305px;">{$fitting_ammo_mid.6.type}</div>{/if}
		{if $fitting_ammo_mid.7.type}<div id="mid8l" class="fit-ammo {if $fitting_high.7.destroyed}fit-destroyed{/if}" style="left:146px; top:318px;">{$fitting_ammo_mid.7.type}</div>{/if}
	{/if}

	{if $noBigImage}
		<div class="bigship"><img src="{$img_url}/panel/noship.png" alt="" /></div>
	{else}
		<div class="bigship"><img src="{$victimShipBigImage}" alt="" /></div>
	{/if}
		<div class="verified">
			{if $verify_yesno}
			<img class="verified-yes" src='{$img_url}/items/24_24/icon09_09.png' alt='Kill verified' title="Kill verified ID: {$verify_id}" />
			{else}
			<img class="verified-no" src='{$img_url}/items/24_24/icon09_13.png' alt='Kill not verified' title="Kill not verified" />
			{/if}
		</div>
	</div>
</div>