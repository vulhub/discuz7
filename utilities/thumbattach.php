<?php

/*
	[DISCUZ!] utilities/thumbattach.php
	This is NOT a freeware, use is subject to license terms

	Last Modified: 2006-12-28 15:05
*/

require_once './include/common.inc.php';

@set_time_limit(0);

echo '缩略图批量生成工具<hr>';

$do = !empty($do) ? $do : '';

if($do == '1') {
	$sqladd = "AND thumb=0";
} elseif($do == '2') {
	$sqladd = '';
} else {
	echo '<a href="?do=1">仅处理缺少缩略图的附件</a><br>';
	echo '<a href="?do=2">处理所有附件</a>';
	exit;
}

$next = !empty($next) ? intval($next) : 0;

$query = $db->query("SELECT * FROM {$tablepre}attachments WHERE isimage IN ('1', '-1') $sqladd LIMIT $next, 10");

if(!$thumbstatus) {
	echo '您的论坛没有开启缩略图功能，请开启后再执行此程序。';
	exit;
}

$thumbcount = !empty($thumbcount) ? $thumbcount : 0;
$imagecount = !empty($imagecount) ? $imagecount : 0;

if($db->num_rows($query)) {

	while($attachments = $db->fetch_array($query)) {

		$target		= 'attachments/'.$attachments['attachment'];
		$attachinfo	= getimagesize($target);
		$img_w		= $attachinfo[0];
		$img_h		= $attachinfo[1];

		$animatedgif = 0;
		if($attachinfo['mime'] == 'image/gif') {
			$fp = fopen($target, 'rb');
			$attachedfile = fread($fp, $attachments['filesize']);
			fclose($fp);
			$animatedgif = strpos($attachedfile, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}

		if(!$animatedgif && ($img_w >= $thumbwidth || $img_h >= $thumbheight)) {

			switch($attachinfo['mime']) {
				case 'image/jpeg':
					$attach_photo = imageCreateFromJPEG($target);
					break;
				case 'image/gif':
					$attach_photo = imageCreateFromGIF($target);
					break;
				case 'image/png':
					$attach_photo = imageCreateFromPNG($target);
					break;
			}

			$x_ratio = $thumbwidth / $img_w;
			$y_ratio = $thumbheight / $img_h;

			if(($x_ratio * $img_h) < $thumbheight) {
				$thumb['height'] = ceil($x_ratio * $img_h);
				$thumb['width'] = $thumbwidth;
			} else {
				$thumb['width'] = ceil($y_ratio * $img_w);
				$thumb['height'] = $thumbheight;
			}

			$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
			imageCopyreSampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $img_w, $img_h);
			imageJPEG($thumb_photo, 'attachments/'.$attachments['attachment'].'.thumb.jpg');
			$db->query("UPDATE {$tablepre}attachments SET thumb=1 WHERE aid='$attachments[aid]'", 'UNBUFFERED');

			$thumbcount++;

		}

		$imagecount++;

	}

	echo "图片附件发现 $imagecount 个，缩略图生成 $thumbcount 个......";

	redirect("?next=".($next+10)."&imagecount=$imagecount&thumbcount=$thumbcount&do=$do");

} else {

	echo "图片附件发现 $imagecount 个，缩略图生成 $thumbcount 个。<br><br><a href=\"?\">重新执行</a>";

}

function redirect($url) {

	echo "<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";
	flush();

}

?>