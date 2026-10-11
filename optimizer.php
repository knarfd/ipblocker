<?php

// Liste der URLs mit IP-Listen
$urls = [
#    "https://raw.githubusercontent.com/stamparm/ipsum/master/levels/3.txt",
#    "https://iplists.firehol.org/files/firehol_level3.netset",
#    "https://lists.blocklist.de/lists/all.txt",
#    "https://raw.githubusercontent.com/knarfd/ipblocker/refs/heads/main/spamhaus-easy.txt",
    "https://raw.githubusercontent.com/borestad/blocklist-abuseipdb/refs/heads/main/abuseipdb-s100-3d.ipv4"
];

// Alle IPs sammeln
$allIps = [];

foreach ($urls as $url) {
    echo "Lade: $url\n";
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "Fehler beim Laden: $url\n";
        continue;
    }
    //$lines = explode("\n", $content);
    //echo "\ngeladen:" . count($lines) . "\n";

    $regexIpAddress = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?:\/\d{2})?/';
    preg_match_all($regexIpAddress, $content, $lines);

    echo "\ngeladen:" . count($lines) . "\n";
//    $lines = explode("\n", $content);


    foreach ($lines as $line) {
        $ip = ($line);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $allIps[] = $ip;
        }
    }
}

echo "\nGesamt" . count($allIps) . "\n";

$uniqueIps = array_unique($allIps);
echo "\nEindeutige:" . count ($uniqueIps) . "\n";
sort($uniqueIps);

// IPs in Long-Form
$longIps = array_map('ip2long', $uniqueIps);
sort($longIps);

// Prüft, ob eine IP in einem Subnetz enthalten ist
function ipInCidr(int $ip, string $cidr): bool {
    list($subnet, $masklen) = explode('/', $cidr);
    $subnetLong = ip2long($subnet);
    $mask = -1 << (32 - (int)$masklen);
    return ($ip & $mask) === ($subnetLong & $mask);
}

// Prüft, ob ein IP-Block ein gültiges Subnetz ist
function canFormSubnet(array $ips, int $cidr): bool {
    $mask = -1 << (32 - $cidr);
    $base = $ips[0] & $mask;
    foreach ($ips as $ip) {
        if (($ip & $mask) !== $base) {
            return false;
        }
    }
    return true;
}

// IPs zu Netzen zusammenfassen, bereits abgedeckte IPs werden übersprungen
function summarizeIps(array $ips): array {
    $result = [];
    $covered = [];

    $count = count($ips);
    $i = 0;

    while ($i < $count) {
        // Prüfen, ob IP schon abgedeckt
        $ip = $ips[$i];
        $isCovered = false;
        foreach ($result as $cidr) {
            if (ipInCidr($ip, $cidr)) {
                $isCovered = true;
                break;
            }
        }
        if ($isCovered) {
            $i++;
            continue;
        }

        $found = false;
        // Versuche größtes passendes Netz zuerst
        for ($cidr = 24; $cidr <= 32; $cidr++) {
            $blockSize = 1 << (32 - $cidr);
            $block = array_slice($ips, $i, $blockSize);

            if (count($block) < $blockSize) continue;
            if (canFormSubnet($block, $cidr)) {
                $result[] = long2ip($block[0]) . "/$cidr";
                $i += $blockSize;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $result[] = long2ip($ips[$i]) . "/32";
            $i++;
        }
    }

    return $result;
}

$optimized = summarizeIps($longIps);

// Ausgabe
echo "\nOptimierte IP/Subnet-Liste:\n";

echo "\nCount:". count($optimized) . "\n";

/*
foreach ($optimized as $entry) {
    echo $entry . "\n";
}
*/

$export = implode(PHP_EOL, $optimized);
$filename='optimized.txt';
file_put_contents($filename, $export);

?>
