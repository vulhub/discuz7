<?php

// Upgrade Discuz! Board from 3.0 to 3.1

header("Content-Type: text/html; charset=gb2312");
set_time_limit(1000);
define('IN_DISCUZ', TRUE);

if(file_exists('./config.php')) {
	require "./config.php";
} else {
	require "./config.inc.php";
}
require "./include/db_mysql.php";

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($HTTP_POST_VARS[action]) ? $HTTP_POST_VARS[action] : $HTTP_GET_VARS[action];
$step = $HTTP_GET_VARS[step];
$start = $HTTP_GET_VARS[start];

$upgrade1 = <<<EOT
ALTER TABLE cdb_forums DROP INDEX status, ADD INDEX forum (status, type, displayorder);
ALTER TABLE cdb_profilefields ADD invisible TINYINT(1) NOT NULL AFTER available;
UPDATE cdb_settings SET value='3.1' WHERE variable='version';
ALTER TABLE cdb_polls CHANGE pollopts pollopts MEDIUMTEXT NOT NULL;
ALTER TABLE cdb_threads CHANGE views views MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL, ADD highlight TINYINT(1) NOT NULL AFTER displayorder;
INSERT INTO cdb_settings VALUES ('attachrefcheck', '0');
DROP TABLE IF EXISTS cdb_attachtypes;
CREATE TABLE cdb_attachtypes (
  id smallint(6) UNSIGNED NOT NULL auto_increment,
  extension char(6) NOT NULL default '',
  maxsize int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS cdb_failedlogins;
CREATE TABLE cdb_failedlogins (
  ip char(15) NOT NULL,
  count tinyint(1) UNSIGNED NOT NULL,
  lastupdate int(10) UNSIGNED NOT NULL
);
ALTER TABLE cdb_usergroups ADD color CHAR(7) NOT NULL AFTER stars;

ALTER TABLE cdb_threads CHANGE views views MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL, CHANGE fid fid SMALLINT(6) UNSIGNED DEFAULT '0' NOT NULL;
DROP TABLE IF EXISTS cdb_ranks;
CREATE TABLE cdb_ranks (
  rankid smallint(6) unsigned NOT NULL auto_increment,
  ranktitle varchar(30) NOT NULL,
  postshigher smallint(6) unsigned NOT NULL default '0',
  stars tinyint(3) NOT NULL default '0',
  color varchar(7) NOT NULL,
  PRIMARY KEY  (rankid)
);
INSERT INTO cdb_ranks VALUES (1, 'Beginner', 0, 1, '');
INSERT INTO cdb_ranks VALUES (2, 'Poster', 50, 2, '');
INSERT INTO cdb_ranks VALUES (3, 'Cool Poster', 300, 5, '');
INSERT INTO cdb_ranks VALUES (4, 'Writer', 1000, 4, '');
INSERT INTO cdb_ranks VALUES (5, 'Excellent Writer', 3000, 5, '');
INSERT INTO cdb_settings VALUES ('regctrl', '0');
INSERT INTO cdb_settings VALUES ('userstatusby', 1);
INSERT INTO cdb_settings VALUES ('newbiespan', 0);
DELETE FROM cdb_settings WHERE variable='accessmasks';

ALTER TABLE cdb_members ADD lastip varchar(15) NOT NULL AFTER regdate, ADD accessmasks tinyint(1) NOT NULL AFTER newpm, ADD secques varchar(8) NOT NULL AFTER password, CHANGE password password varchar(32) NOT NULL, DROP pwdrecover, DROP pwdrcvtime, ADD identifying varchar(20) NOT NULL AFTER accessmasks;;

EOT;

if(!$action) {
	echo"本程序用于升级 Discuz! 3.0 到 Discuz! 3.1,请确认之前已经顺利安装 Discuz! 3.0<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 3.0 升级到 3.1,运行之前,请确认已经上传 3.1 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 3.1 版的全部文件和目录,覆盖服务器上的 3.0<br>2. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {
	$tables = array('access', 'attachments', 'announcements', 'banned', 'caches', 'favorites', 'forumlinks', 'forums', 'karmalog', 'members', 'memo',
	'news', 'polls', 'posts', 'searchindex', 'sessions', 'settings', 'styles', 'smilies', 'stats', 'subscriptions', 'templates', 'themes',
	'threads', 'pm', 'pms', 'usergroups', 'words', 'buddys', 'stylevars');
	foreach($tables as $tablename) {
		${"table_".$tablename} = $tablepre.$tablename;
	}
	unset($tablename);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		$query = $db->query("SELECT value FROM $table_settings WHERE variable='version'");
		if(($db->result($query, 0)) != '3.0') {
			exit('您当前数据库数据版本不是3.0,无法升级');
		}
		runquery($upgrade1);

		$query = $db->query("SELECT styleid FROM $table_styles");
		while($style = $db->fetch_array($query)) {
			$db->query("INSERT INTO $table_stylevars (styleid, variable, substitute) VALUES ('$style[styleid]', 'maintablespace', '8')");
			$db->query("INSERT INTO $table_stylevars (styleid, variable, substitute) VALUES ('$style[styleid]', 'maintablewidth', '98%')");
			$db->query("INSERT INTO $table_stylevars (styleid, variable, substitute) VALUES ('$style[styleid]', 'maintablecolor', '#FFFFFF')");
		}

		$uids = '0';
		$query = $db->query("SELECT DISTINCT uid FROM $table_access");
		while($a = $db->fetch_array($query)) {
			$uids .= ",$a[uid]";
		}
		$db->query("UPDATE $table_members SET accessmasks='1' WHERE uid IN ($uids)");

		loginit('karmalog');
		loginit('illegallog');
		loginit('modslog');
		loginit('cplog');
		@unlink('./forumdata/cache/cache_settings.php');

		echo "第 1 步升级成功<br><br>核心升级已经完成,您是否对帖子数据转换处理?<br><br>继续转换需耗费一定时间,但用户阅读老帖子的资源消耗会减少10%~30%;<br>如果选择不处理,也不会对论坛使用产生任何影响<br><br>";
		echo "<a href=$PHP_SELF?action=upgrade&step=2>继续转换帖子数据</a><br>注意: 如果您选择转换帖子数据,这个过程不是必须的,<br>即使中途退出也不会对数据造成影响<br><br><br>";
		echo "<a href=$PHP_SELF?action=upgrade&step=3>不转换帖子数据</a><br>";

	} elseif($step == 2) {
		
		$many = 5000;
		$start = intval($start);
		$end = $start + $many;

		$smilies = array();
		$query = $db->query("SELECT code FROM $table_smilies WHERE type='smiley' AND code<>''");
		while($smiley = $db->fetch_array($query)) {
			$smilies[]= preg_quote($smiley['code'], '/');
		}

		echo "正在进行升级第 2 步: 转换帖子 $start 到 $end<br><br>";
		$query = $db->query("SELECT pid, message, smileyoff, bbcodeoff FROM $table_posts LIMIT $start, $many");
		if($db->num_rows($query)) {
			while($post = $db->fetch_array($query)) {
				$bbcodeoff = checkbbcodes($post['message'], $post['bbcodeoff']);
				$smileyoff = checksmilies($post['message'], $post['smileyoff']);
				if($bbcodeoff != $post['bbcodeoff'] || $smileyoff != $post['smileyoff']) {
					$db->query("UPDATE $table_posts SET bbcodeoff=$bbcodeoff, smileyoff=$smileyoff WHERE pid=$post[pid]");
				}
			}
			redirect("$PHP_SELF?action=upgrade&step=2&start=$end");
		} else {
			redirect("$PHP_SELF?action=upgrade&step=3");
		}
	} elseif($step == 3) {
		echo "恭喜您升级成功,请务必删除本程序. 进行以下两步操作后才能最后完成:<br>1. 在系统设置中 重建论坛帖数 2. 更新缓存";
	}
}

function loginit($log) {
	global $lang;

	$fp = @fopen('./forumdata/'.$log.'.php');
	@fwrite($fp, "<?PHP exit(\"Access Denied\"); ?>\n");
	@fclose($fp);
}

function checkbbcodes($message, $bbcodeoff) {
	return !$bbcodeoff && !preg_match("/\[.+\].*\[\/.+\]/s", $message) ? -1 : $bbcodeoff;
}

function checksmilies($message, $smileyoff) {
	global $smilies;
	return !$smileyoff && !preg_match('/'.implode('|', $smilies).'/', $message) ? -1 : $smileyoff;
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

function redirect($url) {

	echo"<script>";
	echo"function redirect() {window.location.replace('$url');}\n";
	echo"setTimeout('redirect();', 2000);\n";
	echo"</script>";
	echo"<br><br><a href=\"$url\">如果您的浏览器没有自动跳转，请点击这里</a>";

}

?>