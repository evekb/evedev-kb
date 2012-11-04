<?php
	class serverstatus
	{
		function display()
		{
			$statusBox = new box("EVE Status");
			$statusApi = new API_ServerStatus();
			if ($statusApi->fetchXML())
			{
				if ($statusApi->getserverOpen())
				{
					$statusBox->addOption("caption", "EVE Server is <span><strong><font color=green>ONLINE</font></strong></span>");
					$statusBox->addOption("caption", "Players Online: " . $statusApi->getOnlinePlayers());
				}
				else
				{
					$statusBox->addOption("caption", "EVE Server is <span><strong><font color=red>OFFLINE</font></strong></span>");
				}
			}
			else
			{
				$statusBox->addOption("caption", "EVE Server is <span><strong><font color=red>UNKNOWN</font></strong></span>");
				$statusBox->addOption("caption", "Players Online: Unknown");
				$statusBox->addOption("caption", "EVE API is <span><strong><font color=red>DOWN</font></strong></span>");
			}
			if(config::get("show_clock"))
			{
				$statusBox->addOption("caption", "EVE Time:  <span><strong><font color=orange>" . gmdate("H:i") . "</font></strong></span>");
			}
			return $statusBox->generate();
		}
	}
?>
