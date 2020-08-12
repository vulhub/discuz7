<?php

// Upgrade Discuz! Board from 5.0.0RC1 to 5.0.0RC2

@set_time_limit(1000);
define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("请先上传所有新版本的程序文件后再运行本升级程序");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = $_GET['start'];

if(!$action) {
	echo"本程序用于升级 Discuz! 5.0.0RC1 到 Discuz! 5.0.0RC2,请确认之前已经顺利安装 Discuz! 5.0.0RC1<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 5.0.0RC1 升级到 5.0.0RC2,运行之前,请确认已经上传 5.0.0RC2 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 5.0.0RC2 版的全部文件和目录,覆盖服务器上的 5.0.0RC1<br>2. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	$query = $db->query("SELECT styleid FROM {$tablepre}styles ORDER BY styleid DESC LIMIT 1");
	$styleid = intval($db->result($query, 0));
	$newstyleid = $styleid + 1;

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('indexname', 'index.php')");
	$db->query("REPLACE INTO {$tablepre}styles (styleid, name, available, templateid) VALUES ('$newstyleid', 'Discuz! 5 Default', '1', '1')");
	$db->query("REPLACE INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$newstyleid', 'lighttext', '#999999'),
			('$newstyleid', 'bgcolor', '#FFFFFF'),
			('$newstyleid', 'altbg1', '#F9FAFF'),
			('$newstyleid', 'altbg2', '#FFFFFF'),
			('$newstyleid', 'link', '#154BA0'),
			('$newstyleid', 'bordercolor', '#7AC4EA'),
			('$newstyleid', 'headercolor', 'header_bg.gif'),
			('$newstyleid', 'headertext', '#333333'),
			('$newstyleid', 'catcolor', '#F1F1F1'),
			('$newstyleid', 'tabletext', '#333333'),
			('$newstyleid', 'text', '#333333'),
			('$newstyleid', 'borderwidth', '1'),
			('$newstyleid', 'tablewidth', '98%'),
			('$newstyleid', 'tablespace', '4'),
			('$newstyleid', 'font', 'Tahoma, Verdana'),
			('$newstyleid', 'fontsize', '12px'),
			('$newstyleid', 'msgfontsize', '12px'),
			('$newstyleid', 'nobold', '0'),
			('$newstyleid', 'boardimg', 'logo.gif'),
			('$newstyleid', 'imgdir', 'images/default'),
			('$newstyleid', 'smdir', 'images/smilies'),
			('$newstyleid', 'cattext', '#339900'),
			('$newstyleid', 'smfontsize', '11px'),
			('$newstyleid', 'smfont', 'Arial, Tahoma'),
			('$newstyleid', 'maintablewidth', '98%'),
			('$newstyleid', 'maintablecolor', '#FFFFFF'),
			('$newstyleid', 'innerborderwidth', '0'),
			('$newstyleid', 'innerbordercolor', '#B6DFF6'),
			('$newstyleid', 'menubg', '#D9EEF9'),
			('$newstyleid', 'bgborder', '#B6DFF6'),
			('$newstyleid', 'inputborder', '#7AC4EA'),
			('$newstyleid', 'mainborder', '#154BA0'),
			('$newstyleid', 'catborder', '#E7E7E7'),
			('$newstyleid', 'headermenu', 'menu_bg.gif')");
	$db->query("UPDATE {$tablepre}members SET styleid = '0'");
	$db->query("UPDATE {$tablepre}settings SET value = '$newstyleid' WHERE variable = 'styleid'");
	$db->query("UPDATE {$tablepre}styles SET available = '0' WHERE styleid != '$newstyleid'");

	echo "恭喜您升级成功,请务必删除本程序.";

}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	$directory->close();
}

function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function runquery($query) {
	global $db, $tablepre;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql != "" && $sql[0] != "#") {
			$db->query(str_replace("cdb_", $tablepre, $sql));
		}
	}
}

?>