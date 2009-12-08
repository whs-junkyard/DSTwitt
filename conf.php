<?php
define("CONSUMER_KEY", "TFehjBocpqYRsaUe92uKgA");
define("CONSUMER_SECRET", "n1OHA5lwzNpFiM4PNrmpJLMHJQNInJ1JWkEtpvHc");

// users parser
$_user = (array) json_decode(file_get_contents("userdb"));
function write_user($_user){
	file_put_contents("userdb", json_encode($_user));
}