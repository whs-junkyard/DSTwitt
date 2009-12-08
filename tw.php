<?php
session_start();
include "conf.php";
include "twitteroauth.php";
date_default_timezone_set('Asia/Bangkok');
$user = $_GET['user'];
if(!in_array($user, array_keys($_user))){die("<h1>No such user!</h1>");}
if($_GET['reply'])
	$tweetText = "@".$_GET['reply']." ";
$fnpath = basename($_SERVER['REQUEST_URI']);
if (strpos($fnpath, "?") !== false) $fnpath = reset(explode("?", $fnpath));
if($_GET['ssr'] == "true") $_SESSION['ssr'] = true;
else if($_GET['ssr'] == "false") $_SESSION['ssr'] = false;
php?>
<style>
body{font-family: sans-serif;}
.g{color:gray;}
.t{font-size: 90%;}
ul{padding:0; margin:0; margin-top: 10px;}
li{border-bottom: black solid 1px;}
a{text-decoration:none;}
span{font-size:15pt;}
form{padding:0; margin:0;}
</style>
<style media="screen">
#r{float:right;}
.ssronly{display: none;}
.deskonly{display: inline;}
</style>
<style media="handheld">
#r{float: none;}
.ssronly{display: inline;}
.deskonly{display: none;}
</style>
<script>
function t(u,i){
	e=document.getElementById("tweet");
	e.value = "@"+u+" ";
	e.focus();
	e=document.getElementById("irp");
	e.value = i;
}
</script>
<title>DSTwitt</title>
<div id="r"><span>@<?=$user?></span> | <a href="/u/<?=$user?>?rnd=<?=uniqid()?>">Home</a> | <a href="/u/<?=$user?>/replies?rnd=<?=uniqid()?>">Replies</a>
	<?php if(!$_SESSION['ssr']){ ?><span class="ssronly"> | <a href='/u/<?=$user?>?norefresh=true&ssr=true'>Text-only mode</a></span><?php } ?>
	<?php if($_SESSION['ssr']){ ?><span class="deskonly"> | <a href='/u/<?=$user?>?norefresh=true&ssr=false'>Image mode</a></span><?php } ?>
 | <?=date("g:i:s A")?></div>
<form action="<?=$fnpath?>" method="post">
	<input type='text' name='tweet' value='<?=$tweetText?>' id="tweet" /> 
	<input type='hidden' name='irp' id='irp' />
	<input type='submit' value='Tweet' />
</form>
<ul>
<?php
flush();
$tw = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_user[$user]->oauth_token, $_user[$user]->oauth_token_secret);
if($_GET['rt']){
	$out = $tw->post("statuses/retweet/".$_GET['rt'], array());
}
if($_POST['tweet']){
	$in = array("status" => $_POST['tweet']);
	if($_POST['irp']) $in['in_reply_to_status_id'] = $_POST['irp'];
	$out = $tw->post("statuses/update", $in);
}
// img.php seems to malfunction without status object.
// (of course I edited img.php. Also, the earlier version of DSTwitt that use saved password
// use the old API and it put everything into the status key
if(!$_GET['norefresh']){
	if($_GET['timeline'] == "replies"){
		$tweet = (object) array("status" => $tw->get("statuses/mentions", array("count" => 11)));
	}else{
		$tweet = (object) array("status" => $tw->get("statuses/home_timeline", array("count" => 11)));
	}
}else{
	$tweet = json_decode(file_get_contents("cache"));
}
file_put_contents("cache", json_encode((array) $tweet));
foreach($tweet->status as $t){
	print '<li><a href="#" onclick="t(\''.$t->user->screen_name.'\', '.$t->id.'); return false;"><button>@</button></a>';
	print '<a href="'.$fnpath.'?rt='.$t->id.'"><button>RT</button></a>';
	if(preg_match("~http://twitpic.com/([^ ]+)~", $t->text, $twtpic)){
		print "<a href='/twitpic/".$twtpic[1]."'>";
	}
	if($_SESSION['ssr']){
		$client = strip_tags($t->source);
		$ti = date("g:i:s A", strtotime($t->created_at));
		print $t->user->screen_name.' '.$t->text.' ('.$ti.' | '.$client.')';
	}else{
		print '<img src="/t/'.$t->id.'" />';
	}
	if($twtpic) print "</a>";
	print '</li>';
	flush();
}
php?>
</ul>