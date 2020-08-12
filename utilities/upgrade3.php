<?php

// Upgrade Discuz! Board from 2.0 to 3.0

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
UPDATE cdb_settings SET totalmembers=0, maxavatarsize=maxavatarsize*100;
ALTER TABLE cdb_posts ADD INDEX (author);
ALTER TABLE cdb_pm ADD INDEX (msgfrom);
ALTER TABLE cdb_threads ADD INDEX (author), DROP INDEX lastpost;
EOT;

$upgrade2 = <<<EOT
ALTER TABLE cdb_posts ADD INDEX (authorid), ADD INDEX dotfolder (tid, authorid), DROP INDEX author, CHANGE subject subject VARCHAR(80) NOT NULL;
ALTER TABLE cdb_pm DROP INDEX msgfrom;
ALTER TABLE cdb_favorites ADD uid mediumint(8) UNSIGNED NOT NULL FIRST;
UPDATE cdb_favorites SET uid=uid1;
ALTER TABLE cdb_favorites DROP uid1;
DROP TABLE IF EXISTS cdb_memo, cdb_poll;
ALTER TABLE cdb_pm ADD msgtoid mediumint(8) UNSIGNED NOT NULL AFTER msgfromid;
UPDATE cdb_pm SET msgtoid=msgtoid1;
ALTER TABLE cdb_pm DROP msgtoid1;
ALTER TABLE cdb_pm ADD INDEX (msgtoid);
ALTER TABLE cdb_members ADD lastpost int(10) UNSIGNED NOT NULL AFTER lastvisit, ADD lastactivity int(10) UNSIGNED NOT NULL AFTER lastvisit, DROP charset;
UPDATE cdb_members SET lastactivity=lastvisit-3600, lastpost=lastvisit;
UPDATE cdb_posts SET author='' WHERE author='Guest';

DROP TABLE IF EXISTS cdb_sessions;
CREATE TABLE cdb_sessions (
  sid char(6) binary NOT NULL default '',
  ip1 tinyint(3) UNSIGNED NOT NULL default '0',
  ip2 tinyint(3) UNSIGNED NOT NULL default '0',
  ip3 tinyint(3) UNSIGNED NOT NULL default '0',
  ip4 tinyint(3) UNSIGNED NOT NULL default '0',
  uid mediumint(8) UNSIGNED NOT NULL default '0',
  username char(15) NOT NULL default '',
  groupid smallint(6) unsigned NOT NULL default '0',
  styleid smallint(6) unsigned NOT NULL default '0',
  invisible tinyint(1) NOT NULL default '0',
  action tinyint(1) unsigned NOT NULL default '0',
  lastactivity int(10) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  UNIQUE sid (sid)
) TYPE=HEAP MAX_ROWS=2000;


DROP TABLE IF EXISTS cdb_adminsessions;
CREATE TABLE cdb_adminsessions (
  sid char(6) binary NOT NULL default '',
  dateline int(10) UNSIGNED NOT NULL default '0',
  errorlog tinyint(1) NOT NULL default '0'
);

DROP TABLE IF EXISTS cdb_onlinelist;
CREATE TABLE cdb_onlinelist (
  groupid smallint(6) unsigned NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(30) NOT NULL default '',
  url varchar(30) NOT NULL default ''
);
INSERT INTO cdb_onlinelist VALUES (1, 1, 'Administrator', 'online_admin.gif');
INSERT INTO cdb_onlinelist VALUES (2, 2, 'Super Moderator', 'online_supermod.gif');
INSERT INTO cdb_onlinelist VALUES (3, 3, 'Moderator', 'online_moderator.gif');
INSERT INTO cdb_onlinelist VALUES (0, 4, 'Member', 'online_member.gif');

ALTER TABLE cdb_members ADD groupid smallint(6) UNSIGNED NOT NULL AFTER status;
ALTER TABLE cdb_members ADD adminid tinyint(1) NOT NULL AFTER status;
UPDATE cdb_members SET groupid='3', adminid='3' WHERE status='Moderator';
UPDATE cdb_members SET groupid='2', adminid='2' WHERE status='SuperMod';
UPDATE cdb_members SET groupid='1', adminid='1' WHERE status='Admin';
UPDATE cdb_members SET groupid='4', adminid='-1' WHERE status='Banned';
UPDATE cdb_members SET groupid='5', adminid='-1' WHERE status='PostBanned';
UPDATE cdb_members SET groupid='8', adminid='-1' WHERE status='Inactive';
DELETE FROM cdb_searchindex;

