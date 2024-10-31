<?php
/*
 * Plugin Name: PHP Image Cache
 * Plugin URI: http://www.webfish.se/wp/plugins/php-image-cache
 * Version: 1.1.2
 * Description: Cache images, the PHP way. Other cahce plugins depends on your server settings. This plugin does allwas cache your images.
 * Author: Tobias Nyholm
 * Author URI: http://www.tnyholm.se
 * License: GPLv3
 * Copyright: Tobias Nyholm 2010
 */
/*
Copyright (C) 2010 Tobias Nyholm

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * This filter rewrites the urls.
 * @param $content
 */
function pic_rewriteUrls($content){
	$siteInfo=pic_getDomainAndPage();
	
	//regex to find images
	$regex = '|<img .*?src=(?:["'."'".'])(.*?)(?:["'."'".']).*?>|sm';
	$match = preg_match_all($regex, $content, $matches);
	//die ("<pre>".print_r($matches,true)); //debug code

	//if some images exists
	if ($match){
		foreach($matches[1] as $key=>$url){			
			//replace the url
			$content = str_replace($url, pic_getNewUrl($url,$siteInfo), $content);
		}
	}


	return $content;
}
//"1001" because I want this filter to be the last to run
add_filter("the_content","pic_rewriteUrls",1001);





/**
 * 
 * This filter rewrites the urls. 
 * @param unknown_type $url
 */
function pic_getNewUrl($originalUrl, $siteInfo=null){
	//make sure we haven't allready rewritten this url
	if(strstr($originalUrl,"image.php?path=")!==false)
		return $originalUrl;
	
	if($siteInfo===null)
		$siteInfo=pic_getDomainAndPage();
	
	list($domain, $pageUrl)=$siteInfo;
	$url=$originalUrl;
	
	//if it is an localimage
	if(pic_isExternalLink($domain, $pageUrl, $url))
	//dont rewrite external links
		return $originalUrl;
	
	
	//make sure we dont have /../ then we know something is wrong.
	if(strstr($url,"/../")!==false)
		return $originalUrl;
				
		
	return pic_addPrefixOnUrl($url);

}

add_filter("wp_get_attachment_url","pic_getNewUrl",1001);


/**
 * rewrites an image URL to a new URL that cahce the images. If you develop a theme and want to print
 * design images, run the images url through this function like this:
 * $url="/wp-content/uploads/myPic.jpg";
 * if(function_exists('pic_getNewUrl')) 
 * 		$url=pic_getNewUrl($url);
 * echo "<img src='$url' />";
 * 
 * @param $url, the input must have 'wp-content' in the url
 */
function pic_addPrefixOnUrl($url){
	if(!strpos($url,$_SERVER['HTTP_HOST'])!==false)
		$url=WP_PLUGIN_URL."/php-image-cache/image.php?path=".$url;
	return $url;
}


/**
 * Answers true if the link goes to an other domain than $url.
 * If it is a internal link, the $link will be rewritten to a absolute url if neccecery.
 *
 * @param $domain with http:// prefix
 * @param $currentUrl
 * @param $link BY REFERENCE!!
 * @return boolen
 */
function pic_isExternalLink($domain,$currentUrl,&$link){
	//if domain is included
	if(preg_match("/^(http:\/\/|https:\/\/)/",$link)){
		
		//if link includes this domain
		if(substr($link,0,strlen($domain))!=$domain){
			//external
			return true;
		}
		else{
			//internal
			
			//remove domain
			$link=str_replace($domain,'',$link);
			
			return false;
		}
	}
	else{//internal
		//we must modify $link
		$currentUrl=rtrim($currentUrl,"/");

		if(substr($link,0,1)=="/"){//  /abs/interlal
			//great every thing is fine
			
		}
		else if(substr($link,0,2)!="..")//  rel/internal
			$link=$currentUrl."/".$link;
		else{// ../../very-rel/internal
			$max=substr_count($currentUrl,"/",8);//offset removes http://
			while(substr($link,0,3)=="../" && $max-->0){
				//remove dots
				$link=substr($link,3);
				//remove a folder
				$currentUrl=substr($currentUrl,0,strrpos($currentUrl,"/"));
			}
			$link=$currentUrl."/".$link;
			
			//remove domain
			$link=str_replace($domain,'',$link);
		}
		return false;
	}
}

/**
 * Returns current domain and the current page in a array
 */
function pic_getDomainAndPage(){
	$prefix = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
		$prefix .= "s";
	}
	$prefix .= "://";
	$pageUrl=$prefix.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$domain=$prefix.$_SERVER["SERVER_NAME"];//no tailoring slash
	
	return array($domain, $pageUrl);
}

