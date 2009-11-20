{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<div id="fitting" style="position:relative; height:398px; width:398px;" title="fitting">
	<div id="mask" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
		<img border="0" style="position:absolute; height:398px; width:398px;" src='{$img_url}/{$themedir}/{$panel_colour}.png' alt='' /></div>
	<div id="high1" style="position:absolute; left:73px; top:90px; width:32px; height:32px; z-index:1;">{$fitting_high.0.Icon}</div>
	<div id="high2" style="position:absolute; left:100px; top:67px; width:32px; height:32px; z-index:1;">{$fitting_high.1.Icon}</div>
	<div id="high3" style="position:absolute; left:133px; top:50px; width:32px; height:32px; z-index:1;">{$fitting_high.2.Icon}</div>
	<div id="high4" style="position:absolute; left:167px; top:41px; width:32px; height:32px; z-index:1;">{$fitting_high.3.Icon}</div>
	<div id="high5" style="position:absolute; left:202px; top:41px; width:32px; height:32px; z-index:1;">{$fitting_high.4.Icon}</div>
	<div id="high6" style="position:absolute; left:236px; top:50px; width:32px; height:32px; z-index:1;">{$fitting_high.5.Icon}</div>
	<div id="high7" style="position:absolute; left:270px; top:65px; width:32px; height:32px; z-index:1;">{$fitting_high.6.Icon}</div>
	<div id="high8" style="position:absolute; left:295px; top:89px; width:32px; height:32px; z-index:1;">{$fitting_high.7.Icon}</div>

	<div id="mid1" style="position:absolute; left:48px; top:133px; width:32px; height:32px; z-index:1;">{$fitting_med.0.Icon}</div>
	<div id="mid2" style="position:absolute; left:40px; top:168px; width:32px; height:32px; z-index:1;">{$fitting_med.1.Icon}</div>
	<div id="mid3" style="position:absolute; left:40px; top:203px; width:32px; height:32px; z-index:1;">{$fitting_med.2.Icon}</div>
	<div id="mid4" style="position:absolute; left:50px; top:237px; width:32px; height:32px; z-index:1;">{$fitting_med.3.Icon}</div>
	<div id="mid5" style="position:absolute; left:66px; top:267px; width:32px; height:32px; z-index:1;">{$fitting_med.4.Icon}</div>
	<div id="mid6" style="position:absolute; left:91px; top:292px; width:32px; height:32px; z-index:1;">{$fitting_med.5.Icon}</div>
	<div id="mid7" style="position:absolute; left:123px; top:313px; width:32px; height:32px; z-index:1;">{$fitting_med.6.Icon}</div>
	<div id="mid8" style="position:absolute; left:155px; top:326px; width:32px; height:32px; z-index:1;">{$fitting_med.7.Icon}</div>

	<div id="low1" style="position:absolute; left:313px; top:133px; width:32px; height:32px; z-index:1;">{$fitting_low.0.Icon}</div>
	<div id="low2" style="position:absolute; left:325px; top:170px; width:32px; height:32px; z-index:1;">{$fitting_low.1.Icon}</div>
	<div id="low3" style="position:absolute; left:325px; top:205px; width:32px; height:32px; z-index:1;">{$fitting_low.2.Icon}</div>
	<div id="low4" style="position:absolute; left:316px; top:239px; width:32px; height:32px; z-index:1;">{$fitting_low.3.Icon}</div>
	<div id="low5" style="position:absolute; left:298px; top:271px; width:32px; height:32px; z-index:1;">{$fitting_low.4.Icon}</div>
	<div id="low6" style="position:absolute; left:276px; top:296px; width:32px; height:32px; z-index:1;">{$fitting_low.5.Icon}</div>
	<div id="low7" style="position:absolute; left:248px; top:315px; width:32px; height:32px; z-index:1;">{$fitting_low.6.Icon}</div>
	<div id="low8" style="position:absolute; left:211px; top:326px; width:32px; height:32px; z-index:1;">{$fitting_low.7.Icon}</div>

	<div id="rig1" style="position:absolute; left:185px; top:110px; width:32px; height:32px; z-index:1;">{$fitting_rig.0.Icon}</div>
	<div id="rig2" style="position:absolute; left:160px; top:160px; width:32px; height:32px; z-index:1;">{$fitting_rig.1.Icon}</div>
	<div id="rig3" style="position:absolute; left:208px; top:160px; width:32px; height:32px; z-index:1;">{$fitting_rig.2.Icon}</div>

	<div id="sub1" style="position:absolute; left:119px; top:214px; width:32px; height:32px; z-index:1;">{$fitting_sub.0.Icon}</div>
	<div id="sub2" style="position:absolute; left:145px; top:245px; width:32px; height:32px; z-index:1;">{$fitting_sub.1.Icon}</div>
	<div id="sub3" style="position:absolute; left:185px; top:257px; width:32px; height:32px; z-index:1;">{$fitting_sub.2.Icon}</div>
	<div id="sub4" style="position:absolute; left:224px; top:244px; width:32px; height:32px; z-index:1;">{$fitting_sub.3.Icon}</div>
	<div id="sub5" style="position:absolute; left:250px; top:215px; width:32px; height:32px; z-index:1;">{$fitting_sub.4.Icon}</div>

	{if $showammo}
	<div id="high1l" style="position:absolute; left:98px; top:114px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.0.type}</div>
	<div id="high2l" style="position:absolute; left:120px; top:95px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.1.type}</div>
	<div id="high3l" style="position:absolute; left:146px; top:82px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.2.type}</div>
	<div id="high4l" style="position:absolute; left:174px; top:76px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.3.type}</div>
	<div id="high5l" style="position:absolute; left:202px; top:76px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.4.type}</div>
	<div id="high6l" style="position:absolute; left:230px; top:83px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.5.type}</div>
	<div id="high7l" style="position:absolute; left:254px; top:97px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.6.type}</div>
	<div id="high8l" style="position:absolute; left:275px; top:116px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.7.type}</div>

	<div id="mid1l" style="position:absolute; left:75px; top:146px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.0.type}</div>
	<div id="mid2l" style="position:absolute; left:70px; top:174px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.1.type}</div>
	<div id="mid3l" style="position:absolute; left:70px; top:202px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.2.type}</div>
	<div id="mid4l" style="position:absolute; left:78px; top:230px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.3.type}</div>
	<div id="mid5l" style="position:absolute; left:94px; top:256px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.4.type}</div>
	<div id="mid6l" style="position:absolute; left:112px; top:276px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.5.type}</div>
	<div id="mid7l" style="position:absolute; left:136px; top:291px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.6.type}</div>
	<div id="mid8l" style="position:absolute; left:164px; top:301px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.7.type}</div>
	{/if}
</div>