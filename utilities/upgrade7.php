<?php

// Upgrade Discuz! Board from 4.1.0 to 5.0.0

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 4.1.0';
$version_new = 'Discuz! 5.0.0';

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("请先上传所有新版本的程序文件后再运行本升级程序");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

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

$cron_pushthread_week = rand(1, 7);
$cron_pushthread_hour = rand(1, 8);

$upgrade1 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('ratelogrecord', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('maxbdays', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('jsmenustatus', 15);
REPLACE INTO cdb_settings (variable, value) VALUES ('attachbanperiods', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxonlinelist', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('icp', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('regadvance', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('myrecorddays', '30');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxfavorites', '100');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxsubscriptions', '100');
REPLACE INTO cdb_settings (variable, value) VALUES ('accessemail', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_id', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_commision', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('allowcsscache', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_location', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_jammer', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_searchboxtxt', '输入关键词，快速搜索本论坛');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_relatedsort', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_allsearch', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_ustyle', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('showsettings', '7');
REPLACE INTO cdb_settings (variable, value) VALUES ('editoroptions', '3');
REPLACE INTO cdb_settings (variable, value) VALUES ('threadsticky', '全局置顶,分类置顶,本版置顶');
REPLACE INTO cdb_settings (variable, value) VALUES ('smcols', '4');
DELETE FROM cdb_settings WHERE variable = 'fullmytopics' LIMIT 1;

INSERT INTO cdb_smilies VALUES ('', 0, 'smiley', ':loveliness:', 'loveliness.gif');
INSERT INTO cdb_smilies VALUES ('', 0, 'smiley', ':funk:', 'funk.gif');

INSERT INTO cdb_crons VALUES ('', 1, 'system', '每月主题清理','cleanup_monthly.inc.php', 0, 1143770387, -1, 1, 6, '00');
INSERT INTO cdb_crons VALUES ('', 1, 'system', '每日 X-Space更新用户', 'supe_daily.inc.php', 1149645106, 1149696000, -1, -1, 0, '0');
INSERT INTO cdb_crons VALUES ('', 1, 'system', '每周主题更新', 'pushthreads_weekly.inc.php', 1150122554, 1150660800, $cron_pushthread_week, -1, $cron_pushthread_hour, '0');

EOT;

$upgrade2 = <<<EOT

ALTER TABLE cdb_announcements ADD redirect tinyint(1) NOT NULL default 0 AFTER subject;

ALTER TABLE cdb_favorites ADD fid smallint(6) unsigned NOT NULL default '0';

ALTER TABLE cdb_forums
	CHANGE allowblog allowshare tinyint (1)  default '0' NOT NULL,
	CHANGE allowtrade allowpostspecial tinyint(1) NOT NULL default '15',
	ADD forumcolumns tinyint(3) unsigned NOT NULL default '0',
	ADD threadcaches tinyint(1) default '0' NOT NULL,
	ADD allowpaytoauthor tinyint(1) unsigned NOT NULL default '1';

ALTER TABLE cdb_forumfields
	ADD digestcredits varchar(255) NOT NULL default '' AFTER replycredits,
	ADD postattachcredits varchar(255) NOT NULL default '' AFTER replycredits,
	ADD getattachcredits varchar(255) NOT NULL default '' AFTER replycredits;

ALTER TABLE cdb_usergroups
	ADD allowpostactivity tinyint(1) NOT NULL default '0'AFTER allowpostpoll,
	ADD allowposttrade tinyint(1) NOT NULL default '0'AFTER allowpostpoll,
	ADD allowpostreward tinyint(1) NOT NULL default '0' AFTER allowpostpoll,
	ADD tradetaxtype tinyint (1) NOT NULL default '1',
	ADD tradetaxs smallint(6) unsigned NOT NULL default '0',
	ADD mintradeprice smallint(6) unsigned NOT NULL default '1',
	ADD maxtradeprice smallint(6) unsigned NOT NULL default '0',
	ADD minrewardprice smallint(6) unsigned NOT NULL default '1',
	ADD maxrewardprice smallint(6) unsigned NOT NULL default '0',
	ADD maxpostsperhour tinyint(3) unsigned NOT NULL default '0' AFTER maxsizeperday;

UPDATE cdb_usergroups SET allowpostactivity=1, allowposttrade=1, allowpostreward=1 WHERE radminid>0 OR creditshigher>=200;

ALTER TABLE cdb_buddys ADD grade tinyint(3) unsigned default '1' NOT NULL AFTER buddyid;

ALTER TABLE cdb_bbcodes ADD icon varchar(255) NOT NULL AFTER tag ;
UPDATE cdb_bbcodes SET icon = 'bb_flash.gif' WHERE tag ='flash';
UPDATE cdb_bbcodes SET icon = 'bb_wmv.gif' WHERE tag ='wmv';
UPDATE cdb_bbcodes SET icon = 'bb_fly.gif' WHERE tag ='fly';
UPDATE cdb_bbcodes SET icon = 'bb_qq.gif' WHERE tag ='qq';
UPDATE cdb_bbcodes SET icon = 'bb_ra.gif' WHERE tag ='ra';
UPDATE cdb_bbcodes SET icon = 'bb_rm.gif' WHERE tag ='rm';
UPDATE cdb_bbcodes SET icon = 'bb_wma.gif' WHERE tag ='wma';

EOT;

$upgrade3 = <<<EOT

ALTER TABLE cdb_members
	ADD editormode tinyint( 1 ) unsigned NOT NULL default '2',
	ADD customshow tinyint( 1 ) unsigned  NOT NULL default '26';
EOT;

$upgrade4 = <<<EOT

ALTER TABLE cdb_threads
	CHANGE poll special tinyint(1) NOT NULL default '0';
EOT;

$upgrade5 = <<<EOT

ALTER TABLE cdb_attachments
	ADD isimage tinyint(1) unsigned NOT NULL default '0',
	ADD uid mediumint(8) unsigned NOT NULL default '0',
	ADD INDEX uid (uid);
EOT;

$upgrade6 = <<<EOT

DROP TABLE IF EXISTS cdb_myposts;
CREATE TABLE cdb_myposts (
  uid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  position smallint(6) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,tid),
  KEY tid (tid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_mythreads;
CREATE TABLE cdb_mythreads (
  uid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) NOT NULL default '0',
  PRIMARY KEY  (uid,tid),
  KEY tid (tid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_polls_temp;
RENAME TABLE cdb_polls TO cdb_polls_temp;

DROP TABLE IF EXISTS cdb_polls;
CREATE TABLE cdb_polls (
 tid mediumint(8) unsigned NOT NULL default '0',
 multiple tinyint(1) NOT NULL default '0',
 visible tinyint(1) NOT NULL default '0',
 maxchoices tinyint(3) unsigned NOT NULL default '0',
 expiration int(10) unsigned NOT NULL default '0',
 PRIMARY KEY(tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_polloptions;
CREATE TABLE cdb_polloptions (
 polloptionid int(10) unsigned NOT NULL auto_increment,
 tid mediumint(8) unsigned NOT NULL default '0',
 votes mediumint(8) unsigned NOT NULL default '0',
 displayorder tinyint(3) NOT NULL default '0',
 polloption varchar(80) NOT NULL default '',
 voterids mediumtext NOT NULL,
 PRIMARY KEY(polloptionid),
 KEY tid (tid, displayorder)
)TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_trades;
CREATE TABLE cdb_trades (
  tid mediumint(8) unsigned NOT NULL,
  sellerid mediumint(8) unsigned NOT NULL,
  seller char(15) NOT NULL,
  account char(50) NOT NULL,
  subject char(100) NOT NULL,
  price decimal(6,2) NOT NULL,
  amount smallint(6) unsigned NOT NULL default '1',
  quality tinyint(1) unsigned NOT NULL default '0',
  locus char(20) NOT NULL,
  transport tinyint(1) NOT NULL default '0',
  ordinaryfee smallint(4) unsigned NOT NULL default '0',
  expressfee smallint(4) unsigned NOT NULL default '0',
  emsfee smallint(4) unsigned NOT NULL default '0',
  itemtype tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  lastbuyer char(15) NOT NULL,
  lastupdate int(10) unsigned NOT NULL default '0',
  totalitems smallint(5) unsigned NOT NULL default '0',
  tradesum decimal(8,2) NOT NULL default '0.00',
  closed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY sellerid (sellerid),
  KEY totalitems (totalitems),
  KEY tradesum (tradesum)
)TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_tradelog;
CREATE TABLE cdb_tradelog (
  tid mediumint(8) unsigned NOT NULL,
  orderid char(32) NOT NULL,
  tradeno char(32) NOT NULL,
  subject char(100) NOT NULL,
  price decimal(6,2) NOT NULL default '0.00',
  quality tinyint(1) unsigned NOT NULL default '0',
  itemtype tinyint(1) NOT NULL default '0',
  number smallint(5) unsigned NOT NULL default '0',
  tax decimal(6,2) unsigned NOT NULL default '0.00',
  locus char(100) NOT NULL,
  sellerid mediumint(8) unsigned NOT NULL,
  seller char(15) NOT NULL,
  selleraccount char(50) NOT NULL,
  buyerid mediumint(8) unsigned NOT NULL,
  buyer char(15) NOT NULL,
  buyercontact char(50) NOT NULL,
  buyercredits smallint(5) unsigned NOT NULL default '0',
  buyermsg char(200) default NULL,
  status tinyint(1) NOT NULL default '0',
  lastupdate int(10) unsigned NOT NULL default '0',
  UNIQUE KEY orderid (orderid),
  KEY sellerid (sellerid),
  KEY buyerid (buyerid),
  KEY tid (tid),
  KEY status (status),
  KEY buyerlog (buyerid,status,lastupdate),
  KEY sellerlog (sellerid,status,lastupdate)
)TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_rewardlog;
CREATE TABLE cdb_rewardlog (
 tid mediumint(8) unsigned NOT NULL default '0',
 authorid mediumint(8) unsigned NOT NULL default '0',
 answererid mediumint(8) unsigned NOT NULL default '0',
 dateline int(10) unsigned default '0',
 netamount int(10) unsigned NOT NULL default '0',
 KEY userid (authorid,answererid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_activities;
CREATE TABLE cdb_activities (
  tid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  cost mediumint(8) unsigned NOT NULL default '0',
  starttimefrom int(10) unsigned NOT NULL default '0',
  starttimeto int(10) unsigned NOT NULL default '0',
  place char(40) NOT NULL default '',
  class char(20) NOT NULL default '',
  gender tinyint(1) NOT NULL default '0',
  number smallint(5) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY uid (uid,starttimefrom)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_activityapplies;
CREATE TABLE cdb_activityapplies (
  applyid int(10) unsigned NOT NULL auto_increment,
  tid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  message char(200) NOT NULL default '',
  verified tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  payment mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (applyid),
  KEY uid (uid),
  KEY tid (tid),
  KEY dateline (tid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_pushedthreads;
CREATE TABLE cdb_pushedthreads (
  id mediumint(8) unsigned NOT NULL,
  tid mediumint(8) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  subject char(80) NOT NULL,
  message text NOT NULL,
  PRIMARY KEY  (id),
  KEY displayorder (status,dateline)
) TYPE=MyISAM;

EOT;

$upgrademsg = array(
	1 => '论坛升级第 1 步: 增加基本设置<br>',
	2 => '论坛升级第 2 步: 调整论坛数据表结构<br>',
	3 => '论坛升级第 3 步: 调整会员数据表结构<br>',
	4 => '论坛升级第 4 步: 增加主题数据表结构<br>',
	5 => '论坛升级第 5 步: 增加附件数据表结构<br>',
	6 => '论坛升级第 6 步: 新增部分数据表<br>',
	7 => '论坛升级第 7 步: 转换投票数据，如果您的投票主题比较多，此步骤需要较长时间<br>',
	8 => '论坛升级第 8 步: 转换附件数据，如果您的附件比较多，此步骤需要较长时间<br>',
	9 => '论坛升级第 9 步: 转换论坛栏目资料，如果您的论坛栏目比较多，此步骤需要较长时间<br>',
	10 => '论坛升级第 10 步: 安装 Discuz!5.0.0 新风格<br>',
	11 => '论坛升级第 11 步: 增加 X-Space 和 SupeSite 支持<br>',
	12 => '论坛升级第 12 步: 升级全部完毕<br>',
);

if(!$action) {
	echo "本程序用于升级 $version_old 到 $version_new,请确认之前已经顺利安装 $version_old <br><br><br>",
		"<b><font color=\"red\">本升级程序只能从 $version_old 升级到 $version_new ,运行之前,请确认已经上传 $version_new 的全部文件和目录</font></b><br>",
		"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>",
		"正确的升级方法为:<br><ol><li>关闭原有论坛,上传 $version_new 的全部文件和目录,覆盖服务器上的 $version_old <li>上传本程序到 Discuz! 目录中，并确认已经重新配置好 config.inc.php <li>运行本程序,直到出现升级完成的提示</ol><br>",
		"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$step = intval($step);
	echo $upgrademsg[$step];
	flush();

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		if(!dir_writeable('./forumdata/threadcaches')) {
			echo '升级检测失败，无法建立目录 /forumdata/threadcaches，请手工建立此目录，然后重新运行升级程序';
			exit;
		}

		runquery($upgrade1);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		runquery($upgrade2);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		runquery($upgrade4);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 5) {

		runquery($upgrade5);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 6) {

		runquery($upgrade6);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 7) {

		$limit = 500; // 每次升级多少投票主题
		$start = intval($start);
		$next = FALSE;

		$query = $db->query("SELECT tid, pollopts from {$tablepre}polls_temp LIMIT $start, $limit");
		while($array = $db->fetch_array($query)) {

			$next = TRUE;

			$polltid = $array['tid'];
			$pollopts = unserialize($array['pollopts']);

			if(!empty($pollopts['options'])) {
				foreach($pollopts['options'] as $temp_options) {
					$db->query("REPLACE INTO {$tablepre}polloptions (tid, votes, polloption) VALUES ('$polltid', '$temp_options[1]', '$temp_options[0]')");
				}
			}

			if($pollopts['multiple']) {
				$maxchoices = 0;
			} else {
				$maxchoices = 1;
			}

			$closedquery = $db->query("SELECT closed FROM {$tablepre}threads WHERE tid='$polltid'");
			$closed = $db->result($closedquery, 0);
			$expiration = $closed ? $timestamp : 0;

			$db->query("REPLACE INTO {$tablepre}polls (tid, multiple, visible, maxchoices, expiration) VALUES ('$polltid', '$pollopts[multiple]', '1', '$maxchoices', '$expiration')");

		}

		if($next) {
			echo "正在进行第 $step 步升级 起始列数: $start<br><br>";
			redirect("?action=upgrade&step=$step&start=".($start+$limit));
		} else {
			$db->query("DROP TABLE IF EXISTS cdb_polls_temp");
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 8) {

		$limit = 500; // 每次升级多少附件
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT a.aid, a.filetype, p.authorid FROM {$tablepre}attachments a, {$tablepre}posts p WHERE a.pid=p.pid LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$isimage = substr($data['filetype'], 0, 5) == 'image' ? '1' : '0';
			$db->query("UPDATE {$tablepre}attachments SET isimage='$isimage', uid='$data[authorid]' WHERE aid='$data[aid]'");
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 9) {

		$limit = 100; // 每次升级多少栏目板块
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT fid, threadtypes FROM {$tablepre}forumfields WHERE 1 LIMIT $start, $limit");
		while($forum = $db->fetch_array($query)) {
			$next = TRUE;
			$forum['threadtypes'] = unserialize($forum['threadtypes']);

			if(!empty($forum['threadtypes']['types'])) {
				$newthreadtypes = addslashes(serialize(array
					(
					'required' => (bool)$forum['threadtypes']['required'],
					'listable' => (bool)$forum['threadtypes']['listable'],
					'prefix' => (bool)$forum['threadtypes']['prefix'],
					'types' => $forum['threadtypes']['types'],
					'selectbox' => '',
					'flat' => $forum['threadtypes']['types'],
					)));
			} else {
				$newthreadtypes = '';
			}
			$db->query("UPDATE {$tablepre}forumfields SET threadtypes='$newthreadtypes' WHERE fid='$forum[fid]'");
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 10) {

		$query = $db->query("SELECT styleid FROM {$tablepre}styles ORDER BY styleid DESC LIMIT 1");
		$styleid = intval($db->result($query, 0));
		$newstyleid = $styleid + 1;

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('indexname', 'index.php')");
		$db->query("REPLACE INTO {$tablepre}styles (styleid, name, available, templateid) VALUES ('$newstyleid', 'Discuz! 5 Default', '1', '1')");
		$db->query("REPLACE INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES
			('$newstyleid', 'boardimg', 'logo.gif'),
			('$newstyleid', 'nobold', '0'),
			('$newstyleid', 'msgfontsize', '12px'),
			('$newstyleid', 'fontsize', '12px'),
			('$newstyleid', 'font', 'Tahoma, Verdana'),
			('$newstyleid', 'tablespace', '4'),
			('$newstyleid', 'tablewidth', '98%'),
			('$newstyleid', 'borderwidth', '1'),
			('$newstyleid', 'text', '#333333'),
			('$newstyleid', 'tabletext', '#333333'),
			('$newstyleid', 'catcolor', '#FFFFD9'),
			('$newstyleid', 'headertext', '#154BA0'),
			('$newstyleid', 'headercolor', 'header_bg.gif'),
			('$newstyleid', 'bordercolor', '#86B9D6'),
			('$newstyleid', 'link', '#154BA0'),
			('$newstyleid', 'altbg2', '#FFFFFF'),
			('$newstyleid', 'altbg1', '#F5FBFF'),
			('$newstyleid', 'bgcolor', '#FFFFFF'),
			('$newstyleid', 'imgdir', 'images/default'),
			('$newstyleid', 'smdir', 'images/smilies'),
			('$newstyleid', 'cattext', '#92A05A'),
			('$newstyleid', 'smfontsize', '11px'),
			('$newstyleid', 'smfont', 'Arial, Tahoma'),
			('$newstyleid', 'maintablewidth', '98%'),
			('$newstyleid', 'maintablecolor', '#FFFFFF'),
			('$newstyleid', 'innerborderwidth', '0'),
			('$newstyleid', 'innerbordercolor', '#D6E0EF'),
			('$newstyleid', 'bgborder', '#BBE9FF'),
			('$newstyleid', 'inputborder', '#7AC4EA'),
			('$newstyleid', 'mainborder', '#154BA0'),
			('$newstyleid', 'catborder', '#DEDEB8'),
			('$newstyleid', 'lighttext', '#666666'),
			('$newstyleid', 'headermenu', 'menu_bg.gif'),
			('$newstyleid', 'postnoticebg', '#FDFFF2'),
			('$newstyleid', 'msgheader', '#F3F8D7'),
			('$newstyleid', 'msgheadertext', '#000000'),
			('$newstyleid', 'msgtext', '#FDFFF2'),
			('$newstyleid', 'headermenutext', '#154BA0'),
			('$newstyleid', 'navtext', '#154BA0'),
			('$newstyleid', 'menubg', '#D9EEF9'),
			('$newstyleid', 'menutext', '#154BA0'),
			('$newstyleid', 'menuhltext', '#FFFFFF'),
			('$newstyleid', 'menuhlbg', '#7AC4EA'),
			('$newstyleid', 'calendartext', '#000000'),
			('$newstyleid', 'calendarexpire', '#999999'),
			('$newstyleid', 'calendarchecked', '#FF0000'),
			('$newstyleid', 'calendartoday', '#00BB00');");
		$db->query("UPDATE {$tablepre}settings SET value = '$newstyleid' WHERE variable = 'styleid'");
		$db->query("UPDATE {$tablepre}members SET styleid = '0'");
		$db->query("UPDATE {$tablepre}styles SET available = '0' WHERE styleid != '$newstyleid'");

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 11) {

		$start = $start ? intval($start) : 1;

		if($start == 1) {
			$supe = array();
			$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable IN ('supe_maxupdateusers', 'supe_status', 'supe_siteurl', 'supe_sitename', 'supe_tablepre')");
			while($setting = $db->fetch_array($query)) {
				$supe[$setting['variable']] = $setting['value'];
			}
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_maxupdateusers', '$supe[supe_maxupdateusers]')");
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_status', '$supe[supe_status]')");
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_siteurl', '$supe[supe_siteurl]')");
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_sitename', '$supe[supe_sitename]')");
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_tablepre', '$supe[supe_tablepre]')");

			$db->query("ALTER TABLE {$tablepre}forumfields ADD supe_pushsetting TEXT NOT NULL", 'SILENT');
			$db->query("ALTER TABLE {$tablepre}admingroups ADD supe_allowpushthread tinyint(1) NOT NULL default '0'", 'SILENT');
			$db->query("UPDATE {$tablepre}admingroups SET supe_allowpushthread='1' WHERE admingid='1'", 'SILENT');

			echo "第 $step 步 步骤[ $start ] 升级成功<br><br>";
			redirect("?action=upgrade&step=$step&start=2");

		} else {

			$db->query("ALTER TABLE {$tablepre}members ADD xspacestatus tinyint(1) NOT NULL default '0'", 'SILENT');
			$db->query("ALTER TABLE {$tablepre}threads ADD itemid mediumint(8) unsigned NOT NULL default '0'", 'SILENT');
			$db->query("ALTER TABLE {$tablepre}threads ADD supe_pushstatus tinyint(1) NOT NULL default '0'", 'SILENT');

			echo "第 $step 步 步骤[ $start ] 升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));

		}

	} else {

		echo '<br>恭喜您论坛数据升级成功，接下来请您：<ol><li><b>必删除本程序</b>'.
			'<li>使用管理员身份登录论坛，进入后台，更新缓存'.
			'<li>进行论坛注册、登录、发贴等常规测试，看看运行是否正常'.
			'<li>如果您希望启用 <b>'.$version_new.'</b> 提供的新功能，你还需要对于论坛基本设置、栏目、会员组等等进行重新设置</ol><br><br>'.
			'<b>感谢您选用我们的产品！</b>';
		exit;
	}
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

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.test", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.test");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
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

function loginit($log) {
	global $lang;

	$fp = @fopen('./forumdata/'.$log.'.php');
	@fwrite($fp, "<?PHP exit(\"Access Denied\"); ?>\n");
	@fclose($fp);
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		$sql = str_replace(' cdb_', ' '.$tablepre, $sql);
		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function redirect($url) {

	echo "<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";
	flush();

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

?>