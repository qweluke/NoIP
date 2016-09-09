# NoIP Auto renewal IP

Simple script to auto-renew your IP's without visiting webpage or clicking email buttons ;-)

It's simple as:


```
require_once('NoIP/NoIP.php');

$noIp = new Noip('login','password');

$result = $noIp->refreshHosts();

print_r( $result );
```

You can also use: `getHosts()` to get information about all your hosts.
