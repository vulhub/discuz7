<?php

/*
	[DISCUZ!] utilities/restore.php - Discuz! database importing utilities
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.1
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/9/10 10:05
*/


error_reporting(7);
@set_time_limit(1000);
ob_implicit_flush();

define('DISCUZ_ROOT', './');
define('IN_DISCUZ', TRUE);

require './config.inc.php';
require './include/db_'.$database.'.class.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
$db->select_db($dbname);

if(!get_cfg_var('register_globals')) {
	@extract($HTTP_GET_VARS);
}

$sqldump = '';

echo "<HTML><HEAD></HEAD><BODY STYLE=\"font-family: Tahoma, Verdana, 宋体; font-size: 11px\"><b>数据库恢复实用工具 RESTORE for Discuz!</b><br><br>".
	"本程序用于恢复用 Discuz! 备份的数据文件,当 Discuz! 出现问题无法运行和恢复数据,<br>".
	"而 phpMyAdmin 又不能恢复大文件时,可尝试使用此工具.<br><br>".
	"版权所有(C) 康盛创想(北京)科技有限公司, 2002, 2003, 2004<br><br>".
	"注意:<br><br>".
	"<b>本程序需放于 Discuz! 目录中才能使用<br><br>".
	"只能恢复存放在服务器(远程或本地)上的数据文件,如果您的数据不在服务器上,请用 FTP 上传<br><br>".
	"数据文件必须为 Discuz! 导出格式,并设置相应属性使 PHP 能够读取<br><br>".
	"请尽量选择服务器空闲时段操作,以避免超时.如程序长久(超过 10 分钟)不反应,请刷新</b><br><br>";

if($file) {
	if(strtolower(substr($file, 0, 7)) == "http://") {
		echo "从远程数据库恢复数据 - 读取远程数据:<br><br>";
		echo "从远程服务器读取文件 ... ";

		$sqldump = @fread($fp, 99999999);
		@fclose($fp);
		if($sqldump) {
			echo "成功<br><br>";
		} elseif(!$multivol) {
			exit("失败<br><br><b>无法恢复数据</b>");
		}
	} else {
		echo "从本地恢复数据 - 检查数据文件:<br><br>";
		if(file_exists($file)) {
			echo "数据文件 $file 存在检查 ... 成功<br><br>";
		} elseif(!$multivol) {
			exit("数据文件 $file 存在检查 ... 失败<br><br><br><b>无法恢复数据</b>");
		}

		if(is_readable($file)) {
			echo "数据文件 $file 可读检查 ... 成功<br><br>";
			@$fp = fopen($file, "r");
			@flock($fp, 3);
			$sqldump = @fread($fp, filesize($file));
			@fclose($fp);
			echo "从本地读取数据 ... 成功<br><br>";
		} elseif(!$multivol) {
			exit("数据文件 $file 可读检查 ... 失败<br><br><br><b>无法恢复数据</b>");
		}
	}

	if($multivol && !$sqldump) {
		exit("分卷备份范围检查 ... 成功<br><br><b>恭喜您,数据已经全部成功恢复!安全起见,请务必删除本程序.</b>");
	}

	echo "数据文件 $file 格式检查 ... ";
	@list(,,,$method, $volume) = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", preg_replace("/^(.+)/", "\\1", substr($sqldump, 0, 256)))));
	if($method == 'multivol' && is_numeric($volume)) {
		echo "成功<br><br>";
	} else {
		exit("失败<br><br><b>数据非 Discuz! 分卷备份格式,无法恢复</b>");
	}

	if($onlysave == "yes") {
		echo "将数据文件保存到本地服务器 ... ";
		$filename = "./forumdata".strrchr($file, "/");
		@$filehandle = fopen($filename, "w");
		@flock($filehandle, 3);
		if(@fwrite($filehandle, $sqldump)) {
			@fclose($filehandle);
			echo "成功<br><br>";
		} else {
			@fclose($filehandle);
			die("失败<br><br><b>无法保存数据</b>");
		}
		echo "成功<br><br><b>恭喜您,数据已经成功保存到本地服务器 <a href=\"".strstr($filename, "/")."\">$filename</a>.安全起见,请务必删除本程序.</b>";
	} else {
		$sqlquery = splitsql($sqldump);
		echo "拆分操作语句 ... 成功<br><br>";
		unset($sqldump);

		echo "正在恢复数据,请等待 ... <br><br>";
		foreach($sqlquery as $sql) {
			if(trim($sql)) {
				$db->query($sql);
				//echo "$sql<br>";
			}
		}

		$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);

		echo "数据文件 <b>$volume#</b> 恢复成功,现在将自动导入其他分卷备份数据.<br><b>请勿关闭浏览器或中断本程序运行</b>";
		redirect("restore.php?file=$nextfile&multivol=yes");
	}
} else {
	echo "参数:<br><br>".
		"<b>file=forumdata/dz_xxx.sql</b> (本地恢复: forumdata/dz_xxx.sql 是本地服务器上数据文件的路径和名字)<br>".
		"<b>file=http://your.com/discuz/forumdata/dz_xxx.sql</b> (远程恢复: http://... 是远程数据文件的路径和名字)<br><br>".
		"<b>onlysave=yes</b> (只将数据文件转存到本地服务器,而不恢复到数据库)<br><br>".
		"用法举例:<br><br>".
		"<b><a href=\"restore.php?file=forumdata/discuz.sql\">restore.php?file=forumdata/discuz.sql</a></b> (恢复 forumdata 目录下的 discuz.sql 数据文件)<br>".
		"<b><a href=\"restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql\">restore.php?file=http://your.com/discuz/forumdata/discuz_xxx.sql</a></b> (恢复 your.com 上的相应数据文件)<br>".
		"<b><a href=\"restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql&onlysave=yes\">restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql&onlysave=yes</a></b> (转存 your.com 上的相应数据文件到本地服务器)<br>".
		"</BODY></HTML>";
}

function splitsql($sql) {
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}			
	return($ret);
}

function redirect($url) {
	echo "<script>";
	echo "function redirect() {window.location.replace('$url');}\n";
	echo "setTimeout('redirect();', 2000);\n";
	echo "</script>";
	echo "<br><br><a href=\"$url\">如果您的浏览器没有自动跳转，请点击这里</a>";
}

?>
