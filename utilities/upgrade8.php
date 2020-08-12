<?php

// Upgrade Discuz! Board from 5.0.0 to 5.5.0
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 5.0.0';
$version_new = 'Discuz! 5.5.0';
$timestamp = time();

@include("./config.inc.php");
@include("./include/db_mysql.class.php");

header("Content-Type: text/html; charset=$charset");
showheader();

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

$upgrade1 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('spacecachelife', 1800);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmythreads', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyreplies', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyrewards', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmytrades', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyblogs', 8);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfriends', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfavforums', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfavthreads', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacetextlength', 300);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbstatus', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbwidth', 400);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbheight', 300);
REPLACE INTO cdb_settings (variable, value) VALUES ('forumlinkstatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('pluginjsmenu', '插件');
REPLACE INTO cdb_settings (variable, value) VALUES ('magicstatus', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('magicmarket', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxmagicprice', '50');
REPLACE INTO cdb_settings (variable, value) VALUES ('jswizard', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('passport_shopex', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('seccodeanimator', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('welcomemsgtitle', '{username}，您好，感谢您的注册，请阅读以下内容。');
REPLACE INTO cdb_settings (variable, value) VALUES ('welcomemsgtxt', '尊敬的{username}，您已经注册成为{sitename}的会员，请您在发表言论时，遵守当地法律法规。\r\n如果您有什么疑问可以联系管理员，Email: {adminemail}。\r\n\r\n\r\n{bbname}\r\n{time}');
REPLACE INTO cdb_settings (variable, value) values ('cacheindexlife', '0');
REPLACE INTO cdb_settings (variable, value) values ('cachethreadlife', '0');
REPLACE INTO cdb_settings (variable, value) values ('cachethreaddir', 'forumdata/threadcaches');
REPLACE INTO cdb_settings (variable, value) values ('jsdateformat', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seccodedata', '');
REPLACE INTO cdb_settings (variable, value) values ('frameon', '0');
REPLACE INTO cdb_settings (variable, value) values ('framewidth', '180');
REPLACE INTO cdb_settings (variable, value) VALUES ('smrows', '4');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarktype', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('spacestatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('whosonline_contract', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('attachdir', './attachments');
REPLACE INTO cdb_settings (variable, value) VALUES ('attachurl', 'attachments');
REPLACE INTO cdb_settings (variable, value) VALUES ('onlinehold', '15');
REPLACE INTO cdb_settings (variable, value) VALUES ('wapregister', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('msgforward', 'a:3:{s:11:\"refreshtime\";i:1;s:5:\"quick\";i:1;s:8:\"messages\";a:13:{i:0;s:19:\"thread_poll_succeed\";i:1;s:19:\"thread_rate_succeed\";i:2;s:23:\"usergroups_join_succeed\";i:3;s:23:\"usergroups_exit_succeed\";i:4;s:25:\"usergroups_update_succeed\";i:5;s:20:\"buddy_update_succeed\";i:6;s:17:\"post_edit_succeed\";i:7;s:18:\"post_reply_succeed\";i:8;s:24:\"post_edit_delete_succeed\";i:9;s:22:\"post_newthread_succeed\";i:10;s:13:\"admin_succeed\";i:11;s:17:\"pm_delete_succeed\";i:12;s:15:\"search_redirect\";}}');
REPLACE INTO cdb_settings (variable, value) VALUES ('forumjump','0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ftp', 'a:10:{s:2:\"on\";s:1:\"0\";s:3:\"ssl\";s:1:\"0\";s:4:\"host\";s:0:\"\";s:4:\"port\";s:2:\"21\";s:8:\"username\";s:0:\"\";s:8:\"password\";s:0:\"\";s:9:\"attachdir\";s:1:\".\";s:9:\"attachurl\";s:0:\"\";s:7:\"hideurl\";s:1:\"0\";s:7:\"timeout\";s:1:\"0\";}');
REPLACE INTO cdb_settings (variable, value) VALUES ('secqaa', 'a:2:{s:8:\"minposts\";s:1:\"1\";s:6:\"status\";i:0;}');
REPLACE INTO cdb_settings (variable, value) values ('smthumb','20');

DELETE FROM cdb_settings WHERE variable IN ('qihoo_searchboxtxt', 'qihoo_ustyle', 'qihoo_allsearch');
DELETE FROM cdb_settings WHERE variable='avatarshowwidth';
DELETE FROM cdb_settings WHERE variable='avatarshowstatus';
DELETE FROM cdb_settings WHERE variable='avatarshowpos';
DELETE FROM cdb_settings WHERE variable='avatarshowlink';
DELETE FROM cdb_settings WHERE variable='avatarshowheight';
DELETE FROM cdb_settings WHERE variable='avatarshowdefault';

EOT;

$upgradetable = array(

	array('usergroups', 'CHANGE', 'minrewardprice', "minrewardprice smallint(6) NOT NULL default '1'"),
	array('usergroups', 'CHANGE', 'maxrewardprice', "maxrewardprice smallint(6) NOT NULL default '0'"),
	array('usergroups', 'ADD', 'magicsdiscount', "tinyint(1) NOT NULL default '0'"),
	array('usergroups', 'ADD', 'allowmagics', "tinyint(1) unsigned NOT NULL default '1'"),
	array('usergroups', 'ADD', 'maxmagicsweight', "smallint(6) unsigned NOT NULL default '100'"),
	array('usergroups', 'ADD', 'allowbiobbcode', "tinyint(1) unsigned NOT NULL default '0'"),
	array('usergroups', 'ADD', 'allowbioimgcode', "tinyint(1) unsigned NOT NULL default '0'"),
	array('usergroups', 'ADD', 'maxbiosize', "smallint(6) unsigned NOT NULL default '0'"),

	array('forums', 'ADD', 'alloweditpost', "tinyint(1) unsigned NOT NULL default '1'"),
	array('forums', 'ADD', 'simple', "tinyint(1) unsigned NOT NULL default '0'"),
	array('forums', 'ADD', 'allowspecialonly', "tinyint(1) unsigned NOT NULL default '0' AFTER allowpostspecial"),

	array('attachments', 'ADD', 'thumb', "tinyint(1) unsigned NOT NULL default '0'"),
	array('attachments', 'ADD', 'price', "smallint(6) unsigned not NULL default '0' AFTER readperm"),
	array('attachments', 'ADD', 'remote', "tinyint(1) unsigned NOT NULL default '0'"),

	array('threadsmod', 'ADD', 'magicid', "smallint(6) unsigned NOT NULL"),
	array('threadsmod', 'CHANGE', 'action', "action CHAR(5) NOT NULL"),

	array('announcements', 'CHANGE', 'redirect', "type tinyint(1) NOT NULL default '0'"),
	array('announcements', 'ADD', 'groups', "text NOT NULL"),

	array('activityapplies', 'ADD', 'contact', "CHAR(200) NOT NULL"),

	array('forumlinks', 'CHANGE', 'note', "description mediumtext NOT NULL"),

	array('sessions', 'CHANGE', 'seccode', "seccode mediumint(6) unsigned NOT NULL default '0'"),

	array('bbcodes', 'ADD', 'prompt', "TEXT NOT NULL AFTER params"),

	array('memberfields', 'ADD', 'spacename', "varchar(40) NOT NULL"),
	array('memberfields', 'DROP', 'signature', ""),

	array('members', 'DROP', 'avatarshowid', ""),

	array('pms', 'ADD', 'delstatus', "tinyint(1) unsigned NOT NULL default '0'"),

);

$upgrade2 = <<<EOT

UPDATE cdb_forums SET alloweditpost=1;
UPDATE cdb_usergroups SET maxmagicsweight=100 WHERE groupid<4 OR groupid>9;

UPDATE cdb_bbcodes SET prompt = '请输入滚动显示的文字:' WHERE `tag` ='fly';
UPDATE cdb_bbcodes SET prompt = '请输入 Flash 动画的 URL:' WHERE `tag` ='flash';
UPDATE cdb_bbcodes SET prompt = '请输入显示在线状态 QQ 号码:' WHERE `tag` ='qq';
UPDATE cdb_bbcodes SET prompt = '请输入 Real 音频的 URL:' WHERE `tag` ='ra';
UPDATE cdb_bbcodes SET prompt = '请输入 Real 音频或视频的 URL:' WHERE `tag` ='rm';
UPDATE cdb_bbcodes SET prompt = '请输入 Windows media 音频的 URL:' WHERE `tag` ='wma';
UPDATE cdb_bbcodes SET prompt = '请输入 Windows media 音频或视频的 URL:' WHERE `tag` ='wmv';
UPDATE cdb_bbcodes SET replacement='<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="400" height="30"><param name="src" value="{1}" /><param name="controls" value="controlpanel" /><param name="console" value="{RANDOM}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" console="{RANDOM}" controls="ControlPanel" width="400" height="30"></embed></object>' WHERE tag='ra';
UPDATE cdb_bbcodes SET replacement='<br /><object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="480" height="360"><param name="src" value="{1}" /><param name="controls" value="imagewindow" /><param name="console" value="{MD5}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" controls="IMAGEWINDOW" console="{MD5}" width="480" height="360"></embed></object><br /><object classid="CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="480" height="32"><param name="src" value="{1}" /><param name="controls" value="controlpanel" /><param name="console" value="{MD5}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" console="{MD5}" width="480" height="32"></embed></object><br />'  WHERE tag='rm';
UPDATE cdb_bbcodes SET replacement='<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="260" height="64"><param name="autostart" value="0" /><param name="url" value="{1}" /><embed src="{1}" autostart="0" type="video/x-ms-wmv" width="260" height="42"></embed></object>'  WHERE tag='wma';
UPDATE cdb_bbcodes SET replacement='<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="500" height="400"><param name="autostart" value="0" /><param name="url" value="{1}" /><embed src="{1}" autostart="0" type="video/x-ms-wmv" width="500" height="400"></embed></object>'  WHERE tag='wmv';
UPDATE cdb_crons SET filename = 'threadexpiries_hourly.inc.php' WHERE filename = 'threadexpiries_daily.inc.php';

EOT;

$upgrade4 = <<<EOT

UPDATE cdb_posts SET invisible='-2' WHERE invisible='2';

EOT;

$upgrade6 = <<<EOT

DROP TABLE IF EXISTS cdb_spacecaches;
CREATE TABLE cdb_spacecaches (
  uid mediumint(8) unsigned NOT NULL default '0',
  variable varchar(20) NOT NULL,
  value text NOT NULL,
  expiration int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (uid, variable)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_memberspaces;
CREATE TABLE cdb_memberspaces (
  uid mediumint(8) unsigned NOT NULL default '0',
  style char(20) NOT NULL,
  description char(100) NOT NULL,
  layout char(200) NOT NULL,
  side tinyint(1) NOT NULL default '0',
  PRIMARY KEY (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_attachpaymentlog;
CREATE TABLE cdb_attachpaymentlog (
  uid mediumint(8) unsigned NOT NULL default '0',
  aid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  amount int(10) unsigned NOT NULL default '0',
  netamount int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (aid,uid),
  KEY uid (uid),
  KEY authorid (authorid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magics;
CREATE TABLE cdb_magics (
  magicid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  type tinyint(3) NOT NULL default '0',
  name varchar(50) NOT NULL,
  identifier varchar(40) NOT NULL,
  description varchar(255) NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  price mediumint(8) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  salevolume smallint(6) unsigned NOT NULL default '0',
  supplytype tinyint(1) NOT NULL default '0',
  supplynum smallint(6) unsigned NOT NULL default '0',
  weight tinyint(3) unsigned NOT NULL default '1',
  filename varchar(50) NOT NULL,
  magicperm text NOT NULL,
  PRIMARY KEY  (magicid),
  UNIQUE KEY identifier (identifier),
  KEY displayorder (available,displayorder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magiclog;
CREATE TABLE cdb_magiclog (
  uid mediumint(8) unsigned NOT NULL default '0',
  magicid smallint(6) unsigned NOT NULL default '0',
  action tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  amount smallint(6) unsigned NOT NULL default '0',
  price mediumint(8) unsigned NOT NULL default '0',
  targettid mediumint(8) unsigned NOT NULL default '0',
  targetpid int(10) unsigned NOT NULL default '0',
  targetuid mediumint(8) unsigned NOT NULL default '0',
  KEY uid (uid,dateline),
  KEY targetuid (targetuid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magicmarket;
CREATE TABLE cdb_magicmarket (
  mid smallint(6) unsigned NOT NULL auto_increment,
  magicid smallint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL,
  price mediumint(8) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY (mid),
  KEY num (magicid,num),
  KEY price (magicid,price),
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_membermagics;
CREATE TABLE cdb_membermagics (
  uid mediumint(8) unsigned NOT NULL default '0',
  magicid smallint(6) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_projects;
CREATE TABLE cdb_projects (
  id smallint(6) unsigned NOT NULL auto_increment auto_increment,
  name varchar(50) NOT NULL,
  type varchar(10) NOT NULL,
  description varchar(255) NOT NULL,
  value mediumtext NOT NULL,
  PRIMARY KEY (id),
  KEY type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_itempool;
CREATE TABLE cdb_itempool (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  type tinyint(1) unsigned NOT NULL,
  question text NOT NULL,
  answer varchar(50) NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_faqs;
CREATE TABLE cdb_faqs (
  id smallint(6) NOT NULL auto_increment,
  fpid smallint(6) unsigned NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  identifier varchar(20) NOT NULL,
  keyword varchar(50) NOT NULL,
  title varchar(50) NOT NULL,
  message text NOT NULL,
  PRIMARY KEY (id),
  KEY displayplay (displayorder)
) TYPE=MyISAM  AUTO_INCREMENT=35;

EOT;

$upgrade7 = <<<EOT

INSERT INTO cdb_magics (magicid, available, type, name, identifier, description, displayorder, price, num, salevolume, supplytype, supplynum, weight, filename, magicperm) VALUES
	('1','1','1','变色卡','CCK','可以变换主题的颜色,并保存24小时','0','10','999','0','0','0','30','magic_color.inc.php',''),
	('2','1','3','金钱卡','MOK','可以随机获得一些金币','0','10','999','0','0','0','30','magic_money.inc.php',''),
	('3','1','1','IP卡','SEK','可以查看帖子作者的IP','0','15','999','0','0','0','30','magic_see.inc.php',''),
	('4','1','1','提升卡','UPK','可以提升某个主题','0','10','999','0','0','0','30','magic_up.inc.php',''),
	('5','1','1','置顶卡','TOK','可以将主题置顶24小时','0','20','999','0','0','0','40','magic_top.inc.php',''),
	('6','1','1','悔悟卡','REK','可以删除自己的帖子','0','10','999','0','0','0','30','magic_del.inc.php',''),
	('7','1','2','狗仔卡','RTK','查看某个用户是否在线','0','15','999','0','0','0','30','magic_reporter.inc.php',''),
	('8','1','1','沉默卡','CLK','24小时内不能回复','0','15','999','0','0','0','30','magic_close.inc.php',''),
	('9','1','1','喧嚣卡','OPK','使贴子可以回复','0','15','999','0','0','0','30','magic_open.inc.php',''),
	('10','1','1','隐身卡','YSK','可以将自己的帖子匿名','0','20','999','0','0','0','30','magic_hidden.inc.php',''),
	('11','1','1','恢复卡','CBK','将匿名恢复为正常显示的用户名,匿名终结者','0','15','999','0','0','0','20','magic_renew.inc.php',''),
	('12','1','1','移动卡','MVK','可将自已的帖子移动到其他版面（隐含、特殊限定版面除外）','0','50','989','0','0','0','50','magic_move.inc.php','');

INSERT INTO cdb_projects (name, type, description, value) VALUES
	('技术性论坛', 'extcredit', '如果您不希望会员通过灌水、页面访问等方式得到积分，而是需要发布一些技术性的帖子获得积分。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:49:\"posts*0.5+digestposts*5+extcredits1*2+extcredits2\";s:13:\"creditspolicy\";s:299:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1444:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
  	('娱乐性论坛', 'extcredit', '此类型论坛的会员可以通过发布一些评论、回复等获得积分，同时扩大论坛的访问量。更重要的是希望会员发布一些有价值的娱乐新闻等。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:81:\"posts+digestposts*5+oltime*5+pageviews/1000+extcredits1*2+extcredits2+extcredits3\";s:13:\"creditspolicy\";s:315:\"a:12:{s:4:\"post\";a:1:{i:1;i:1;}s:5:\"reply\";a:1:{i:2;i:1;}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1036:\"a:8:{i:1;a:6:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:2;a:6:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:3;a:6:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:4;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:5;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:6;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:7;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:8;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}}\";}'),
  	('动漫、摄影类论坛', 'extcredit', '此类型论坛需要更多的图片附件发布给广大会员，因此增加一项扩展积分：魅力。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:86:\"posts+digestposts*2+pageviews/2000+extcredits1*2+extcredits2+extcredits3+extcredits4*3\";s:13:\"creditspolicy\";s:324:\"a:12:{s:4:\"post\";a:1:{i:2;i:1;}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:1:{i:4;i:3;}s:9:\"getattach\";a:1:{i:2;i:-2;}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1454:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:4:\"魅力\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
 	('文章、小说类论坛', 'extcredit', '此类型的论坛更重视会员的原创文章或者是转发的文章，因此增加一项扩展积分：文采。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:57:\"posts+digestposts*8+extcredits2+extcredits3+extcredits4*2\";s:13:\"creditspolicy\";s:307:\"a:12:{s:4:\"post\";a:1:{i:2;i:1;}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:4;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1454:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:4:\"文采\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('调研性论坛', 'extcredit', '此类型论坛更期望的是得到会员的建议和意见，主要是通过投票的方式体现会员的建议，因此增加一项积分策略为：参加投票，增加一项扩展积分为：积极性。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:63:\"posts*0.5+digestposts*2+extcredits1*2+extcredits3+extcredits4*2\";s:13:\"creditspolicy\";s:306:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:8;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:1:{i:4;i:5;}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1456:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:6:\"积极性\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('贸易性论坛', 'extcredit', '此类型论坛更注重的是会员之间的交易，因此使用积分策略：交易成功，增加一项扩展积分：诚信度。', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:55:\"posts+digestposts+extcredits1*2+extcredits3+extcredits4\";s:13:\"creditspolicy\";s:306:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:5;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:1:{i:4;i:6;}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1456:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"威望\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"金钱\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"贡献\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:6:\"诚信度\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('坛内事务类版块', 'forum', '该板块设置了不允许其他模块共享，以及设置了需要很高的权限才能浏览该版块。也适合于保密性高版块。', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"0\";s:16:\"allowpostspecial\";s:1:\"0\";s:14:\"alloweditrules\";s:1:\"1\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"0\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:7:\"	1	2	3	\";s:8:\"postperm\";s:7:\"	1	2	3	\";s:9:\"replyperm\";s:7:\"	1	2	3	\";s:13:\"getattachperm\";s:7:\"	1	2	3	\";s:14:\"postattachperm\";s:7:\"	1	2	3	\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('技术交流类版块', 'forum', '该设置开启了主题缓存系数。其他的权限设置级别较低。', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"5\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:0:\"\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('发布公告类版块', 'forum', '该设置开启了发帖审核，限制了允许发帖的用户组。', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"1\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"1\";s:6:\"jammer\";s:1:\"1\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:1:\"0\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"0\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:7:\"	1	2	3	\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('发起活动类版块', 'forum', '该类型设置里发起主题一个月之后会自动关闭主题。', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"9\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"1\";s:9:\"autoclose\";s:2:\"30\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:22:\"	1	2	3	11	12	13	14	15	\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('娱乐灌水类版块', 'forum', '该设置了主题缓存系数，开启了所有的特殊主题按钮。', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:2:\"15\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:0:\"\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}');

INSERT INTO cdb_faqs (id, fpid, displayorder, identifier, keyword, title, message) VALUES
	('1', '0', '1', '', '', '用户须知', ''),
	('2', '1', '1', '', '', '我必须要注册吗？', '这取决于管理员如何设置 Discuz! 论坛的用户组权限选项，您甚至有可能必须在注册成正式用户后后才能浏览帖子。当然，在通常情况下，您至少应该是正式用户才能发新帖和回复已有帖子。请 <a href="register.php" target="_blank">点击这里</a> 免费注册成为我们的新用户！\r\n<br><br>强烈建议您注册，这样会得到很多以游客身份无法实现的功能。'),
	('3', '1', '2', 'login', '登录帮助', '我如何登录论坛？', '如果您已经注册成为该论坛的会员，哪么您只要通过访问页面右上的<a href="logging.php?action=login" target="_blank">登录</a>，进入登陆界面填写正确的用户名和密码（如果您设有安全提问，请选择正确的安全提问并输入对应的答案），点击“提交”即可完成登陆如果您还未注册请点击这里。<br><br>\r\n如果需要保持登录，请选择相应的 Cookie 时间，在此时间范围内您可以不必输入密码而保持上次的登录状态。'),
	('4', '1', '3', '', '', '忘记我的登录密码，怎么办？', '当您忘记了用户登录的密码，您可以通过注册时填写的电子邮箱重新设置一个新的密码。点击登录页面中的 <a href="member.php?action=lostpasswd" target="_blank">取回密码</a>，按照要求填写您的个人信息，系统将自动发送重置密码的邮件到您注册时填写的 Email 信箱中。如果您的 Email 已失效或无法收到信件，请与论坛管理员联系。'),
	('5', '0', '2', '', '', '帖子相关操作', ''),
	('6', '0', '3', '', '', '基本功能操作', ''),
	('7', '0', '4', '', '', '其他相关问题', ''),
	('8', '1', '4', '', '', '我如何使用个性化头像', '在<a href="memcp.php" target="_blank">控制面板</a>中的“编辑个人资料”，有一个“头像”的选项，可以使用论坛自带的头像或者自定义的头像。'),
	('9', '1', '5', '', '', '我如何修改登录密码', '在<a href="memcp.php" target="_blank">控制面板</a>中的“编辑个人资料”，填写“原密码”，“新密码”，“确认新密码”。点击“提交”，即可修改。'),
	('10', '1', '6', '', '', '我如何使用个性化签名和昵称', '在<a href="memcp.php" target="_blank">控制面板</a>中的“编辑个人资料”，有一个“昵称”和“个人签名”的选项，可以在此设置。'),
	('11', '5', '1', '', '', '我如何发表新主题', '在论坛版块中，点“新帖”，如果有权限，您可以看到有“投票，悬赏，活动，交易”，点击即可进入功能齐全的发帖界面。\r\n<br><br>注意：一般论坛都设置为高级别的用户组才能发布这四类特殊主题。如发布普通主题，直接点击“新帖”，当然您也可以使用版块下面的“快速发帖”发表新帖(如果此选项打开)。一般论坛都设置为需要登录后才能发帖。'),
	('12', '5', '2', '', '', '我如何发表回复', '回复有分三种：第一、贴子最下方的快速回复； 第二、在您想回复的楼层点击右下方“回复”； 第三、完整回复页面，点击本页“新帖”旁边的“回复”。'),
	('13', '5', '3', '', '', '我如何编辑自己的帖子', '在帖子的右下角，有编辑，回复，报告等选项，点击编辑，就可以对帖子进行编辑。'),
	('14', '5', '4', '', '', '我如何出售购买主题', '<li>出售主题：\r\n当您进入发贴界面后，如果您所在的用户组有发买卖贴的权限，在“售价(金钱)”后面填写主题的价格，这样其他用户在查看这个帖子的时候就需要进入交费的过程才可以查看帖子。</li>\r\n<li>购买主题：\r\n浏览你准备购买的帖子，在帖子的相关信息的下面有[查看付款记录] [购买主题] [返回上一页] \r\n等链接，点击“购买主题”进行购买。</li>'),
	('15', '5', '5', '', '', '我如何出售购买附件', '<li>上传附件一栏有个售价的输入框，填入出售价格即可实现需要支付才可下载附件的功能。</li>\r\n<li>点击帖子中[购买附件]按钮或点击附件的下载链接会跳转至附件购买页面，确认付款的相关信息后点提交按钮，即可得到附件的下载权限。只需购买一次，就有该附件的永远下载权限。</li>'),
	('16', '5', '6', '', '', '我如何上传附件', '<li>发表新主题的时候上传附件，步骤为：写完帖子标题和内容后点上传附件右方的浏览，然后在本地选择要上传附件的具体文件名，最后点击发表话题。</li>\r\n<li>发表回复的时候上传附件，步骤为：写完回复楼主的内容，然后点上传附件右方的浏览，找到需要上传的附件，点击发表回复。</li>'),
	('17', '5', '7', '', '', '我如何实现发帖时图文混排效果', '<li>先把文章写在帖子里，然后把相关的图片以附件的形式上传。</li>\r\n<li>编辑帖子，找到帖子下方的附件信息，点击aid栏目所对应的数字，论坛会自动把附件的内容以[attach]xx[/attach]的形式插入到当前光标所在的位置。</li>'),
	('18', '5', '8', 'discuzcode', 'Discuz!代码', '我如何使用Discuz!代码', '<table width="100%" cellpadding="2" cellspacing="2">\r\n  <tr>\r\n    <th width="50%">Discuz!代码</th>\r\n    <th width="402">效果</th>\r\n  </tr>\r\n  <tr>\r\n    <td>[b]粗体文字 Abc[/b]</td>\r\n    <td><strong>粗体文字 Abc</strong></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[i]斜体文字 Abc[/i]</td>\r\n    <td><em>斜体文字 Abc</em></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[u]下划线文字 Abc[/u]</td>\r\n    <td><u>下划线文字 Abc</u></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[color=red]红颜色[/color]</td>\r\n    <td><font color="red">红颜色</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[size=3]文字大小为 3[/size] </td>\r\n    <td><font size="3">文字大小为 3</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[font=仿宋]字体为仿宋[/font] </td>\r\n    <td><font face"仿宋">字体为仿宋</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[align=Center]内容居中[/align] </td>\r\n    <td><div align="center">内容居中</div></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[url]http://www.comsenz.com[/url]</td>\r\n    <td><a href="http://www.comsenz.com" target="_blank">http://www.comsenz.com</a>（超级链接）</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[url=http://www.Discuz.net]Discuz! 论坛[/url]</td>\r\n    <td><a href="http://www.Discuz.net" target="_blank">Discuz! 论坛</a>（超级链接）</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[email]myname@mydomain.com[/email]</td>\r\n    <td><a href="mailto:myname@mydomain.com">myname@mydomain.com</a>（E-mail链接）</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[email=support@discuz.net]Discuz! 技术支持[/email]</td>\r\n    <td><a href="mailto:support@discuz.net">Discuz! 技术支持（E-mail链接）</a></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[quote]Discuz! Board 是由康盛创想（北京）科技有限公司开发的论坛软件[/quote] </td>\r\n    <td><div style="font-size: 12px"><br><br><div class="msgheader">QUOTE:</div><div class="msgborder">原帖由 <i>admin</i> 于 2006-12-26 08:45 发表<br>Discuz! Board 是由康盛创想（北京）科技有限公司开发的论坛软件</div></td>\r\n  </tr>\r\n   <tr>\r\n    <td>[code]Discuz! Board 是由康盛创想（北京）科技有限公司开发的论坛软件[/code] </td>\r\n    <td><div style="font-size: 12px"><br><br><div class="msgheader">CODE:</div><div class="msgborder">Discuz! Board 是由康盛创想（北京）科技有限公司开发的论坛软件</div></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[hide]隐藏内容 Abc[/hide]</td>\r\n    <td>效果:只有当浏览者回复本帖时，才显示其中的内容，否则显示为“<b>**** 隐藏信息 跟帖后才能显示 *****</b>”</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[hide=20]隐藏内容 Abc[/hide]</td>\r\n    <td>效果:只有当浏览者积分高于 20 点时，才显示其中的内容，否则显示为“<b>**** 隐藏信息 积分高于 20 点才能显示 ****</b>”</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[list][*]列表项 #1[*]列表项 #2[*]列表项 #3[/list]</td>\r\n    <td><ul>\r\n      <li>列表项 ＃1</li>\r\n      <li>列表项 ＃2</li>\r\n      <li>列表项 ＃3 </li>\r\n    </ul></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[fly]飞行的效果[/fly]</td>\r\n    <td><marquee scrollamount="3" behavior="alternate" width="90%">飞行的效果</marquee></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[flash]Flash网页地址 [/flash] </td>\r\n    <td>帖子内嵌入 Flash 动画</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[qq]123456789[/qq]</td>\r\n    <td>在帖子内显示 QQ 在线状态，点这个图标可以和他（她）聊天</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[ra]ra网页地址[/ra]</td>\r\n    <td>帖子内嵌入 Real 音频</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[rm]rm网页地址[/rm] </td>\r\n    <td>帖子内嵌入 Real 音频或视频</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[wma]wma网页地址[/wma] </td>\r\n    <td>帖子内嵌入 Windows media 音频</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[wmv]wmv网页地址[/wmv]</td>\r\n    <td>帖子内嵌入 Windows media 音频或视频</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[img]http://www.discuz.net/images/default/logo.gif[/img] </td>\r\n    <td>帖子内显示为：<img src="http://www.discuz.net/images/default/logo.gif"></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[img=88,31]http://www.discuz.net/images/logo.gif[/img] </td>\r\n    <td>帖子内显示为：<img src="http://www.discuz.net/images/logo.gif"></td>\r\n  </tr>\r\n</table>'),
	('19', '6', '1', '', '', '我如何使用短消息功能', '您登录后，点击导航栏上的短消息按钮，即可进入短消息管理。\r\n点击[发送短消息]按钮，在"发送到"后输入收信人的用户名，填写完标题和内容，点提交(或按 Ctrl+Enter 发送)即可发出短消息。\r\n<br><br>如果要保存到发件箱，以在提交前勾选"保存到发件箱中"前的复选框。\r\n<ul>\r\n<li>点击收件箱可打开您的收件箱查看收到的短消息。</li>\r\n<li>点击发件箱可查看保存在发件箱里的短消息。 </li>\r\n<li>点击消息跟踪来查看对方是否已经阅读您的短消息。 </li>\r\n<li>点击搜索短消息就可通过关键字，发信人，收信人，搜索范围，排序类型等一系列条件设定来找到您需要查找的短消息。 </li>\r\n<li>点击导出短消息可以将自己的短消息导出htm文件保存在自己的电脑里。 </li>\r\n<li>点击忽略列表可以设定忽略人员，当这些被添加的忽略用户给您发送短消息时将不予接收。</li>\r\n</ul>'),
	('20', '6', '2', '', '', '我如何向好友群发短消息', '登录论坛后，点击短消息，然后点发送短消息，如果有好友的话，好友群发后面点击全选，可以给所有的好友群发短消息。'),
	('21', '6', '3', '', '', '我如何查看论坛会员数据', '点击导航栏上面的会员，然后显示的是此论坛的会员数据。注：需要论坛管理员开启允许你查看会员资料才可看到。'),
	('22', '6', '4', '', '', '我如何使用搜索', '点击导航栏上面的搜索，输入搜索的关键字并选择一个范围，就可以检索到您有权限访问论坛中的相关的帖子。'),
	('23', '6', '5', '', '', '我如何使用“我的”功能', '<li>会员必须首先<a href="logging.php?action=login" target="_blank">登录</a>，没有用户名的请先<a href="register.php" target="_blank">注册</a>；</li>\r\n<li>登录之后在论坛的左上方会出现一个“我的”的超级链接，点击这个链接之后就可进入到有关于您的信息。</li>'),
	('24', '7', '1', '', '', '我如何向管理员报告帖子', '打开一个帖子，在帖子的右下角可以看到：“编辑”、“引用”、“报告”、“评分”、“回复”等等几个按钮，点击其中的“报告”按钮进入报告页面，填写好“我的意见”，单击“报告”按钮即可完成报告某个帖子的操作。'),
	('25', '7', '2', '', '', '我如何“打印”，“推荐”，“订阅”，“收藏”帖子', '当你浏览一个帖子时，在它的右上角可以看到：“打印”、“推荐”、“订阅”、“收藏”，点击相对应的文字连接即可完成相关的操作。'),
	('26', '7', '3', '', '', '我如何设置论坛好友', '设置论坛好友有3种简单的方法。\r\n<ul>\r\n<li>当您浏览帖子的时候可以点击“发表时间”右侧的“加为好友”设置论坛好友。</li>\r\n<li>当您浏览某用户的个人资料时，可以点击头像下方的“加为好友”设置论坛好友。</li>\r\n<li>您也可以在控制面板中的好友列表增加您的论坛好友。</li>\r\n<ul>'),
	('27', '7', '4', '', '', '我如何使用RSS订阅', '在论坛的首页和进入版块的页面的右上角就会出现一个rss订阅的小图标<img src="images/common/xml.gif" border="0">，鼠标点击之后将出现本站点的rss地址，你可以将此rss地址放入到你的rss阅读器中进行订阅。'),
	('28', '7', '5', '', '', '我如何清除Cookies', 'cookie是由浏览器保存在系统内的，在论坛的右下角提供有"清除 Cookies"的功能，点击后即可帮您清除系统内存储的Cookies。 <br><br>\r\n以下介绍3种常用浏览器的Cookies清除方法(注：此方法为清除全部的Cookies,请谨慎使用)\r\n<ul>\r\n<li>Internet Explorer: 工具（选项）内的Internet选项→常规选项卡内，IE6直接可以看到删除Cookies的按钮点击即可，IE7为“浏 览历史记录”选项内的删除点击即可清空Cookies。对于Maxthon,腾讯TT等IE核心浏览器一样适用。 </li>\r\n<li>FireFox:工具→选项→隐私→Cookies→显示Cookie里可以对Cookie进行对应的删除操作。 </li>\r\n<li>Opera:工具→首选项→高级→Cookies→管理Cookies即可对Cookies进行删除的操作。</li>\r\n</ul>'),
	('29', '7', '6', '', '', '我如何联系管理员', '您可以通过论坛底部右下角的“联系我们”链接快速的发送邮件与我们联系。也可以通过管理团队中的用户资料发送短消息给我们。'),
	('30', '7', '7', '', '', '我如何开通个人空间', '如果您有权限开通“我的个人空间”，当用户登录论坛以后在论坛首页，用户名的右方点击开通我的个人空间，进入个人空间的申请页面。'),
	('31', '7', '8', '', '', '我如何将自己的主题加入个人空间', '如果您有权限开通“我的个人空间”，在您发表的主题上方点击“加入个人空间”，您发表的主题以及回复都会加入到您空间的日志里。'),
	('32', '5', '9', 'smilies', 'Smilies', '我如何使用Smilies代码', 'Smilies是一些用字符表示的表情符号，如果打开 Smilies 功能，Discuz! 会把一些符号转换成小图像，显示在帖子中，更加美观明了。目前支持下面这些 Smilies：<br><br>\r\n<table cellspacing="0" cellpadding="4" width="30%" align="center">\r\n<tr><th width="25%" align="center">表情符号</td>\r\n<th width="75%" align="center">对应图像</td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:)</td>\r\n<td width="75%" align="center"><img src="images/smilies/smile.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:(</td>\r\n<td width="75%" align="center"><img src="images/smilies/sad.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:D</td>\r\n<td width="75%" align="center"><img src="images/smilies/biggrin.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:\'(</td>\r\n<td width="75%" align="center"><img src="images/smilies/cry.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:@</td>\r\n<td width="75%" align="center"><img src="images/smilies/huffy.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:o</td>\r\n<td width="75%" align="center"><img src="images/smilies/shocked.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:P</td>\r\n<td width="75%" align="center"><img src="images/smilies/tongue.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:$</td>\r\n<td width="75%" align="center"><img src="images/smilies/shy.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">；P</td>\r\n<td width="75%" align="center"><img src="images/smilies/titter.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:L</td>\r\n<td width="75%" align="center"><img src="images/smilies/sweat.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:Q</td>\r\n<td width="75%" align="center"><img src="images/smilies/mad.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:lol</td>\r\n<td width="75%" align="center"><img src="images/smilies/lol.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:hug:</td>\r\n<td width="75%" align="center"><img src="images/smilies/hug.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:victory:</td>\r\n<td width="75%" align="center"><img src="images/smilies/victory.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:time:</td>\r\n<td width="75%" align="center"><img src="images/smilies/time.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:kiss:</td>\r\n<td width="75%" align="center"><img src="images/smilies/kiss.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:handshake</td>\r\n<td width="75%" align="center"><img src="images/smilies/handshake.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:call:</td>\r\n<td width="75%" align="center"><img src="images/smilies/call.gif" alt=""></td>\r\n</tr>\r\n</table>\r\n</div></div>\r\n<br>'),
	('33','0','5','','','论坛高级功能使用',''),
	('34','33','0','forwardmessagelist','','论坛快速跳转关键字列表','Discuz! 支持自定义快速跳转页面，当某些操作完成后，可以不显示提示信息，直接跳转到新的页面，从而方便用户进行下一步操作，避免等待。 在实际使用当中，您根据需要，把关键字添加到快速跳转设置里面(后台 -- 基本设置 --  界面与显示方式 -- [<a href=\"admincp.php?action=settings&do=styles&frames=yes\" target=\"_blank\">提示信息跳转设置</a> ])，让某些信息不显示而实现快速跳转。以下是 Discuz! 当中的一些常用信息的关键字:\r\n</br></br>\r\n\r\n<table width=\"400\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"msgborder\" align=\"center\">\r\n  <tr class=\"msgheader\">\r\n    <td width=\"50%\">关键字</td>\r\n    <td width=\"50%\">提示信息页面或者作用</td>\r\n  </tr>\r\n  <tr>\r\n    <td>login_succeed</td>\r\n    <td>登录成功</td>\r\n  </tr>\r\n  <tr>\r\n    <td>logout_succeed</td>\r\n    <td>退出登录成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>thread_poll_succeed</td>\r\n    <td>投票成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>thread_rate_succeed</td>\r\n    <td>评分成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>register_succeed</td>\r\n    <td>注册成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>usergroups_join_succeed</td>\r\n    <td>加入扩展组成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td height=\"22\">usergroups_exit_succeed</td>\r\n    <td>退出扩展组成功</td>\r\n  </tr>\r\n  <tr>\r\n    <td>usergroups_update_succeed</td>\r\n    <td>更新扩展组成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>buddy_update_succeed</td>\r\n    <td>好友更新成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_edit_succeed</td>\r\n    <td>编辑帖子成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_edit_delete_succeed</td>\r\n    <td>删除帖子成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_reply_succeed</td>\r\n    <td>回复成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_newthread_succeed</td>\r\n    <td>发表新主题成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_reply_blog_succeed</td>\r\n    <td>文集评论发表成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_newthread_blog_succeed</td>\r\n    <td>blog 发表成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>&nbsp;profile_avatar_succeed</td>\r\n    <td>头像设置成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>&nbsp;profile_succeed</td>\r\n    <td>个人资料更新成功</td>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_send_succeed</td>\r\n    <td>短消息发送成功</td>\r\n  </tr>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_delete_succeed</td>\r\n    <td>短消息删除成功</td>\r\n  </tr>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_ignore_succeed</td>\r\n    <td>短消息忽略列表更新</td>\r\n  </tr>\r\n    <tr>\r\n    <td>admin_succeed</td>\r\n    <td>管理操作成功〔注意：设置此关键字后，所有管理操作完毕都将直接跳转〕</td>\r\n  </tr>\r\n    <tr>\r\n    <td>admin_succeed_next&nbsp;</td>\r\n    <td>管理成功并将跳转到下一个管理动作</td>\r\n  </tr> \r\n    <tr>\r\n    <td>search_redirect</td>\r\n    <td>搜索完成，进入搜索结果列表</td>\r\n  </tr>\r\n</table>');

EOT;

$upgrademsg = array(
	1 => '论坛升级第 1 步: 增加基本设置<br><br>',
	2 => '论坛升级第 2 步: 调整论坛数据表结构<br><br>',
	3 => '论坛升级第 3 步: 更新部分数据<br><br>',
	4 => '论坛升级第 4 步: 调整审核信息<br><br>',
	5 => '论坛升级第 5 步: 调整部分数据<br><br>',
	6 => '论坛升级第 6 步: 新增数据表<br><br>',
	7 => '论坛升级第 7 步: 插入论坛相关数据<br><br>',
	8 => '论坛升级第 8 步: SupeSite相关数据升级<br><br>',
	9 => '论坛升级第 9 步: 其他相关数据升级<br><br>',
	10 => '论坛升级第 10 步: 升级全部完毕<br><br>',
);

$errormsg = '';
if(!isset($dbhost)) {
	showerror("<span class=error>没有找到 config.inc.php 文件!</span><br>请确认您已经上传了所有 $version_new 文件");
} elseif(!isset($cookiepre)) {
	showerror("<span class=error>config.inc.php 版本错误!</span><br>请上传 $version_new 的 config.inc.php，并调整好数据库设置然后重新进行升级");
} elseif(!$dblink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
	showerror("<span class=error>config.inc.php 配置错误!</span><br>请修改 config.inc.php 当中关于数据库的设置，然后上传到论坛目录，重新开始升级");
}

@mysql_close($dblink);
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$action) {

	if(!$tableinfo = loadtable('threads')) {
		showerror("<span class=error>无法找到 Discuz! 论坛数据表!</span><br>请修改 config.inc.php 当中关于数据库的设置，然后上传到论坛目录，重新开始升级");
	} elseif($db->version() > '4.1') {
		$old_dbcharset = substr($tableinfo['subject']['Collation'], 0, strpos($tableinfo['subject']['Collation'], '_'));
		if($old_dbcharset <> $dbcharset) {
			showerror("<span class=error>config.inc.php 数据库字符集设置错误!</span><br>".
				"<li>原来的字符集设置为：$old_dbcharset".
				"<li>当前使用的字符集为：$dbcharset".
				"<li>建议：修改 config.inc.php， 将其中的 <b>\$dbcharset = ''</b> 或者 <b>\$dbcharset = '$dbcharset'</b> 修改为： <b>\$dbcharset = '$old_dbcharset'</b>".
				"<li>修改完毕后上传 config.inc.php，然后重新进行升级"
			);
		}
	}

	echo <<< EOT
<span class="red">
升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>
升级之前务必备份数据库资料，否则升级失败无法恢复<br></span><br>
正确的升级方法为:
<ol>
	<li>关闭原有论坛,上传 $version_new 的全部文件和目录, 覆盖服务器上的 $version_old
	<li>上传升级程序到论坛目录中，<b>重新配置好 config.inc.php</b>
	<li>运行本程序,直到出现升级完成的提示
	<li>如果中途失败，请使用Discuz!工具箱（./utilities/tools.php）里面的数据恢复工具恢复备份, 去除错误后重新运行本程序
</ol>
<a href="$PHP_SELF?action=upgrade&step=1"><font size="2" color="red"><b>&gt;&gt;&nbsp;如果您已确认完成上面的步骤,请点这里升级</b></font></a>
<br><br>
EOT;
	showfooter();

} else {

	$step = intval($step);
	echo '&gt;&gt;'.$upgrademsg[$step];
	flush();

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		if(!dir_writeable('./forumdata/logs')) {
			showerror('升级检测失败，无法建立目录 /forumdata/logs，请手工建立此目录，然后重新运行升级程序');
		} else {
			$logfilearray = array('cplog.php', 'illegallog.php', 'ratelog.php', 'medalslog.php', 'banlog.php', 'runwizardlog.php', 'errorlog.php', 'modslog.php', 'viewcount.log', 'dberror.log');
			foreach($logfilearray as $filename) {
				@copy('./forumdata/'.$filename, './forumdata/logs/'.$filename);
				@unlink('./forumdata/'.$filename);
			}
		}

		runquery($upgrade1);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

			echo "升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} :";
			$successed = upgradetable($upgradetable[$start]);

			if($successed === TRUE) {
				echo ' <font color=green>OK</font><br>';
			} elseif($successed === FALSE) {
				echo ' <font color=red>ERROR</font><br>';
			} elseif($successed == 'TABLE NOT EXISTS') {
				showerror('<span class=red>数据表不存在</span>升级无法继续，请确认您的论坛版本是否正确!</font><br>');
			}
		}

		$start ++;
		if(isset($upgradetable[$start])) {
			redirect("?action=upgrade&step=$step&start=$start");
		}

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade2);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		runquery($upgrade4);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 5) {

		$db->query("DELETE FROM {$tablepre}crons WHERE type='system' AND filename='magics_daily.inc.php'", 'SILENT');
		$db->query("INSERT INTO {$tablepre}crons (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (1, 'system', '道具自动补货', 'magics_daily.inc.php', $timestamp, $timestamp, -1, -1, 0, '0')", "SILENT");
		$db->query("INSERT INTO {$tablepre}crons (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (1, 'system', '每日验证问答更新', 'secqaa_daily.inc.php', 0, 0, -1, -1, 6, '0')", "SILENT");
		
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='msgbigsize'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='msgsmallsize'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='frameswitch'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='framebg'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='framebgcolor'", 'SILENT');

		$styleids = array();
		$query = $db->query("SELECT styleid FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$styleids[] = $style['styleid'];
		}

		foreach ($styleids as $styleid) {
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'msgbigsize', '')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'msgsmallsize', '')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'frameswitch', 'frame_switch.gif')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'framebg', 'frame_bg.gif')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'framebgcolor', '#E8F2F7')", 'SILENT');
		}

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 6) {

		runquery($upgrade6);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 7) {

		runquery($upgrade7);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 8) {

		$settings = array();
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable LIKE 'supe_%'");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}
		$supe = array(
		'dbmode' => '0',
		'dbhost' => '',
		'dbuser' => '',
		'dbpw' => '',
		'dbname' => '',
		'status' => $settings['supe_status'],
		'tablepre' => $settings['supe_tablepre'],
		'siteurl' => $settings['supe_siteurl'],
		'sitename' => $settings['supe_sitename'],
		'maxupdateusers' => $settings['supe_maxupdateusers'],
		'items' => array(
		'status' => '1',
		'rows' => '4',
		'columns' => '3',
		'orderby' => '1'
		),
		'circlestatus' => '0'
		);

		$supe = addslashes(serialize($supe));

		$db->query("DELETE FROM {$tablepre}settings WHERE `variable` = 'supe_maxupdateusers'");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) values ('supe', '$supe')");

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 9) {

		$backupdir = random(6);
		@mkdir('forumdata/backup_'.$backupdir, 0777);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) values ('backupdir', '$backupdir')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('seccodedata', '".addslashes(serialize(array('loginfailedcount' => 0, 'animator' => 0, 'background' => 1, 'width' => mt_rand(70, 100), 'height' => mt_rand(25, 40))))."')");

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} else {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		echo '<br>恭喜您论坛数据升级成功，接下来请您：<ol><li><b>必删除本程序</b>'.
		'<li>使用管理员身份登录论坛，进入后台，更新缓存'.
		'<li><span class="red">由于 Discuz! 5.5.0 将原 config.inc.php 当中关于附件的设置改为数据库存储，所以如果您的附件目录如果不是默认设置，请进入后台－基本设置－附件设置，进行更改。否则论坛旧贴可能无法找到原有的附件</span>'.
		'<li>进行论坛注册、登录、发贴等常规测试，看看运行是否正常'.
		'<li><b>如果您的论坛开启了 URL 静态化功能，您需要阅读《用户使用说明书》当中的高级应用，调整服务器 rewrite 设置， 否则论坛部分页面会出现无法访问的错误。</b>'.
		'<li>如果您希望启用 <b>'.$version_new.'</b> 提供的新功能，你还需要对于论坛基本设置、栏目、会员组等等进行重新设置</ol><br>'.
		'<b>感谢您选用我们的产品！</b><a href="index.php" target="_blank">您现在可以访问论坛，查看升级情况</a><iframe width="0" height="0" src="index.php"></iframe>';
		showfooter();
	}
}

instfooter();

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	@touch($dir.'/index.htm');
	$directory->close();
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
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

function instfooter() {
	echo '</table></body></html>';
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;

	$query = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function loadtable($table, $force = 0) {
	global $db, $tablepre, $dbcharset;
	static $tables = array();

	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM {$tablepre}$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM {$tablepre}$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function upgradetable($updatesql) {
	global $db, $tablepre, $dbcharset;

	$successed = FALSE;

	if(is_array($updatesql) && !empty($updatesql[0])) {

		list($table, $action, $field, $sql) = $updatesql;

		if($tableinfo = loadtable($table)) {
			$fieldexist = isset($tableinfo[$field]) ? 1 : 0;

			$query = "ALTER TABLE {$tablepre}{$table} ";

			if($action == 'CHANGE') {

				$field2 = trim(substr($sql, 0, strpos($sql, ' ')));
				$field2exist = isset($tableinfo[$field2]);

				if($fieldexist && ($field == $field2 || !$field2exist)) {
					$query .= "CHANGE $field $sql";
				} elseif($fieldexist && $field2exist) {
					$db->query('ALTER TABLE {$tablepre}{$table} DROP $field2', 'SILENT');
					$query .= "CHANGE $field $sql";
				} elseif(!$fieldexist && $fieldexist2) {
					$db->query('ALTER TABLE {$tablepre}{$table} DROP $field2', 'SILENT');
					$query .= "ADD $sql";
				} elseif(!$fieldexist && !$field2exist) {
					$query .= "ADD $sql";
				}
				$successed = $db->query($query);

			} elseif($action == 'ADD') {

				$query .= $fieldexist ? "CHANGE $field $field $sql" :  "ADD $field $sql";
				if($successed = $db->query($query)) {
					$db->query("UPDATE LOW_PRIORITY IGNORE $tablepre{$table} SET $field=NULL", "UNBUFFERED");
				}

			} elseif($action == 'DROP') {
				if($fieldexist) {
					$successed = $db->query("$query DROP $field", "SILENT");
				}
				$successed = TRUE;
			}

		} else {

			$successed = 'TABLE NOT EXISTS';

		}
	}
	return $successed;
}

function showheader() {
	global $version_old, $version_new;

	print <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Discuz! 升级程序( $version_old &gt;&gt; $version_new)</title>
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta http-equiv="MSThemeCompatible" content="Yes">
<style>
a:visited	{color: #FF0000; text-decoration: none}
a:link		{color: #FF0000; text-decoration: none}
a:hover		{color: #FF0000; text-decoration: underline}
body,table,td	{color: #3a4273; font-family: Tahoma, verdana, arial; font-size: 12px; line-height: 20px; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
input		{color: #085878; font-family: Tahoma, verdana, arial; font-size: 12px; background-color: #3a4273; color: #ffffff; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
.install	{font-family: Arial, Verdana; font-size: 14px; font-weight: bold; color: #000000}
.header		{font: 12px Tahoma, Verdana; font-weight: bold; background-color: #3a4273 }
.header	td	{color: #ffffff}
.red		{color: red; font-weight: bold}
.bg1		{background-color: #e3e3ea}
.bg2		{background-color: #eeeef6}
</style>
</head>

<body bgcolor="#3A4273" text="#000000">
<table width="95%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
<tr>
<td>
<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="install" height="30" valign="bottom"><font color="#FF0000">&gt;&gt;</font>
Discuz! 升级程序( $version_old &gt;&gt; $version_new)</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr>
<td align="center">
<b>本升级程序只能从 $version_old 升级到 $version_new ，运行之前，请确认已经上传所有文件，并做好数据备份<br>
升级当中有任何问题请访问技术支持站点 <a href="http://www.discuz.net" target="_blank">http://www.discuz.net</a></b>
</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr><td>
EOT;
}

function showfooter() {
	echo <<< EOT
</td></tr></table></td></tr>
<tr><td height="100%">&nbsp;</td></tr>
</table>
</body>
</html>
EOT;
	exit();
}

function showerror($message, $break = 1) {
	echo '<br><br>'.$message.'<br><br>';
	if($break) showfooter();
}

function redirect($url) {

	echo <<< EOT
<hr size=1>
<script language="JavaScript">
	function redirect() {
		window.location.replace('$url');
	}
	setTimeout('redirect();', 1000);
</script>
<br><br>
&gt;&gt;<a href="$url">浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里</a>
<br><br>
EOT;
	showfooter();
}
?>