DROP TABLE IF EXISTS cdb_admingroups;
CREATE TABLE cdb_admingroups (
  admingid tinyint(1) unsigned NOT NULL default '0',
  alloweditpost tinyint(1) NOT NULL default '0',
  alloweditpoll tinyint(1) NOT NULL default '0',
  allowdelpost tinyint(1) NOT NULL default '0',
  allowmassprune tinyint(1) NOT NULL default '0',
  allowcensorword tinyint(1) NOT NULL default '0',
  allowviewip tinyint(1) NOT NULL default '0',
  allowbanip tinyint(1) NOT NULL default '0',
  allowedituser tinyint(1) NOT NULL default '0',
  allowbanuser tinyint(1) NOT NULL default '0',
  allowpostannounce tinyint(1) NOT NULL default '0',
  allowviewlog tinyint(1) NOT NULL default '0',
  disablepostctrl tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (admingid)
);

INSERT INTO cdb_admingroups VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO cdb_admingroups VALUES (2, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO cdb_admingroups VALUES (3, 1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1);

DROP TABLE IF EXISTS cdb_bbcodes;
CREATE TABLE cdb_bbcodes (
  id mediumint(8) UNSIGNED NOT NULL auto_increment,
  available tinyint(1) NOT NULL,
  tag varchar(100) NOT NULL default '',
  replacement text NOT NULL,
  example varchar(255) NOT NULL default '',
  explanation text NOT NULL,
  params tinyint(1) UNSIGNED NOT NULL default '1',
  nest tinyint(3) UNSIGNED NOT NULL default '1',
  PRIMARY KEY  (id)
);

INSERT INTO cdb_bbcodes VALUES (1, 0, 'fly', '<marquee width="90%" behavior="alternate" scrollamount="3">{1}</marquee>', '[fly]This is sample text[/fly]', 'Make text move horizontal, the same effect as html tag <marquee>. NOTE: Only Internet Explorer supports this feature', 1, 1);
INSERT INTO cdb_bbcodes VALUES (2, 0, 'iframe', '<iframe src="{1}" frameborder="0" allowtransparency="true" scrolling="yes" width="97%" height="480"></iframe>', '[iframe]http://discuz.net[/iframe]', 'Embed another web site in your post page', 1, 1);
INSERT INTO cdb_bbcodes VALUES (3, 0, 'wmv', '<object classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" class="OBJECT" id="MediaPlayer" width="500" height="350" >\r\n<param name="ShowStatusBar" value="-1">\r\n<param name="Filename" value="{1}">\r\n<embed type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" flename="wmv" src="{1}" width="500" height="350">\r\n</embed></object>', '[wmv]mms://your.com/example.wmv[/wmv]', 'Embed Windows media file in thread page', 1, 1);
INSERT INTO cdb_bbcodes VALUES (4, 0, 'rm', '<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="500" height="300" id="Player" viewastext>\r\n<param name="_ExtentX" value="12726">\r\n<param name="_ExtentY" value="8520">\r\n<param name="AUTOSTART" value="0">\r\n<param name="SHUFFLE" value="0">\r\n<param name="PREFETCH" value="0">\r\n<param name="NOLABELS" value="0">\r\n<param name="CONTROLS" value="ImageWindow">\r\n<param name="CONSOLE" value="_master">\r\n<param name="LOOP" value="0">\r\n<param name="NUMLOOP" value="0">\r\n<param name="CENTER" value="0">\r\n<param name="MAINTAINASPECT" value="{1}">\r\n<param name="BACKGROUNDCOLOR" value="#000000">\r\n</object><br><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="500" height="50" id="Player" viewastext>\r\n<param name="_ExtentX" value="18256">\r\n<param name="_ExtentY" value="794">\r\n<param name="AUTOSTART" value="-1">\r\n<param name="SHUFFLE" value="0">\r\n<param name="PREFETCH" value="0">\r\n<param name="NOLABELS" value="0">\r\n<param name="CONTROLS" value="controlpanel">\r\n<param name="CONSOLE" value="_master">\r\n<param name="LOOP" value="0">\r\n<param name="NUMLOOP" value="0">\r\n<param name="CENTER" value="0">\r\n<param name="MAINTAINASPECT" value="0">\r\n<param name="BACKGROUNDCOLOR" value="#000000">\r\n<param name="SRC" value="{1}"></object>', '[rm]rtsp://your.com/example.rm[/rm]', 'Embed Real Movie in thread page', 1, 1);

DROP TABLE IF EXISTS cdb_profilefields;
CREATE TABLE cdb_profilefields (
  fieldid smallint(6) UNSIGNED NOT NULL auto_increment,
  available tinyint(1) NOT NULL,
  title varchar(50) NOT NULL,
  description varchar(255) NOT NULL,
  size tinyint(3) UNSIGNED NOT NULL,
  displayorder smallint(6) NOT NULL,
  required tinyint(1) NOT NULL,
  showinthread tinyint(1) NOT NULL,
  selective tinyint(1) NOT NULL,
  choices text NOT NULL,
  PRIMARY KEY  (fieldid)
);

DROP TABLE IF EXISTS cdb_access;
CREATE TABLE cdb_access (
  uid mediumint(8) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  allowview tinyint(1) NOT NULL default '0',
  allowpost tinyint(1) NOT NULL default '0',
  allowreply tinyint(1) NOT NULL default '0',
  allowgetattach tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid, fid)
);

