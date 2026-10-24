# Notes
Here are some files for usage in Firewall with dynamic ip lists.

### AbuseDB
A pull and push of the abusedb list ( https://github.com/borestad/blocklist-abuseipdb ) 1d,3d,30d ip blocklist but instead of single IPs the scripts calculates the possible CIDR Range to "compress" the list. So less entries are needed if you put it into some firewall which is capable of CIDR (Forti/Palo, etc.).

### SpamHaus
Also a pull and push of the SpamHaus Json DROP File ( https://www.spamhaus.org/drop/drop_v4.json ) without the JSON Stuff (which somehow seems a little not usable with json_decode; so just a regex on ip formats on it)

## Usage
### AbuseDB
Use the raw link to abusedb-cidr-1d.txt, abusedb-cidr-3d.txt (prefered) or abusedb-cidr-30d.txt (when your firewall supports the amount of entries, currently aprox. 250k)

AbuseDB 1d: https://raw.githubusercontent.com/knarfd/ipblocker/refs/heads/main/abusedb-cidr-1d.txt

AbuseDB 3d (recommended): https://raw.githubusercontent.com/knarfd/ipblocker/refs/heads/main/abusedb-cidr-3d.txt

AbuseDB 30d: https://raw.githubusercontent.com/knarfd/ipblocker/refs/heads/main/abusedb-cidr-30d.txt

### Spamhaus
Use the raw link to spamhaus-easy.txt
Spamhaus Easy: https://raw.githubusercontent.com/knarfd/ipblocker/refs/heads/main/spamhaus-easy.txt
