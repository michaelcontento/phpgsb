<?php

include 'safebrowsing.class.php';

$class = new GoogleSafeBrowsing('ABQIAAAAqkWYEbo3LIYtxwQNWk0RbhTm4_7vVQOTE_iGsfoCNq06amSRbA', true);

//echo "## Retreive Blacklist\n";
//print_r($class->getBlackList());

echo "## Retreive Malwarelist\n";
print_r($class->getMalwareList());

/*$googleTests = array();
$googleTests[] = array('Already in canonical form',
                       'http://google.com/',
                       'http://google.com/');
$googleTests[] = array('Need a trailing slash, and lowercase the host',
                       'http://gOOgle.com',
                       'http://google.com/'); 
$googleTests[] = array('Strip leading and trailing dots, collapse multiple dots',
                       'http://..google..com../',
                       'http://google.com/');
$googleTests[] = array('Fully unescape, then re-escape once',
                       'http://google.com/%25%34%31%25%31%46',
                       'http://google.com/A%1F/');
$googleTests[] = array('Escape characters in the hostname',
                       'http://google^.com/',
                       'http://google%5E.com/');
$googleTests[] = array('Relative path resolution', 
                       'http://google.com/1/../2/././', 
                       'http://google.com/2/');
$googleTests[] = array('Collapse consecutive slashes in the path', 
                       'http://google.com/1//2?3//4',
                       'http://google.com/1/2?3//4');
$googleTests[] = array('IP: Normal decimal form', 
                       '1.2.3.4',
                       'http://1.2.3.4/');
$googleTests[] = array('IP: Octal components, identified by a leading zero', 
                       '012.034.01.055',
                       'http://10.28.1.45/');
$googleTests[] = array('IP: Hex components, identified by a leading 0[xX]', 
                       '0x12.0x43.0x44.0x01',
                       'http://18.67.68.1/');
$googleTests[] = array('IP: Fewer than 4 components, extend the last component to fill the remaining bytes', 
                       '167838211',
                       'http://10.1.2.3/');
$googleTests[] = array('IP: Mixed bases, fewer than 4 components', 
                       '12.0x12.01234',
                       'http://12.18.2.156/');
$googleTests[] = array('IP: Since 276 is not the last component, it\'s only allowed to take up 1 byte in the result', 
                       '276.2.3',
                       'http://20.2.0.3/');
$googleTests[] = array('IP: If the number is larger than 32 bits, take the low 32 bits', 
                       '0x10000000b',
                       'http://0.0.0.11/');

$counter = 1;
foreach ($googleTests as $test) {
    echo "Test #{$counter}: {$test[0]}... ";
    $return = $class->canonicalize($test[1]);
    
    if ($test[2] == ('http://' . $return)) {
        echo "Done\n";
    } else {
        echo "ERROR !!\n";
        echo "  !! 'http://{$return}' should be '{$test[2]}'\n";
    }
    
    $counter += 1;
}

print_r($class->lookupsFor('http://a.b.c.d.e.f.g/1.html'));
echo "\n";

print_r($class->lookupsFor('http://a.b.c/1/3/4/5/6/2.html?param=1'));
echo "\n";

print_r($class->lookupsFor('http://s.e.h.r.l.a.n.g.e.r.h.o.s.t/1/2/3/4/5/6/7/8/9/10?p=1&p=2&p=3#anker1'));
echo "\n";*/

/*$a = $class->lookupsFor('http://216.84.195.93//https:/SIngIn/signin.ebay.com/ws/login/eBay_com Verify your eBay account.htm');
foreach ($a as $item) {
    echo "{$item}\n";
}*/