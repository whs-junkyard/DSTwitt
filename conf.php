<?php
define("CONSUMER_KEY", "TFehjBocpqYRsaUe92uKgA");
define("CONSUMER_SECRET", "n1OHA5lwzNpFiM4PNrmpJLMHJQNInJ1JWkEtpvHc");
$_conf = array();
$_conf['blockedclients'] = array(
	"foursquare", "Playground"
);
$_conf['hideuser'] = array();
$_conf['display'] = 10; //max = 20
$_conf['sizzle'] = "selector.js"; // selector.js = class/id only; sizzle.js = css3, very slow

// users parser
$_user = (array) json_decode(file_get_contents("userdb"));
function write_user($_user){
	file_put_contents("userdb", json_encode($_user));
}