DROP TABLE IF EXISTS cdb_pms;
ALTER TABLE cdb_pm RENAME cdb_pms;
ALTER TABLE cdb_pms ADD INDEX (msgfromid);
UPDATE cdb_threads SET lastposter='' WHERE lastposter='Guest';
UPDATE cdb_settings SET version='3.0';
UPDATE cdb_forums SET viewperm='', postperm='', getattachperm='', postattachperm='';
ALTER TABLE cdb_threads CHANGE topped displayorder tinyint(1) NOT NULL, ADD iconid smallint(6) UNSIGNED NOT NULL AFTER icon, ADD INDEX displayorder (fid, displayorder, lastpost);
ALTER TABLE cdb_banned ADD expiration int(10) UNSIGNED NOT NULL;
EOT;

$upgrade3 = <<<EOT
ALTER TABLE cdb_threads CHANGE closed closed1 VARCHAR(15) NOT NULL, ADD closed mediumint(8) UNSIGNED NOT NULL;
UPDATE cdb_threads SET closed='1' WHERE closed1='1' OR closed1='yes';
UPDATE cdb_threads SET closed=REPLACE(closed1, 'moved|', '') WHERE closed1 LIKE 'moved|%';
ALTER TABLE cdb_threads DROP closed1;
ALTER TABLE cdb_posts CHANGE pid pid1 int(10) UNSIGNED NOT NULL, DROP PRIMARY KEY, ADD pid int(10) UNSIGNED NOT NULL auto_increment FIRST, ADD PRIMARY KEY (pid);
UPDATE cdb_posts SET pid=pid1;
ALTER TABLE cdb_posts DROP pid1, DROP icon;
ALTER TABLE cdb_settings CHANGE totalmembers nocacheheaders tinyint(1) NOT NULL, DROP lastmember, CHANGE welcommsg welcomemsg TINYINT(1) DEFAULT '0' NOT NULL, ADD minpostsize MEDIUMINT( 8 ) UNSIGNED DEFAULT '0' NOT NULL AFTER memberperpage;
ALTER TABLE cdb_settings CHANGE welcommsgtxt welcomemsgtxt TEXT NOT NULL, ADD dosevasive tinyint(1) NOT NULL;
ALTER TABLE cdb_subscriptions DROP INDEX tid, ADD PRIMARY KEY (uid, tid), CHANGE email email1 varchar(60) NOT NULL, ADD email varchar(60) NOT NULL AFTER tid;;
UPDATE cdb_subscriptions SET email=email1;
ALTER TABLE cdb_subscriptions DROP email1;
ALTER TABLE cdb_smilies CHANGE type type ENUM('smiley', 'icon') DEFAULT 'smiley' NOT NULL;
UPDATE cdb_smilies SET type='icon' WHERE type<>'smiley';
ALTER TABLE cdb_words ADD admin VARCHAR(15) NOT NULL AFTER id;

