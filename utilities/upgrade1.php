<?php

/*
	[DISCUZ!] upgrade1.php - upgrade 3.0RC1 to Discuz! 1.0/1.01
	This is NOT a freeware, use is subject to license terms

	Version: 1.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2002/10/6 17:00
*/



header("Content-Type: text/html; charset=gb2312");
define("IN_CDB", TRUE);
require "./config.php";
require "./functions.php";
require "./lib/$database.php";

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($HTTP_POST_VARS["action"]) ? $HTTP_POST_VARS["action"] : $HTTP_GET_VARS["action"];

$upgrade = <<<EOT

UPDATE cdb_settings SET version='1.0';
ALTER TABLE cdb_themes DROP top;
UPDATE cdb_themes SET headercolor='header_bg.gif' WHERE themename='标准界面';
UPDATE cdb_members SET status='正式会员' WHERE status='游客';

ALTER TABLE cdb_settings ADD postcredits1 tinyint(3) NOT NULL AFTER smcols, ADD digistcredits1 tinyint(3) NOT NULL AFTER postcredits1;
UPDATE cdb_settings SET postcredits1=postcredits, digistcredits1=digistcredits;
ALTER TABLE cdb_settings DROP postcredits, DROP digistcredits, CHANGE postcredits1 postcredits tinyint(3) NOT NULL, CHANGE digistcredits1 digistcredits tinyint(3) NOT NULL;
ALTER TABLE cdb_settings ADD karmactrl smallint(6) UNSIGNED NOT NULL AFTER floodctrl;
UPDATE cdb_settings SET karmactrl='600';


ALTER TABLE cdb_posts DROP INDEX tid, ADD INDEX tid (tid, dateline);
ALTER TABLE cdb_threads DROP hide;

ALTER TABLE cdb_attachments CHANGE filename filename VARCHAR(255) NOT NULL;
ALTER TABLE cdb_attachments CHANGE attachment attachment VARCHAR(255) NOT NULL;

EOT;


if(!$action) {
	echo"本程序用于升级 CDB 3.0 RC1 到 Discuz! 1.0,请确认之前已经顺利安装 CDB 3.0 RC1<br><br><br>";
	echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 2.0.0 RC1 的全部文件和目录</font></b><br><br>";
	echo"<b><font color=\"red\">本程序只能从 CDB 3.0 RC1 升级到 Discuz! 1.0,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 上传 Discuz! 1.0 版的全部文件和目录,覆盖服务器上的 CDB 3.0 RC1;<br>2. 上传本程序($PHP_SELF)到 Discuz! 目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 在 Discuz! 系统设置中恢复默认模板,更新缓存,升级完成.<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$tables = array('attachments', 'announcements', 'banned', 'caches', 'favorites', 'forumlinks', 'forums', 'members', 'memo',
		'news', 'posts', 'searchindex', 'sessions', 'settings', 'smilies', 'stats', 'subscriptions', 'templates', 'themes',
		'threads', 'u2u', 'usergroups', 'words', 'buddys');
	foreach($tables as $tablename) {
		${"table_".$tablename} = $tablepre.$tablename;
	}
	unset($tablename);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	runquery($upgrade);
	echo "升级完成,请恢复默认模板,更新缓存以便完成升级.";

}

function runquery($query) {
	global $tablepre;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql != "" && $sql[0] != "#") {
			mysql_query(str_replace("cdb_", $tablepre, $sql)) or die(mysql_error());
		}
	}
}

?>