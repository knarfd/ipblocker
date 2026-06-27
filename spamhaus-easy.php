<?php

// Functions
function getUrlContent($url){
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
 curl_setopt($ch, CURLOPT_TIMEOUT, 5);
 $data = curl_exec($ch);
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 curl_close($ch);
 return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

// Start here
$data=getURLContent('https://www.spamhaus.org/drop/drop_v4.json');

if ($data)
{


        $regexIpAddress = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?:\/\d{2})?/';
        preg_match_all($regexIpAddress, $data, $ip_matches);

//echo $data;
//var_dump($ip_matches);
$lines = implode(PHP_EOL, $ip_matches[0]);
//var_dump($lines);

$filename='spamhaus-easy.txt';
file_put_contents($filename, $lines);
//echo $filename;

}



?>
