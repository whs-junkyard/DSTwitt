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
try{
	$oldtweet = json_decode(file_get_contents("cache"));
}catch(Exception $e){$oldtweet = array();}
$lastupdate = $oldtweet->status;
$lastupdate = $lastupdate[0];
php?>
<script src="../<?php if($_GET['timeline']) print "../"; ?><?=$_conf['sizzle']?>"></script>
<style>
body{font-family: sans-serif;}
.g{color:gray;}
.t{font-size: 90%;}
ul{padding:0; margin:0; margin-top: 10px;}
li{border-bottom: black solid 1px; min-height: 24px;}
a{text-decoration:none;}
span{font-size:15pt;}
form{padding:0; margin:0;}
</style>
<style media="screen">
#r{float: none;}
.ssronly{display: none;}
.deskonly{display: inline;}
.actionbar{display: none;}
</style>
<style media="handheld">
#r{float: none;}
.ssronly{display: inline;}
.deskonly{display: none;}
.actionbar{display: block;}
</style>
<script>
var $ = Sizzle;
function t(u,i){
	e=$("#tweet")[0];
	e.value = "@"+u+" ";
	e.focus();
	e=$("#irp")[0];
	e.value = i;
}
function ut(u){
	$("#tweet")[0].value = u;
	$("#utl")[0].click();
}
function se(u){
	$("#tweet")[0].value = u;
}
function actbar(e){
	e=$(".actionbar", e)[0];
	if(e.style.display == "block") e.style.display = "none";
	else e.style.display = "block";
}
</script>
<title>@<?=$user?> | DSTwitt</title>
<div id="r"><span>@<?=$user?></span> | <a href="/u/<?=$user?>?rnd=<?=uniqid()?>">Home</a> | <a href="/u/<?=$user?>/replies?rnd=<?=uniqid()?>">Mentions</a> | <a href="/u/<?=$user?>/fav?rnd=<?=uniqid()?>">Favourite</a>
	<?php if(!$_SESSION['ssr']){ ?><span class="ssronly"> | <a href='/u/<?=$user?>?norefresh=true&ssr=true'>Text-only mode</a></span><?php } ?>
	<?php if($_SESSION['ssr']){ ?><span class="deskonly"> | <a href='/u/<?=$user?>?norefresh=true&ssr=false'>Image mode</a></span><?php } ?>
 | <?=date("g:i:s A")?></div>
<form action="<?=$fnpath?>" method="post">
	<input type='text' name='tweet' value='<?=$tweetText?>' id="tweet" /> 
	<input type='hidden' name='irp' id='irp' />
	<input type='submit' name='act' value='Tweet' />
	<input type='submit' name='act' value='User timeline' id='utl' />
	<input type='submit' name='act' value='Search' id='search' />
