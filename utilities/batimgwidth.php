<?php

/*
	[DISCUZ!] utilities/thumbattach.php
	This is NOT a freeware, use is subject to license terms

	Last Modified: 2006-12-28 15:05
*/

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/ftp.func.php';

//$db->query("ALTER TABLE cdb_attachments ADD `width` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'");

@set_time_limit(0);

$act = isset($_GET['act']) && $_GET['act'] == 'getwidth' ? 'getwidth' : 'ready';

$query = $db->query("SELECT COUNT(*) FROM {$tablepre}attachments WHERE isimage IN ('1', '-1') AND width='0'");
$num = $db->result($query, 0);
if(empty($num)) {
	echo '升级完成，请删除改程序文件';exit;
}

$total = isset($_GET['total']) ? intval($_GET['total']) : $num;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$repeat = isset($_GET['repeat']) ? intval($_GET['repeat']) : 0;
$limit = 100;

if($act == 'ready') {
	echo $num.'个图片附件需要升级<br /><a href="?act=getwidth">点击开始</a>';exit;
} else {
	$ftp = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='ftp'"));
	$attachdir = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='attachdir'");
	$attachurl = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='attachurl'");
	$query = $db->query("SELECT attachment,aid,remote FROM {$tablepre}attachments WHERE isimage IN ('1', '-1') AND width='0' ORDER BY dateline DESC LIMIT $start, $limit");
	$count = 0;
	if($db->num_rows($query)) {
		while($attachments = $db->fetch_array($query)) {
			$img_w = 0;
			if($attachments['remote'] || $ftp['attachurl'] == $attachurl) {
				if($ftp['connid'] || ($ftp['connid'] = dftp_connect($ftp['host'], $ftp['username'], authcode($ftp['password'], 'DECODE', md5($authkey)), $ftp['attachdir'], $ftp['port'], $ftp['ssl']))) {
					$local_file = DISCUZ_ROOT.'./forumdata/tmp_batimgwidth';
					if(dftp_get($ftp['connid'], $local_file, $attachments['attachment'], FTP_BINARY)) {
						$attachinfo	= getimagesize($local_file);
						$img_w		= $attachinfo[0];
						@unlink($local_file);
					} else {
						$target = $ftp['attachurl'].'/'.$attachments['attachment'];
						$attachinfo	= getimagesize($target);
						$img_w		= $attachinfo[0];
					}
				} else {
					$target = $ftp['attachurl'].'/'.$attachments['attachment'];
					$attachinfo	= getimagesize($target);
					$img_w		= $attachinfo[0];
				}
			} else {
				$target		= $attachdir.'/'.$attachments['attachment'];
				$attachinfo	= getimagesize($target);
				$img_w		= $attachinfo[0];
			}
			if($img_w) {
				$db->query("UPDATE {$tablepre}attachments SET width='$img_w' WHERE aid='$attachments[aid]'", 'UNBUFFERED');
				$count++;
			} else {
				writelog('imgwidthlog', implode("\t", $attachments));
			}
		}
	} else {
		$repeat++;
		$yearmonth = gmdate('Ym', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
		$logdir = DISCUZ_ROOT.'./forumdata/logs/';
		$logfile = $logdir.$yearmonth.'_imgwidthlog.php';
		if($repeat > 5) {
			echo "升级完成，请删除改程序文件<br />错误记录请查看 $logfile 文件";exit;
		}
		if(file_exists($logfile)) @unlink($logfile);
		redirect("处理完毕，再次请求，将刚才遗漏的处理完", "batimgwidth.php?act=getwidth&repeat=$repeat");
	}
}
$end = $start + $limit;
redirect("此次成功处理 $count 个图片。图片附件已处理 $start / $total ...", "batimgwidth.php?act=getwidth&start=$end&total=$total&repeat=$repeat");

function redirect($message, $url_forward) {
echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>$message</title>
<script type="text/javascript">
	function redirect(url) {
		window.location=url;
	}	
</script>
</head>
<body>
EOD;
	$message .= "<br /><br /><br /><a href=\"$url_forward\">浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里</a>";
	$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script></body></html>";
	die($message);
}

?>