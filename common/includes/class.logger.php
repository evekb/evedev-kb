<?php
/*!
 * 
 *
 */
class logger
{
	function getip()
	{
		if (logger::validip($_SERVER["HTTP_CLIENT_IP"]))
		{
			return $_SERVER["HTTP_CLIENT_IP"];
		}

		foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip)
		{
			if (logger::validip(trim($ip)))
			{
				return $ip;
			}
		}
		if (logger::validip($_SERVER["HTTP_X_FORWARDED"]))
		{
			return $_SERVER["HTTP_X_FORWARDED"];
		}
		elseif (logger::validip($_SERVER["HTTP_FORWARDED_FOR"]))
		{
			return $_SERVER["HTTP_FORWARDED_FOR"];
		}
		elseif (logger::validip($_SERVER["HTTP_FORWARDED"]))
		{
			return $_SERVER["HTTP_FORWARDED"];
		}
		elseif (logger::validip($_SERVER["HTTP_X_FORWARDED"]))
		{
			return $_SERVER["HTTP_X_FORWARDED"];
		}
		else
		{
			return $_SERVER["REMOTE_ADDR"];
		}
	}
	function validip($ip)
	{
		if (!empty($ip) && ip2long($ip)!=-1)
		{
			$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
			);

			foreach ($reserved_ips as $r)
			{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	//! Log a new kill.
	function logKill($killid, $note = null)
	{
		if(is_null($note)) $note = logger::getip();

		$qry = DBFactory::getDBQuery(true);;
		$qry->execute("INSERT INTO kb3_log (log_kll_id, log_site, log_ip_address, log_timestamp) values(".
				$killid.",'".KB_SITE."','".$note."', now())");
	}
}