</form>
<ul>
<?php
flush();
$tw = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_user[$user]->oauth_token, $_user[$user]->oauth_token_secret);
if($_GET['rt']){
	$tw->post("statuses/retweet/".$_GET['rt'], array());
}
if($_GET['fav']){
	$tw->post("favorites/create/".$_GET['fav'], array());
}
if($_GET['unfav']){
	$tw->post("favorites/destroy/".$_GET['unfav'], array());
}
if($_GET['unfollow']){
	$tw->post("friendships/destroy/".$_GET['unfollow'], array());
}
if($_POST['tweet'] && $_POST['act'] == "Tweet"){
	$in = array("status" => $_POST['tweet']);
	if($_POST['irp']) $in['in_reply_to_status_id'] = $_POST['irp'];
	$tw->post("statuses/update", $in);
}
// img.php seems to malfunction without status object.
// (of course I edited img.php. Also, the earlier version of DSTwitt that use saved password
// use the old API and it put everything into the status key
if(!$_GET['norefresh']){
	if($_POST['act'] == "User timeline"){
		$tweet = (object) array("status" => $tw->get("statuses/user_timeline", array("count" => 20, "screen_name" => $_POST['tweet'])));
		$tlname = "@".htmlspecialchars($_POST['tweet']);
	}else if($_POST['act'] == "Search"){
		$_data = json_decode($tw->http("http://search.twitter.com/search.json?q=".urlencode($_POST['tweet'])."&rpp=20", "GET"));
		$rtd = array();
		foreach($_data->results as $t){
			$c = count($rtd);
			$rtd[$c]['source'] = html_entity_decode($t->source);
			$rtd[$c]['favorited'] = false;
			$rtd[$c]['id'] = $t->id;
			$rtd[$c]['text'] = $t->text;
			$rtd[$c]['created_at'] = $t->created_at;
			$rtd[$c]['user']['screen_name'] = $t->from_user;
			$rtd[$c]['user'] = (object) $rtd[$c]['user'];
			$rtd[$c] = (object) $rtd[$c];
		}
		$tweet = (object) array("status" => $rtd);
		$tlname = "Search for ".htmlspecialchars($_POST['tweet']);
	}else if($_GET['timeline'] == "replies"){
		$tweet = (object) array("status" => $tw->get("statuses/mentions", array("count" => 20)));
		$tlname = "Replies";
	}else if($_GET['timeline'] == "fav"){
		$tweet = (object) array("status" => $tw->get("favorites", array()));
		$tlname = "Favourites";
	}else{
		$tweet = (object) array("status" => $tw->get("statuses/home_timeline", array("count" => 20)));
		$tlname = "Home";
	}
}else{
	$tweet = json_decode(file_get_contents("cache"));
}
file_put_contents("cache", json_encode((array) $tweet));
print "<b>".$tlname."</b>";
// Parse clientdb.tsv
$clientdb = array();
foreach(split("\n", file_get_contents("clientdb.tsv")) as $i){
	$i = split("\t", trim($i));
	$clientdb[trim($i[0])] = trim($i[1]);
}
if(!$tweet->status){
	if($_POST['act'] == "User timeline")
		$kind = "username";
	else if($_POST['act'] == "Search")
		$kind = "query";
	else
		$kind = "N/A";
	print "No data received. Did the $kind \"<b>".$_POST['tweet']."</b>\" correct?<br />Bug? Report at <a href='https://bugs.launchpad.net/dstwitt'>https://bugs.launchpad.net/dstwitt</a>";
}else{
	$i=0;
	foreach($tweet->status as $t){
		if(in_array(strip_tags($t->source), $_conf['blockedclients'])) continue;
		if(in_array($t->user->screen_name, $_conf['hideuser'])) continue;
		print '<li onclick="actbar(this);">';
		if($t->id == $lastupdate->id) print "<b>&gt;&gt;&gt;</b> ";
		if($t->favorited) print "&#x2661;";
		$client = strip_tags($t->source);
		if($clientdb[$client]) $client .= " on <b>".$clientdb[$client]."</b>";
		$ti = date("g:i:s A", strtotime($t->created_at));
		if($_SESSION['ssr']){
			print '<b>'.$t->user->screen_name.'</b> '.$t->text;
		}else{
			print '<b>'.$t->user->screen_name.'</b> <img src="/t/'.$t->id_str.'" />';
		}
		echo '<div class="actionbar">';
		if($t->user->id){
			print '<a href="'.$fnpath.'?unfollow='.$t->user->id.'" onclick="return confirm(\'Unfollow?\')"><button>Unfollow</button></a>';
			print '<a href="#" onclick="t(\''.$t->user->screen_name.'\', '.$t->id_str.'); return false;"><button>@</button></a>';
			print '<a href="'.$fnpath.'?rt='.$t->id_str.'"><button>RT</button></a>';
			print '<a href="#" onclick="ut(\''.$t->user->screen_name.'\'); return false;"><button>TL</button></a>';
			if($t->favorited) print '<a href="'.$fnpath.'?unfav='.$t->id_str.'"><button><b>Faved</b></button></a>';
			else print '<a href="'.$fnpath.'?fav='.$t->id_str.'"><button>Fave</button></a>';
		}
		print $ti.' | '.$client;
		if(preg_match("~http://twitpic.com/([^ ]+)~", $t->text, $twtpic)){
			print " <a href='/twitpic/".$twtpic[1]."'>TwitPic</a> ";
		}
		if(preg_match_all("~#([^ \"]+)~", $t->text, $hashtag)){
			foreach(array_unique($hashtag[0]) as $tag){
				print " <a href='#' onclick='se(\"$tag\")'>$tag</a> ";
			}
		}
		print '</div></li>';
		flush();
		$i++;
		if($i > $_conf['display']) break;
	}
}
php?>
</ul>
