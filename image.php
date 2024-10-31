<?php

$this_path=dirname(__FILE__);
$image_path=substr($this_path,0,strpos($this_path,"wp-content")).$_GET['path'];

if(!file_exists($image_path) || strstr($image_path,"/../")!==false){
	//throw 404
	die("Image not found");
	//this is a security issue. This make sure nobody can do like this: 
	//$ wget 'http://[victim]/wp-content/plugins/php-image-cache/image.php?path=/../../../../../../../etc/passwd'
}


$fileSize = filesize($image_path);

$mime_type=getMimeType($image_path);
if($mime_type==null)
	die("Unknown extension");

$expires = 2592000; // 60*60*24*30;
// send headers then display image
header("Content-Type: " . $mime_type);
//header("Accept-Ranges: bytes");
header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime($image_path)) . " GMT");
header("Content-Length: " . filesize($image_path));
header("Cache-Control: max-age=$expires");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . "GMT");

readfile($image_path);
exit;

function getMimeType($image_path){
	// set defaults
	$mime_type = null;

	$ext = strtolower(substr($image_path,strrpos($image_path,".")+1));
	// mime types
	$types = array(
 			'jpg'  => 'image/jpeg',
 			'jpeg' => 'image/jpeg',
 			'png'  => 'image/png',
 			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/x-icon',
			'mac'  => 'image/x-macpaint',
			'pnt'  => 'image/x-macpaint',
			'pntg' => 'image/x-macpaint',
			'pct'  => 'image/pict',
			'pic'  => 'image/pict',
			'pict' => 'image/pict',
			'svg'  => 'image/svg+xml',
			'tif'  => 'image/tiff',
			'tiff' => 'image/tiff',
			'xbm'  => 'image/x-xbitmap',
			'xwd'  => 'image/x-xwindowdump'
 			);//add some more filetypes if you need it..
	
 			
 	if(strlen($ext) && strlen($types[$ext])) {
 		$mime_type = $types[$ext];
 	}
 	
 	return $mime_type;
}