DROP TABLE IF EXISTS cdb_searchindex;
CREATE TABLE cdb_searchindex (
  searchid int(10) UNSIGNED NOT NULL auto_increment,
  keywords varchar(255) NOT NULL,
  searchstring varchar(255) NOT NULL,
  useip varchar(15) NOT NULL,
  uid mediumint(10) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  expiration int(10) UNSIGNED NOT NULL,
  threads smallint(6) UNSIGNED NOT NULL,
  tids text NOT NULL,
  PRIMARY KEY  (searchid)
);

DROP TABLE IF EXISTS cdb_polls;
CREATE TABLE cdb_polls (
  tid mediumint(8) UNSIGNED NOT NULL,
  pollopts text NOT NULL,
  PRIMARY KEY  (tid)
);


DROP TABLE IF EXISTS settmp;
CREATE TABLE settmp (
  variable varchar(32) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (variable)
);
ALTER TABLE cdb_threads CHANGE author author CHAR(15) NOT NULL, CHANGE subject subject CHAR(80) NOT NULL, CHANGE lastposter lastposter CHAR(15) NOT NULL, CHANGE attachment attachment TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE cdb_members ADD avatarwidth TINYINT( 3 ) UNSIGNED NOT NULL AFTER avatar, ADD avatarheight TINYINT( 3 ) UNSIGNED NOT NULL AFTER avatarwidth, ADD invisible TINYINT( 1 ) NOT NULL AFTER newsletter;
UPDATE cdb_members SET avatarwidth=80, avatarheight=80;
ALTER TABLE cdb_forums DROP postcredits, ADD postcredits TINYINT( 3 ) DEFAULT '-1' NOT NULL AFTER allowimgcode, ADD replycredits TINYINT( 3 ) DEFAULT '-1' NOT NULL AFTER postcredits;
ALTER TABLE cdb_forums CHANGE icon icon CHAR(50) NOT NULL, CHANGE name name CHAR(50) NOT NULL, CHANGE description description CHAR(255) NOT NULL, CHANGE moderator moderator CHAR(255) NOT NULL, CHANGE lastpost lastpost CHAR(100) NOT NULL, CHANGE password password CHAR(12) NOT NULL, CHANGE viewperm viewperm CHAR(100) NOT NULL, CHANGE postperm postperm CHAR(100) NOT NULL, CHANGE getattachperm replyperm CHAR(100) NOT NULL, CHANGE postattachperm getattachperm CHAR(100) NOT NULL, CHANGE threads threads MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE cdb_announcements ADD displayorder TINYINT(3) NOT NULL AFTER subject;
ALTER TABLE cdb_usergroups ADD type ENUM('system', 'special', 'member') NOT NULL default 'member' AFTER groupid, ADD allowinvisible TINYINT(1) NOT NULL AFTER allowkarma;
UPDATE cdb_usergroups SET type='system' WHERE status IN ('Admin','SuperMod','Moderator','Banned','PostBanned','IPBanned','Guest','Inactive');
UPDATE cdb_usergroups SET type='member' WHERE status='Member';
UPDATE cdb_usergroups SET type='special' WHERE specifiedusers<>'';
ALTER TABLE cdb_threads DROP INDEX author, ADD INDEX (digest), CHANGE author author CHAR(15) NOT NULL, CHANGE subject subject CHAR(80) NOT NULL, CHANGE lastposter lastposter CHAR(15) NOT NULL;
ALTER TABLE cdb_buddys ADD INDEX (uid);
ALTER TABLE cdb_members CHANGE location location VARCHAR(30) NOT NULL, CHANGE customstatus customstatus VARCHAR(30) NOT NULL;
ALTER TABLE cdb_attachments CHANGE filename filename CHAR(100) NOT NULL, CHANGE filetype filetype CHAR(50) NOT NULL, CHANGE filesize filesize INT(10) UNSIGNED DEFAULT '0' NOT NULL, CHANGE attachment attachment CHAR(100) NOT NULL;
ALTER TABLE cdb_members ADD extracredit INT(10) NOT NULL AFTER credit, CHANGE credit credit INT(10) NOT NULL;
ALTER TABLE cdb_forums CHANGE lastpost lastpost CHAR(110) NOT NULL;
EOT;

