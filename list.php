<?php
session_start();
include "conf.php";
include "twitteroauth.php";
if($_GET['add'] == "1"){
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$request_token = $connection->getRequestToken();
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	$url = $connection->getAuthorizeURL($request_token['oauth_token'], false);
	header('Location: '.$url); 
}else if($_GET['add'] == "2"){
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	$request_token = $connection->getAccessToken(trim($_POST['pin']));
	$u=$connection->get('account/verify_credentials');
	if(!$u->screen_name) die("Invalid pin!");
	$_user[$u->screen_name] = $request_token;
	write_user($_user);
}
php?>
<style>body{font-family: sans-serif;}</style>
<title>DSTwitt</title>
<h1>User select</h1>
<ul>
<?php
foreach($_user as $k=>$v){
	print "<li><a href='u/$k'><button>$k</button></a></li>";
}
php?>
</ul>
<h1>Add new account</h1>
<ol>
	<li><a href="list.php?add=1" target="_blank">OAuth</a></li>
	<li>Pin: <form action="list.php?add=2" method="post" style="display: inline;"><input type='text' name='pin' /></form></li>
</ol>