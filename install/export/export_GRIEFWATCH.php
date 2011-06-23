<?php
/**
 * @package EDK
 */

$host = 'http://guard.griefwatch.net/';
$num_kills = 3480;
$saveto = './mails';
exit('please edit file before running');

for ($i = 0; $i<=$num_kills; $i++)
{
    $kill = $i;
    if (file_exists($saveto.'/'.$kill.'.txt'))
    {
        continue;
    }
    $url = $host.'?p=details&kill='.$kill;
    echo "reading kill $kill<br>\n";
    $content = file_get_contents($url);
    $content = str_replace("\r\n", "", $content);
    $content = str_replace("\n", "", $content);

    $pattern = '^<div class="box" style="margin-top: 10px; display: none;" id="raw">.*?style="padding: 2px;">(.*?)<script type="text/javascript">^';
    preg_match($pattern, $content, $raw);

    $mail = strip_tags(str_replace('<br>', "\r\n", $raw[1]));

    if ($kill)
    {
        $fp = fopen($saveto.'/'.$kill.'.txt', "w");
        fwrite($fp, $mail);
        fclose($fp);
        flush();
    }
}
?>