if(!$action) {
	echo"本程序用于升级 Discuz! 2.0 COML 到 Discuz! 3.0,请确认之前已经顺利安装 Discuz! 2.0<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 2.0 COML 升级到 3.0,运行之前,请确认已经上传 3.0 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br><br>本次升级需要耗时很多,并占用大量CPU资源,每10万贴的升级需要5分钟左右,请在服务器空闲时进行.<br>请确保服务器上 PHP 没有在安全模式，或没有脚本运行时间的硬性限制，否则请在本地机器升级后上传数据库。</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 3.0 版的全部文件和目录,覆盖服务器上的 1.01<br>2. 根据安装说明,新建customavatars目录,forumdata/accesslogs目录,两个目录属性777<br>3. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {
	$tables = array('attachments', 'announcements', 'banned', 'caches', 'favorites', 'forumlinks', 'forums', 'karmalog', 'members', 'memo',
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
		$db->query("ALTER TABLE $table_threads DROP INDEX digest", 'SILENT');
		runquery($upgrade1);
	} elseif($step == 2) {
		user2id($table_karmalog);
	} elseif($step == 3) {
		user2id($table_buddys);
	} elseif($step == 4) {
		user2id($table_buddys, 'buddyname', 'buddyid');
	} elseif($step == 5) {
		user2id($table_favorites, 'username', 'uid1');
	} elseif($step == 6) {
		user2id($table_pm, 'msgto', 'msgtoid1');
	} elseif($step == 7) {
		user2id($table_pm, 'msgfrom', 'msgfromid', TRUE);
	} elseif($step == 8) {
		user2id($table_subscriptions);
	} elseif($step == 9) {
		user2id($table_threads, 'author', 'authorid', TRUE);
	} elseif($step == 10) {
		user2id($table_posts, 'author', 'authorid', TRUE);
	} elseif($step == 11) {
		runquery($upgrade2);
		$query = $db->query("SELECT DISTINCT attachment FROM $table_threads WHERE attachment<>''");
		while($t = $db->fetch_array($query)) {
			$db->query("UPDATE $table_threads SET attachment='".attachtype($t['attachment'], 'id')."' WHERE attachment='$t[attachment]'");
		}
	} elseif($step == 12) {
		runquery($upgrade3);
		$group = array();
		$query = $db->query("SELECT * FROM $table_usergroups");
		while($g = $db->fetch_array($query)) {
			switch($g['status']) {
				case 'Admin': $group[1] = $g; break;
				case 'SuperMod': $group[2] = $g; break;
				case 'Moderator': $group[3] = $g; break;
				case 'PostBanned': $group[4] = $g; break;
				case 'Banned': $group[5] = $g; break;
				case 'IPBanned': $group[6] = $g; break;
				case 'Guest': $group[7] = $g; break;
				case 'Inactive': $group[8] = $g; break;
				case 'Member': $group[] = $g; break;
			}
		}
		$db->query("DELETE FROM $table_usergroups");
		$db->query("ALTER TABLE $table_members DROP status");
		ksort($group);
		foreach($group as $groupid => $array) {
			$sql = "INSERT INTO $table_usergroups VALUES ('$groupid'";
			foreach($array as $key => $value) {
				if($key != 'groupid') {
					$sql .= ",'".addslashes($value)."'";
				}
			}
			$sql .= ')';
			$db->query($sql);
		}
		$query = $db->query("SELECT groupid, specifiedusers FROM $table_usergroups WHERE specifiedusers<>''");
		while($g = $db->fetch_array($query)) {
			$g['specifiedusers'] = "'".str_replace("\t", "','", trim($g['specifiedusers']))."'";
			$db->query("UPDATE $table_members SET groupid='$g[groupid]', adminid='-1' WHERE username IN ($g[specifiedusers])");
		}
		$db->query("ALTER TABLE $table_usergroups DROP INDEX creditshigher, DROP INDEX creditslower, ADD INDEX creditsrange (creditshigher, creditslower), DROP specifiedusers, DROP status, DROP maxmemonum, DROP ismoderator, DROP issupermod, DROP isadmin, ADD allowhidecode tinyint(1) NOT NULL AFTER allowsetattachperm, CHANGE grouptitle grouptitle CHAR(30) NOT NULL, CHANGE groupavatar groupavatar CHAR(60) NOT NULL, CHANGE attachextensions attachextensions CHAR(60) NOT NULL;");
	} elseif($step == 13) {
		$query = $db->query("ALTER TABLE $table_stats DROP INDEX type, DROP INDEX var, ADD PRIMARY KEY (type, var)", 'SILENT');
		$query = $db->query("SELECT groupid, creditshigher, creditslower FROM $table_usergroups WHERE (creditshigher<>'0' OR creditslower<>'0') AND groupid>'8'");
		while($g = $db->fetch_array($query)) {
			$db->query("UPDATE $table_members SET groupid='$g[groupid]' WHERE groupid='0' AND credit>='$g[creditshigher]' AND credit<'$g[creditslower]'");
		}
		$query = $db->query("SELECT * FROM $table_settings");
		foreach($db->fetch_array($query) as $key => $val) {
			$val = addslashes($val);
			$db->query("INSERT INTO settmp VALUES('$key', '$val')");
		}
		$db->query("DROP TABLE $table_settings");
		$db->query("INSERT INTO settmp VALUES ('replycredits', '1')");
		$db->query("INSERT INTO settmp VALUES ('deletedcredits', '1')");
		$db->query("INSERT INTO settmp VALUES ('loadctrl', '0')");
		$db->query("INSERT INTO settmp VALUES ('forumjump', '1')");
		$db->query("INSERT INTO settmp VALUES ('accessmasks', '0')");
		$db->query("INSERT INTO settmp VALUES ('maxavatarpixel', '120')");
		$db->query("INSERT INTO settmp VALUES ('maxsearchresults', '512')");
		$db->query("INSERT INTO settmp VALUES ('delayviewcount', '0')");
		$db->query("INSERT INTO settmp VALUES ('maxpolloptions', '10')");
		$db->query("UPDATE settmp SET value='30' WHERE variable='searchctrl'");
		$db->query("ALTER TABLE settmp RENAME $table_settings, ORDER BY variable");

		$query = $db->query("SELECT tid, pollopts FROM $table_threads WHERE pollopts<>''");
		while($thread = $db->fetch_array($query)) {
			$thread['pollopts'] = addslashes($thread['pollopts']);
			$db->query("INSERT INTO $table_polls (tid, pollopts) VALUES ('$thread[tid]', '$thread[pollopts]')");
		}
		$db->query("ALTER TABLE $table_threads ADD poll tinyint(1) NOT NULL AFTER pollopts");
		$db->query("UPDATE $table_threads SET poll='1' WHERE pollopts<>''");
		$db->query("ALTER TABLE $table_threads DROP pollopts");
		$db->query("ALTER TABLE $table_banned DROP INDEX ip1, DROP INDEX ip2, DROP INDEX ip3, DROP INDEX ip4", 'SILENT');

		$query = $db->query("SELECT id, url FROM $table_smilies WHERE type='icon'");
		while($icon = $db->fetch_array($query)) {
			$db->query("UPDATE $table_threads SET iconid='$icon[id]' WHERE icon='$icon[url]'");
		}
		$db->query("ALTER TABLE $table_threads DROP icon");
		$db->query("ALTER TABLE $table_threads CHANGE author author CHAR(15) NOT NULL, CHANGE subject subject CHAR(80) NOT NULL, CHANGE lastposter lastposter CHAR(15) NOT NULL");

		@unlink('./forumdata/cache/cache_settings.php');
		echo "恭喜您升级成功,请删除本程序.<br>因为不同版本机制不同，您原有分论坛中的用户组权限设定都被清空了，请务必到论坛编辑中重新设置，给您带来的不便请您谅解，谢谢。";
		$db->query("ALTER TABLE $table_forums DROP theme", 'SILENT');
	}

	if($step < 13) {
		echo "第 $step 步升级成功<br>";
		redirect("$PHP_SELF?action=upgrade&step=".($step + 1));
	}

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

function user2id($table, $columnorig = 'username', $columnnew = 'uid', $keepold = FALSE) {
	extract($GLOBALS, EXTR_SKIP);
	$startrow = intval($HTTP_GET_VARS['startrow']);
	$converted = 0;
	if(!$startrow) {
		$db->query("ALTER TABLE $table ADD $columnnew mediumint(8) UNSIGNED NOT NULL AFTER $columnorig");
	}
	$query = $db->query("SELECT $table.$columnorig, $table_members.uid FROM $table LEFT JOIN $table_members ON $table.$columnorig=$table_members.username GROUP BY $table.$columnorig LIMIT $startrow, 100");
	while($member = $db->fetch_array($query)) {
		$converted = 1;
		$db->query("UPDATE $table SET $columnnew='$member[uid]' WHERE $columnorig='".addslashes($member[$columnorig])."'");
	}
	if(empty($converted)) {
		if(empty($keepold)) {
			$db->query("ALTER TABLE $table DROP $columnorig");
		}
		echo "数据表 $table 转换完毕。<br><br>";
	} else {
		echo "转换数据表 $table, 起始于第 $startrow 列<br>";
		redirect("$PHP_SELF?action=upgrade&step=$step&startrow=".($startrow + 100));
		exit();
	}
}

function attachtype($type, $returnval = 'html') {
	if(!isset($GLOBALS['_DCACHE']['attachicon'])) {
		$GLOBALS['_DCACHE']['attachicon'] = array
			(	1 => 'common.gif',
				2 => 'binary.gif',
				3 => 'zip.gif',
				4 => 'rar.gif',
				5 => 'msoffice.gif',
				6 => 'text.gif',
				7 => 'html.gif',
				8 => 'real.gif',
				9 => 'av.gif',
				10 => 'flash.gif',
				11 => 'image.gif'
			);
	}

	if(is_numeric($type)) {
		$typeid = $type;
	} else {
		if(preg_match("/image|^(jpg|gif|png|bmp)\t/", $type)) {
			$typeid = 11;
		} elseif(preg_match("/flash|^(swf|fla|swi)\t/", $type)) {
			$typeid = 10;
		} elseif(preg_match("/audio|video|^(wav|mid|mp3|m3u|wma|asf|asx|vqf|mpg|mpeg|avi|wmv)\t/", $type)) {
			$typeid = 9;
		} elseif(preg_match("/real|^(ra|rm|rv)\t/", $type)) {
			$typeid = 8;
		} elseif(preg_match("/htm|^(php|js|pl|cgi|asp)\t/", $type)) {
			$typeid = 7;
		} elseif(preg_match("/text|^(txt|rtf|wri|chm)\t/", $type)) {
			$typeid = 6;
		} elseif(preg_match("/word|powerpoint|^(doc|ppt)\t/", $type)) {
			$typeid = 5;
		} elseif(preg_match("/^rar\t/", $type)) {
			$typeid = 4;
		} elseif(preg_match("/compressed|^(zip|arj|arc|cab|lzh|lha|tar|gz)\t/", $type)) {
			$typeid = 3;
		} elseif(preg_match("/octet-stream|^(exe|com|bat|dll)\t/", $type)) {
			$typeid = 2;
		} elseif($type) {
			$typeid = 1;
		} else {
			$typeid = 0;
		}
	}
	if($returnval == 'html') {
		return '<img src="images/attachicons/'.$GLOBALS['_DCACHE']['attachicon'][$typeid].'" align="absmiddle" border="0">';
	} elseif($returnval == 'id') {
		return $typeid;
	}
}

?>