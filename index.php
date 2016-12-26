<?php
$cookiedomain 	= str_replace("http://www.","",base64_decode('aHR0cDovL3d3dy5pbWRiLmNvbS8='));
$cookiedomain 	= str_replace("https://www.","",$cookiedomain);
$cookiedomain 	= str_replace("www.","",$cookiedomain);
$url 			= base64_decode('aHR0cDovL3d3dy5pbWRiLmNvbS8=') . $_SERVER['REQUEST_URI'];

$bodyReplace = array(
	"http://ia.media-imdb.com" 									=> "http://nfqq.nvswi2lbfvuw2zdcfzrw63i.nblk.ru",
	'https://secure.imdb.com' 									=> '',
	'http://pubads.g.doubleclick.net/gampad/ads?' 				=> '',
	'http://s.media-imdb.com/twilight/?' 						=> '',
	'<div class="ab_twitter">' 									=> '<div class="ab_twitter" style="display:none">',
	'<div class="ab_facebook">' 								=> '<div class="ab_facebook" style="display:none">',
	'PageType=homepage.java&Geo=DE&' 							=> '',
	'<input autocomplete="off" value="" name="q" id="navbar-query" placeholder="' 				=> '<input style="height: 30px;" autocomplete="off" value="" name="q" id="navbar-query" placeholder="',
	'http://pubads.g.doubleclick.net/gampad/jump?&iu=4215/imdb2.consumer.homepage/&sz=1008x150'	=> '',
);

$page 	= file_get_contents($url);
$doc 	= new DOMDocument();
@$doc->loadHTML($page);
$divs = $doc->getElementsByTagName('h1');
foreach($divs as $div) {
    if ($div->getAttribute('itemprop') === 'name') {
		$last =  $div->nodeValue;
    }
}

if($_SERVER['HTTPS'] == 'on'){
	$mydomain = 'https://'.$_SERVER['HTTP_HOST'];
} else {
	$mydomain = 'http://'.$_SERVER['HTTP_HOST'];
}

$imdbcurl = curl_init();

curl_setopt($imdbcurl, CURLOPT_URL, $url);
curl_setopt($imdbcurl, CURLOPT_HEADER, 1);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	curl_setopt($imdbcurl, CURLOPT_POST, 1);
	curl_setopt($imdbcurl, CURLOPT_POSTFIELDS, $_POST);
}

curl_setopt($imdbcurl, CURLOPT_RETURNTRANSFER,1);
curl_setopt($imdbcurl, CURLOPT_TIMEOUT,10);
curl_setopt($imdbcurl, CURLOPT_SSL_VERIFYHOST, 0);

foreach($_COOKIE as $k=>$v){
	if(is_array($v)){
		$v = serialize($v);
	}
	curl_setopt($imdbcurl,CURLOPT_COOKIE,"$k=$v; domain=.$cookiedomain ; path=/");
}

$response = curl_exec($imdbcurl);

if (strpos($url,"title/") !== false) {
	$imdbcode = explode("/", $url);
}

if(curl_error($imdbcurl)){
	print curl_error($imdbcurl);
} else {
	$response = str_replace("HTTP/1.1 100 Continue\r\n\r\n","",$response);
	$ar = explode("\r\n\r\n", $response, 2);

	$header = $ar[0];
	$body = $ar[1];

	while(strpos($body,"<script async")>0){
		$offs = strpos($body,"<script async");
		$body = substr($body,0,$offs).substr($body,strpos($body,"push({})")+40);
	}
	$body = str_replace(base64_decode('aHR0cDovL3d3dy5pbWRiLmNvbS8='),$mydomain,$body);
	
	foreach($bodyReplace as $k => $v){
		$body = str_replace($k,$v,$body);
	}

	$body = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $body);
	print $body;
}

curl_close($imdbcurl);
?>
