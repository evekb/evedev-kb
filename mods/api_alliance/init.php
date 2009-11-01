<?php
require_once("mods/api_alliance/class.api_alliance.php");

event::register("allianceDetail_assembling", "APIAllianceMod::replaceStats");
event::register("allianceDetail_assembling", "APIAllianceMod::addCorpList");

