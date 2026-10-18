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

function ip2long_safe($ip) {
    $long = ip2long($ip);
    if ($long === false) {
        throw new Exception("Invalid IP address: $ip");
    }
    return sprintf('%u', $long); // unsigned
}

function long2ip_safe($long) {
    return long2ip((int) $long);
}

// Generiert alle möglichen CIDR-Blöcke zwischen zwei IPs
function rangeToCIDRs($startIp, $endIp) {
    $start = ip2long_safe($startIp);
    $end = ip2long_safe($endIp);
    $cidrs = [];

    while ($start <= $end) {
        $maxSize = 32;
        while ($maxSize > 0) {
            $mask = (1 << (32 - ($maxSize - 1))) - 1;
            if (($start & ~$mask) != $start) {
                break;
            }
            $maxSize--;
        }

        $maxDiff = 32 - floor(log($end - $start + 1) / log(2));
        if ($maxSize < $maxDiff) {
            $maxSize = $maxDiff;
        }

        $cidrs[] = long2ip_safe($start) . "/$maxSize";
        $start += pow(2, (32 - $maxSize));
    }

    return $cidrs;
}

function summarizeIps(array $ips) {
    $longs = array_map('ip2long_safe', $ips);
    sort($longs, SORT_NUMERIC);

    $result = [];
    $rangeStart = $longs[0];
    $rangeEnd = $rangeStart;

    for ($i = 1, $n = count($longs); $i < $n; $i++) {
        if ($longs[$i] == $rangeEnd + 1) {
            $rangeEnd = $longs[$i];
        } else {
            // Range beenden und CIDRs erzeugen
            $result = array_merge($result, rangeToCIDRs(long2ip_safe($rangeStart), long2ip_safe($rangeEnd)));
            $rangeStart = $longs[$i];
            $rangeEnd = $rangeStart;
        }
    }

    // Letzten Bereich hinzufügen
    $result = array_merge($result, rangeToCIDRs(long2ip_safe($rangeStart), long2ip_safe($rangeEnd)));

    return $result;
}



// Start here

$howmanydays='3';

if ($argc)
	{
	$howmanydays=$argv[1];
	}

$data=getURLContent('https://raw.githubusercontent.com/borestad/blocklist-abuseipdb/refs/heads/main/abuseipdb-s100-' . $howmanydays . 'd.ipv4');

if ($data)
{
	$regexIpAddress = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?:\/\d{2})?/';
	preg_match_all($regexIpAddress, $data, $ip_matches);

$CIDRIPs=summarizeIps($ip_matches[0]);

$lines = implode(PHP_EOL, $CIDRIPs);
$filename='abusedb-cidr-'.$howmanydays.'d.txt';
file_put_contents($filename, $lines);

// Make Smaller Parts (just half / half)
$mid = ceil(count($CIDRIPs) / 2); // Aufteilen in zwei Hälften
$firstHalf = array_slice($CIDRIPs, 0, $mid);
$secondHalf = array_slice($CIDRIPs, $mid);

// Erste Hälfte schreiben
$filename1 = 'abusedb-cidr-' . $howmanydays . 'd-part1.txt';
file_put_contents($filename1, implode(PHP_EOL, $firstHalf));

// Zweite Hälfte schreiben
$filename2 = 'abusedb-cidr-' . $howmanydays . 'd-part2.txt';
file_put_contents($filename2, implode(PHP_EOL, $secondHalf));

}

?>
