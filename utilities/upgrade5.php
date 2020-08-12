<?php

// Upgrade Discuz! Board from 3.1.2 to 4.0.0

@set_time_limit(1000);
define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("请先上传所有新版本的程序文件后再运行本升级程序");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = $_GET['start'];

$authkey = random(15);

$upgrade1 = <<<EOT
UPDATE cdb_settings SET value=REPLACE(REPLACE(value, ',', '\r\n'), ' ', '') WHERE variable='censoruser';
REPLACE INTO cdb_settings (variable, value) VALUES ('starthreshold', 2);
REPLACE INTO cdb_settings (variable, value) VALUES ('seccodestatus', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('ipregctrl', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('statscachelife', 180);
REPLACE INTO cdb_settings (variable, value) VALUES ('karmaratelimit', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('fullmytopics', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('rssstatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('visitedforums', 10);
REPLACE INTO cdb_settings (variable, value) VALUES ('seotitle', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seokeywords', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seodescription', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seohead', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('oltimespan', '10');
REPLACE INTO cdb_settings (variable, value) VALUES ('deletereason', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('showemail', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('subforumsindex', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('edittimelimit', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('maintspans', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('threadmaxpages', 1000);
REPLACE INTO cdb_settings (variable, value) VALUES ('membermaxpages', 100);
REPLACE INTO cdb_settings (variable, value) VALUES ('archiverstatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('authkey', '$authkey');
REPLACE INTO cdb_settings (variable, value) VALUES ('regfloodctrl', '30');
REPLACE INTO cdb_settings (variable, value) VALUES ('transsidstatus', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('globalstick', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('dupkarmarate', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('initcredits', '0,0,0,0,0,0,0,0,0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_securitycode', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_account', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_ratio', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_mincredits', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_maxcredits', '1000');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_maxcreditspermonth', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('losslessdel', '365');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarkstatus', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarktrans', '65');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxsmilies', '3');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxthreadads', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('modreasons', '广告/SPAM
恶意灌水
违规内容
文不对题
重复发帖

我很赞同
精品文章
原创内容');
REPLACE INTO cdb_settings VALUES ('wapstatus', '1');
REPLACE INTO cdb_settings VALUES ('waptpp', '10');
REPLACE INTO cdb_settings VALUES ('wapppp', '5');
REPLACE INTO cdb_settings VALUES ('wapdateformat', 'n/j');
REPLACE INTO cdb_settings VALUES ('wapmps', '500');
REPLACE INTO cdb_settings VALUES ('wapcharset', '2');
REPLACE INTO cdb_settings VALUES ('avatarshowstatus', 1);
REPLACE INTO cdb_settings VALUES ('avatarshowwidth', 135);
REPLACE INTO cdb_settings VALUES ('avatarshowheight', 200);
REPLACE INTO cdb_settings VALUES ('avatarshowpos', 3);
REPLACE INTO cdb_settings VALUES ('avatarshowdefault', 0);
REPLACE INTO cdb_settings VALUES ('avatarshowlink', 1);
REPLACE INTO cdb_settings VALUES ('maxsigrows', 20);
REPLACE INTO cdb_settings VALUES ('bannedmessages', 1);
REPLACE INTO cdb_settings VALUES ('creditsformula', 'extcredits1');
REPLACE INTO cdb_settings VALUES ('ipaccess', '');
REPLACE INTO cdb_settings VALUES ('pvfrequence', '60');
REPLACE INTO cdb_settings VALUES ('adminipaccess', '');
REPLACE INTO cdb_settings VALUES ('stylejump', '0');
REPLACE INTO cdb_settings VALUES ('jsstatus', '0');
REPLACE INTO cdb_settings VALUES ('jscachelife', '1800');
REPLACE INTO cdb_settings VALUES ('jsrefdomains', '');
REPLACE INTO cdb_settings VALUES ('extcredits', '');
REPLACE INTO cdb_settings VALUES ('creditspolicy', '');
REPLACE INTO cdb_settings VALUES ('transfermincredits', '1000');
REPLACE INTO cdb_settings VALUES ('exchangemincredits', '100');
REPLACE INTO cdb_settings VALUES ('creditsformulaexp', '');
REPLACE INTO cdb_settings VALUES ('passport_status', '');
REPLACE INTO cdb_settings VALUES ('passport_key', '');
REPLACE INTO cdb_settings VALUES ('passport_expire', '3600');
REPLACE INTO cdb_settings VALUES ('passport_extcredits', '0');
REPLACE INTO cdb_settings VALUES ('passport_url', '');
REPLACE INTO cdb_settings VALUES ('passport_register_url', '');
REPLACE INTO cdb_settings VALUES ('passport_login_url', '');
REPLACE INTO cdb_settings VALUES ('passport_logout_url', '');
REPLACE INTO cdb_settings VALUES ('creditstrans', '0');
REPLACE INTO cdb_settings VALUES ('creditstax', '0.2');
REPLACE INTO cdb_settings VALUES ('rssttl', '60');
UPDATE cdb_settings SET variable='postbanperiods', value=REPLACE(value, ',', '\r\n') WHERE variable='maintspans';
REPLACE INTO cdb_settings VALUES ('visitbanperiods', '');
REPLACE INTO cdb_settings VALUES ('searchbanperiods', '');
REPLACE INTO cdb_settings VALUES ('postmodperiods', '');
ALTER TABLE cdb_templates DROP charset;
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoostatus', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('searchbox', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('censoremail', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxincperthread', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxchargespan', '0');
DELETE FROM cdb_settings WHERE variable='charset';

DROP TABLE IF EXISTS cdb_plugins;
CREATE TABLE cdb_plugins (
  pluginid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  adminid tinyint(1) unsigned NOT NULL default '0',
  name varchar(40) NOT NULL default '',
  identifier varchar(40) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  datatables varchar(255) NOT NULL default '',
  `directory` varchar(100) NOT NULL default '',
  copyright varchar(100) NOT NULL default '',
  modules text NOT NULL,
  PRIMARY KEY  (pluginid),
  UNIQUE KEY identifier (identifier)
) Type=MyISAM;

DROP TABLE IF EXISTS cdb_pluginvars;
CREATE TABLE cdb_pluginvars (
  pluginvarid mediumint(8) unsigned NOT NULL auto_increment,
  pluginid smallint(6) unsigned NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(100) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  variable varchar(40) NOT NULL default '',
  `type` varchar(20) NOT NULL default 'text',
  `value` text NOT NULL,
  extra text NOT NULL,
  PRIMARY KEY  (pluginvarid),
  KEY pluginid (pluginid)
) TYPE=MyISAM;

DELETE FROM cdb_settings WHERE variable IN ('dosevasive', 'logincredits', 'version', 'modshortcut');
INSERT INTO cdb_bbcodes (id, available, tag, replacement, example, explanation, params, nest) VALUES ('', 1, 'qq', '<a href="http://wpa.qq.com/msgrd?V=1&amp;Uin={1}&amp;Site=[Discuz!]&amp;Menu=yes" target="_blank"><img src="http://wpa.qq.com/pa?p=1:{1}:1" border="0"></a>', '[qq]688888[/qq]', 'Show online status of specified QQ UIN and chat with him/her simply by clicking the icon', 1, 1);
INSERT INTO cdb_bbcodes (id, available, tag, replacement, example, explanation, params, nest) VALUES ('', 0, 'flash', '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="550" height="400">\r\n<param name="allowScriptAccess" value="sameDomain">\r\n<param name="movie" value="{1}">\r\n<param name="quality" value="high">\r\n<param name="bgcolor" value="#ffffff">\r\n<embed src="{1}" quality="high" bgcolor="#ffffff" width="550" height="400" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\r\n</object>', 'Flash Movie', 'Insert flash movie to thread page', 1, 1);
ALTER TABLE cdb_access ADD allowpostattach tinyint(1) NOT NULL;

ALTER TABLE cdb_words CHANGE find find varchar(255) NOT NULL, CHANGE replacement replacement varchar(255) NOT NULL;
ALTER TABLE cdb_stats CHANGE type type char(10) NOT NULL default '', CHANGE var variable char(10) NOT NULL default '';
ALTER TABLE cdb_ranks CHANGE postshigher postshigher MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE cdb_admingroups ADD allowmodpost tinyint(1) NOT NULL default '' AFTER alloweditpoll, CHANGE admingid admingid SMALLINT(6) UNSIGNED DEFAULT '0' NOT NULL, ADD allowmoduser TINYINT(1) NOT NULL AFTER allowedituser, ADD allowstickthread tinyint(1) NOT NULL AFTER alloweditpoll, ADD allowrefund tinyint(1) NOT NULL AFTER allowmassprune;
ALTER TABLE cdb_attachtypes CHANGE extension extension char(12) NOT NULL;
UPDATE cdb_stats SET variable='Spiders' WHERE type='os' AND variable='BeOS';
UPDATE cdb_stats SET variable='0' WHERE type='week' AND variable='';
UPDATE cdb_admingroups SET allowmodpost=1;
UPDATE cdb_admingroups SET allowmoduser='1', allowstickthread=4-admingid WHERE admingid IN (1,2,3);
UPDATE cdb_admingroups SET allowrefund=1 WHERE admingid=1 OR admingid=2;

ALTER TABLE cdb_threads CHANGE views views INT(10) UNSIGNED DEFAULT '0' NOT NULL,
	CHANGE replies replies MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL,
	ADD blog TINYINT(1) NOT NULL AFTER digest,
	ADD moderated TINYINT(1) NOT NULL AFTER attachment,
	ADD rate TINYINT(1) NOT NULL AFTER digest,
	ADD INDEX blog (blog , authorid , dateline),
	ADD readperm tinyint(3) UNSIGNED NOT NULL AFTER iconid,
	ADD price smallint(6) NOT NULL AFTER readperm;
UPDATE cdb_threads SET readperm=100 WHERE creditsrequire>10;
ALTER TABLE cdb_threads DROP creditsrequire, ADD typeid smallint(6) UNSIGNED NOT NULL default '0' AFTER iconid, ADD INDEX typeid (fid, typeid, displayorder, lastpost);

ALTER TABLE cdb_sessions ADD lastolupdate INT(10) UNSIGNED NOT NULL AFTER lastactivity, ADD bloguid MEDIUMINT(8) UNSIGNED NOT NULL, ADD seccode SMALLINT(6) UNSIGNED NOT NULL AFTER lastolupdate, ADD pageviews smallint(6) UNSIGNED NOT NULL AFTER lastolupdate, ADD INDEX (uid), ADD INDEX (bloguid);
ALTER TABLE cdb_usergroups ADD maxsizeperday INT(10) UNSIGNED NOT NULL AFTER maxattachsize, CHANGE maxattachsize maxattachsize MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL, ADD radminid TINYINT(3) NOT NULL AFTER groupid, ADD allowhtml TINYINT(1) NOT NULL AFTER allowhidecode, ADD allowdirectpost tinyint(1) NOT NULL AFTER allowpostpoll, ADD reasonpm tinyint(1) NOT NULL AFTER allowviewstats, ADD allowuseblog TINYINT(1) NOT NULL AFTER allowkarma, ADD allowviewpro tinyint(1) NOT NULL AFTER allowsigimgcode, ADD minkarmarate smallint(6) NOT NULL default 0 AFTER reasonpm, CHANGE maxkarmarate maxkarmarate1 smallint(6) NOT NULL default 0, ADD maxkarmarate smallint(6) NOT NULL default 0 AFTER minkarmarate, ADD allowcusbbcode TINYINT(1) NOT NULL AFTER allowhtml, CHANGE allowsetviewperm allowsetreadperm tinyint(1) NOT NULL, ADD readaccess tinyint(3) UNSIGNED NOT NULL default '0' AFTER groupavatar, ADD allownickname tinyint(1) NOT NULL AFTER allowcusbbcode;
UPDATE cdb_usergroups SET allowdirectpost='1', allowuseblog='1', allowcusbbcode='1' WHERE groupid IN ('1', '2', '3');
UPDATE cdb_usergroups SET allowviewpro=1 WHERE groupid IN (1,2,3) OR groupid>8;
UPDATE cdb_usergroups SET maxkarmarate=maxkarmarate1;
UPDATE cdb_usergroups SET minkarmarate=-1*maxkarmarate;
UPDATE cdb_usergroups SET allownickname=1 WHERE groupid IN (1,2,3) OR creditshigher>500;
UPDATE cdb_usergroups SET readaccess=10 WHERE groupid NOT IN (4,5,6,7,8) AND allowview<>0;
UPDATE cdb_usergroups SET readaccess=100+3-groupid WHERE groupid IN (1,2,3);
ALTER TABLE cdb_usergroups DROP maxkarmarate1, DROP allowview;
UPDATE cdb_usergroups SET radminid=groupid WHERE groupid IN (1,2,3);
ALTER TABLE cdb_smilies CHANGE code code VARCHAR(30) NOT NULL, ADD displayorder tinyint(1) NOT NULL AFTER id;
ALTER TABLE cdb_favorites DROP INDEX tid, ADD INDEX uid (uid);
ALTER TABLE cdb_styles DROP INDEX themename;

DROP TABLE IF EXISTS cdb_threadtypes;
CREATE TABLE cdb_threadtypes (
  typeid smallint(6) UNSIGNED NOT NULL auto_increment,
  displayorder tinyint(3) NOT NULL,
  name varchar(255) NOT NULL,
  description varchar(255) NOT NULL default '',
  PRIMARY KEY  (typeid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_medals;
CREATE TABLE cdb_medals (
  medalid smallint(6) UNSIGNED NOT NULL auto_increment,
  name varchar(50) NOT NULL,
  available tinyint(1) NOT NULL,
  image varchar(30) NOT NULL,
  PRIMARY KEY  (medalid)
) TYPE=MyISAM;

INSERT INTO cdb_medals VALUES (1, 'Medal No.1', 0, 'medal1.gif');
INSERT INTO cdb_medals VALUES (2, 'Medal No.2', 0, 'medal2.gif');
INSERT INTO cdb_medals VALUES (3, 'Medal No.3', 0, 'medal3.gif');
INSERT INTO cdb_medals VALUES (4, 'Medal No.4', 0, 'medal4.gif');
INSERT INTO cdb_medals VALUES (5, 'Medal No.5', 0, 'medal5.gif');
INSERT INTO cdb_medals VALUES (6, 'Medal No.6', 0, 'medal6.gif');
INSERT INTO cdb_medals VALUES (7, 'Medal No.7', 0, 'medal7.gif');
INSERT INTO cdb_medals VALUES (8, 'Medal No.8', 0, 'medal8.gif');
INSERT INTO cdb_medals VALUES (9, 'Medal No.9', 0, 'medal9.gif');
INSERT INTO cdb_medals VALUES (10, 'Medal No.10', 0, 'medal10.gif');

DROP TABLE IF EXISTS cdb_adminactions;
CREATE TABLE cdb_adminactions (
  admingid SMALLINT(6) UNSIGNED NOT NULL,
  disabledactions TEXT NOT NULL,
  PRIMARY KEY  (admingid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_advertisements;
CREATE TABLE cdb_advertisements (
  advid mediumint(8) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  `type` varchar(50) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  targets varchar(255) NOT NULL default '',
  parameters text NOT NULL,
  code text NOT NULL,
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (advid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_orders;
CREATE TABLE cdb_orders (
  orderid char(32) NOT NULL default '',
  `status` char(3) NOT NULL default '',
  buyer char(50) NOT NULL default '',
  admin char(15) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  amount smallint(6) unsigned NOT NULL default '0',
  price float(7,2) unsigned NOT NULL default '0.00',
  submitdate int(10) unsigned NOT NULL default '0',
  confirmdate int(10) unsigned NOT NULL default '0',
  UNIQUE KEY orderid (orderid),
  KEY submitdate (submitdate),
  KEY uid (uid,submitdate)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_adminsessions;
CREATE TABLE cdb_adminsessions (
  uid mediumint(8) UNSIGNED NOT NULL default '0',
  ip char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  errorcount tinyint(1) NOT NULL default '0'
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_blogcaches;
CREATE TABLE cdb_blogcaches (
  uid mediumint(8) unsigned NOT NULL default '0',
  variable char(10) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY  (uid,variable)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_karmalog;
DROP TABLE IF EXISTS cdb_ratelog;
CREATE TABLE cdb_ratelog (
  pid int(10) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  extcredits tinyint(1) UNSIGNED NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  score smallint(6) NOT NULL default '0',
  reason char(20) NOT NULL,
  INDEX (dateline),
  INDEX  (pid, dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_moderators;
CREATE TABLE cdb_moderators (
  uid mediumint(8) UNSIGNED NOT NULL,
  fid smallint(6) UNSIGNED NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  inherited tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid, fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_regips;
CREATE TABLE cdb_regips (
  ip char(15) NOT NULL,
  dateline int(10) UNSIGNED NOT NULL default 0,
  count smallint(6) NOT NULL default 0,
  KEY  (ip)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_statvars;
CREATE TABLE cdb_statvars (
  type varchar(20) NOT NULL default '',
  variable varchar(20) NOT NULL default '',
  value mediumtext NOT NULL,
  PRIMARY KEY  (type, variable)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_threadsmod;
CREATE TABLE cdb_threadsmod (
  tid mediumint(8) UNSIGNED NOT NULL,
  uid mediumint(8) UNSIGNED NOT NULL,
  username char(15) NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  action char(3) NOT NULL,
  PRIMARY KEY (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_adminnotes;
CREATE TABLE cdb_adminnotes (
  id mediumint(8) unsigned NOT NULL auto_increment,
  admin varchar(15) NOT NULL default '',
  access tinyint(3) NOT NULL default '0',
  adminid tinyint(3) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  PRIMARY KEY  (id)
) Type=MyISAM;

DROP TABLE IF EXISTS cdb_onlinetime;
CREATE TABLE cdb_onlinetime (
  uid mediumint(8) unsigned NOT NULL,
  thismonth smallint(6) UNSIGNED NOT NULL default '0',
  total mediumint(8) UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (uid)
) Type=MyISAM;

DROP TABLE IF EXISTS cdb_forumfields;
CREATE TABLE cdb_forumfields (
  fid smallint(6) UNSIGNED NOT NULL,
  description text NOT NULL,
  password varchar(12) NOT NULL,
  icon varchar(255) NOT NULL,
  postcredits varchar(255) NOT NULL,
  replycredits varchar(255) NOT NULL,
  redirect varchar(255) NOT NULL,
  attachextensions varchar(255) NOT NULL,
  moderators text NOT NULL,
  rules text NOT NULL,
  threadtypes text NOT NULL,
  viewperm text NOT NULL,
  postperm text NOT NULL,
  replyperm text NOT NULL,
  getattachperm text NOT NULL,
  postattachperm text NOT NULL,
  PRIMARY KEY  (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_validating;
CREATE TABLE cdb_validating (
  uid mediumint(8) unsigned NOT NULL,
  submitdate int(10) unsigned NOT NULL,
  moddate int(10) unsigned NOT NULL,
  admin varchar(15) NOT NULL,
  submittimes tinyint(3) unsigned NOT NULL,
  status tinyint(1) NOT NULL,
  message text NOT NULL,
  remark text NOT NULL,
  PRIMARY KEY  (uid),
  KEY  (status)
) Type=MyISAM;

DROP TABLE IF EXISTS cdb_paymentlog;
CREATE TABLE cdb_paymentlog (
  uid mediumint(8) UNSIGNED NOT NULL,
  tid mediumint(8) UNSIGNED NOT NULL,
  authorid mediumint(8) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  amount smallint(6) NOT NULL,
  netamount smallint(6) NOT NULL,
  PRIMARY KEY (tid, uid),
  INDEX (uid),
  INDEX (authorid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_creditslog;
CREATE TABLE cdb_creditslog (
  uid mediumint(8) UNSIGNED NOT NULL,
  fromto char(15) NOT NULL,
  sendcredits tinyint(1) NOT NULL,
  receivecredits tinyint(1) NOT NULL,
  send int(10) UNSIGNED NOT NULL,
  receive int(10) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  operation char(3) NOT NULL,
  INDEX (uid, dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_rsscaches;
CREATE TABLE cdb_rsscaches (
  lastupdate int(10) UNSIGNED NOT NULL,
  fid smallint(6) UNSIGNED NOT NULL,
  tid mediumint(8) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  forum char(50) NOT NULL,
  author char(15) NOT NULL,
  subject char(80) NOT NULL,
  description char(255) NOT NULL,
  INDEX (fid, dateline)
) TYPE=MyISAM;
EOT;

$upgrade2 = <<<EOT
DROP TABLE IF EXISTS cdb_memberfields;
CREATE TABLE cdb_memberfields (
  uid mediumint(8) unsigned NOT NULL,
  origgroup VARCHAR(10) NOT NULL,
  site varchar(75) NOT NULL default '',
  icq varchar(12) NOT NULL default '',
  oicq varchar(12) NOT NULL default '',
  yahoo varchar(40) NOT NULL default '',
  msn varchar(40) NOT NULL default '',
  location varchar(30) NOT NULL default '',
  customstatus varchar(30) NOT NULL default '',
  avatar varchar(100) NOT NULL default '',
  avatarwidth tinyint(3) unsigned NOT NULL default '0',
  avatarheight tinyint(3) unsigned NOT NULL default '0',
  bio text NOT NULL,
  signature text NOT NULL,
  sightml text NOT NULL,
  ignorepm text NOT NULL,
  authstr varchar(20) NOT NULL default '',
  \$str_table
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

ALTER TABLE cdb_pms DROP INDEX msgtoid, ADD INDEX msgtoid (msgtoid, folder, dateline), DROP INDEX msgfromid, ADD INDEX msgfromid (msgfromid, folder, dateline);
EOT;

$upgrade4_3 = <<<EOT
ALTER TABLE cdb_posts ADD attachment TINYINT(1) NOT NULL AFTER parseurloff,
	ADD invisible TINYINT(1) NOT NULL AFTER useip,
	ADD htmlon TINYINT(1) NOT NULL AFTER usesig,
	DROP INDEX tid,
	ADD INDEX (invisible),
	ADD INDEX displayorder (tid, invisible, dateline),
	ADD first tinyint(1) NOT NULL AFTER tid,
	ADD INDEX first (tid, first),
	DROP INDEX dotfolder;
EOT;

$upgrade5_3 = <<<EOT
UPDATE cdb_posts SET attachment=1 WHERE aid>0;
ALTER TABLE cdb_attachments ADD INDEX (tid), CHANGE downloads downloads MEDIUMINT(8) DEFAULT '0' NOT NULL, CHANGE creditsrequire readperm tinyint(3) UNSIGNED NOT NULL, ADD description char(100) NOT NULL AFTER filename, ADD dateline int(10) UNSIGNED NOT NULL AFTER pid, ADD INDEX pid (pid, aid);
UPDATE cdb_attachments SET readperm=20 WHERE readperm>100;

EOT;

$upgrade6_3 = <<<EOT
ALTER TABLE cdb_posts DROP aid;
EOT;

$upgrade4_4 = <<<EOT
ALTER TABLE cdb_posts CHANGE aid attachment TINYINT(1) NOT NULL AFTER parseurloff,
	ADD invisible TINYINT(1) NOT NULL AFTER useip,
	ADD htmlon TINYINT(1) NOT NULL AFTER usesig,
	DROP INDEX tid,
	ADD INDEX (invisible),
	ADD INDEX displayorder (tid, invisible, dateline),
	ADD first tinyint(1) NOT NULL AFTER tid,
	ADD INDEX first (tid, first),
	DROP INDEX dotfolder;
EOT;

$upgrade5_4 = <<<EOT
UPDATE cdb_posts SET attachment=1 WHERE attachment>0;
EOT;

$upgrade6_4 = <<<EOT
ALTER TABLE cdb_attachments ADD INDEX (tid), ADD INDEX pid (pid,aid), CHANGE downloads downloads MEDIUMINT(8) DEFAULT '0' NOT NULL, CHANGE creditsrequire readperm tinyint(3) UNSIGNED NOT NULL, ADD description char(100) NOT NULL AFTER filename, ADD dateline int(10) UNSIGNED NOT NULL AFTER pid;
UPDATE cdb_attachments SET readperm=20 WHERE readperm>100;
EOT;

$upgrade7 = <<<EOT
ALTER TABLE cdb_members DROP site, DROP icq, DROP oicq, DROP yahoo, DROP msn, DROP location, DROP bio,
	DROP avatar, DROP avatarwidth, DROP avatarheight, DROP customstatus, DROP ignorepm, DROP identifying,
	CHANGE username username char(15) NOT NULL default '',
	CHANGE password password char(32) NOT NULL default '',
	CHANGE secques secques char(8) NOT NULL default '',
	CHANGE regip regip char(15) NOT NULL default '',
	CHANGE lastip lastip char(15) NOT NULL default '',
	CHANGE email email char(50) NOT NULL default '',
	CHANGE dateformat dateformat char(10) NOT NULL default '',
	CHANGE timeformat timeformat tinyint(1) NOT NULL default '0',
	CHANGE signature sigstatus tinyint(1) NOT NULL default 0,
	CHANGE postnum posts MEDIUMINT(8) UNSIGNED NOT NULL default '0',
	CHANGE credit credits int(10) NOT NULL,
	CHANGE showemail showemail tinyint(1) NOT NULL default '0',
	CHANGE timeoffset timeoffset CHAR(4) NOT NULL,
	ADD digestposts SMALLINT(6) UNSIGNED NOT NULL AFTER posts,
	ADD groupexpiry INT(10) UNSIGNED NOT NULL AFTER groupid,
	ADD pmsound TINYINT(1) NOT NULL AFTER timeformat,
	CHANGE extracredit extcredits1 int(10) NOT NULL,
	ADD extcredits5 int(10) NOT NULL AFTER extcredits1,
	ADD extcredits4 int(10) NOT NULL AFTER extcredits1,
	ADD extcredits3 int(10) NOT NULL AFTER extcredits1,
	ADD extcredits2 int(10) NOT NULL AFTER extcredits1,
	ADD extgroupids char(60) NOT NULL AFTER groupexpiry,
	ADD avatarshowid int(10) UNSIGNED NOT NULL default '0' AFTER extcredits5,
	ADD extcredits8 int(10) NOT NULL AFTER extcredits5,
	ADD extcredits7 int(10) NOT NULL AFTER extcredits5,
	ADD extcredits6 int(10) NOT NULL AFTER extcredits5,
	ADD pageviews mediumint(8) UNSIGNED NOT NULL AFTER digestposts,
	ADD oltime smallint(6) UNSIGNED NOT NULL AFTER digestposts,
	DROP INDEX username,
	ADD UNIQUE (username),
	ADD INDEX (email)
	\$str_fields;
EOT;

$upgrade8 = <<<EOT
UPDATE cdb_members SET extcredits1=credits, showemail='-1', timeoffset='9999', timeformat='0', dateformat='', pmsound='1';
ALTER TABLE cdb_memberfields CHANGE oicq qq varchar(12) NOT NULL, ADD nickname varchar(30) NOT NULL AFTER uid, CHANGE avatar avatar varchar(255) NOT NULL, ADD medals varchar(255) NOT NULL AFTER customstatus;
ALTER TABLE cdb_forums DROP moderator,
	DROP password,
	DROP icon,
	DROP viewperm,
	DROP postperm,
	DROP replyperm,
	DROP getattachperm,
	ADD inheritedmod tinyint(1) NOT NULL AFTER allowimgcode,
	ADD autoclose smallint(6) NOT NULL AFTER replycredits,
	ADD modnewposts tinyint(1) NOT NULL AFTER allowimgcode,
	ADD recyclebin tinyint(1) NOT NULL AFTER allowimgcode,
	ADD allowblog TINYINT(1) NOT NULL AFTER allowimgcode,
	ADD jammer TINYINT(1) NOT NULL AFTER modnewposts,
	ADD todayposts MEDIUMINT(8) UNSIGNED NOT NULL AFTER posts,
	ADD INDEX (fup);
DELETE FROM cdb_attachments WHERE pid='0' OR aid='0' OR tid='0';
UPDATE cdb_forums SET allowblog='1';
DELETE FROM cdb_posts WHERE dateline=0;
DELETE FROM cdb_threads WHERE dateline=0;
EOT;

$upgrade9 = <<<EOT
ALTER TABLE cdb_usergroups ADD public tinyint(1) NOT NULL AFTER radminid, ADD allowmultigroups tinyint(1) NOT NULL AFTER allowvote, CHANGE allowcstatus allowcstatus1 tinyint(1) NOT NULL, CHANGE allowavatar allowavatar1 tinyint(1) NOT NULL, ADD allowcstatus tinyint(1) NOT NULL AFTER allowsearch, ADD allowavatar tinyint(1) NOT NULL AFTER allowsearch, ADD allowreply tinyint(1) NOT NULL AFTER allowpost, ADD raterange char(120) NOT NULL AFTER attachextensions, ADD maxprice smallint(6) UNSIGNED NOT NULL NOT NULL AFTER reasonpm, ADD allowtransfer tinyint(1) NOT NULL AFTER allowinvisible, ADD disableperiodctrl tinyint(1) NOT NULL AFTER allowviewstats;
UPDATE cdb_usergroups SET allowcstatus=allowcstatus1, allowavatar=allowavatar1, allowreply=allowpost, raterange=CONCAT_WS('\t', allowkarma, minkarmarate, maxkarmarate, maxrateperday);
UPDATE cdb_usergroups SET allowmultigroups=1 WHERE groupid NOT IN (4,5,6,7,8) AND (type<>'member' OR (type='member' AND creditshigher>=500));
UPDATE cdb_usergroups SET raterange='' WHERE raterange LIKE '0\t0\t0\t%';
UPDATE cdb_usergroups SET allowtransfer=1, disableperiodctrl=1, maxprice=(4-groupid)*10 WHERE groupid IN (1,2,3);
ALTER TABLE cdb_usergroups DROP allowcstatus1, DROP allowavatar1, DROP allowkarma, DROP minkarmarate, DROP maxkarmarate, DROP maxrateperday, CHANGE attachextensions attachextensions CHAR(100) NOT NULL;
ALTER TABLE cdb_buddys ADD dateline int(10) UNSIGNED NOT NULL, ADD description CHAR(255) NOT NULL;
UPDATE cdb_buddys SET dateline=UNIX_TIMESTAMP();
ALTER TABLE cdb_profilefields ADD unchangeable TINYINT(1) NOT NULL AFTER required;
ALTER TABLE cdb_failedlogins ADD PRIMARY KEY (ip);
DELETE FROM cdb_settings WHERE variable IN ('maintspans');
ALTER TABLE cdb_settings ORDER BY variable;
ALTER TABLE cdb_onlinetime ADD lastupdate int(10) UNSIGNED NOT NULL;
ALTER TABLE cdb_usergroups ADD system char(8) NOT NULL default 'private' AFTER type;
UPDATE cdb_usergroups SET system='0\t0' WHERE public<>0;
ALTER TABLE cdb_usergroups DROP public;
ALTER TABLE cdb_memberfields ADD groupterms text NOT NULL AFTER ignorepm;
REPLACE INTO cdb_smilies (id, displayorder, type, code, url) VALUES ('25', '0', 'icon', '', 'icon7.gif');
UPDATE cdb_usergroups SET system='0\t0' WHERE system<>'private';
UPDATE cdb_forums SET threads=0, posts=0 WHERE type='group';
ALTER TABLE cdb_usergroups CHANGE raterange raterange CHAR(150) NOT NULL;
ALTER TABLE cdb_forums ADD alloweditrules TINYINT(1) NOT NULL AFTER allowblog, ADD allowtrade TINYINT(1) NOT NULL AFTER allowblog;
UPDATE cdb_forums SET allowtrade='3' WHERE type IN ('forum', 'sub');
DELETE FROM cdb_settings WHERE variable IN ('ec_allowposttrade', 'ec_allowpaytoauthor');
ALTER TABLE cdb_memberfields ADD alipay VARCHAR(50) NOT NULL AFTER site, ADD taobao varchar(40) NOT NULL AFTER msn;
REPLACE INTO cdb_settings (variable, value) VALUES ('modratelimit', '0');
ALTER TABLE cdb_forums ADD disablewatermark TINYINT(1) NOT NULL AFTER jammer;
EOT;

if(!$action) {
	echo"本程序用于升级 Discuz! 3.1.2 到 Discuz! 4.0.0,请确认之前已经顺利安装 Discuz! 3.1.2<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 3.1.2 升级到 4.0.0,运行之前,请确认已经上传 3.1.2 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 4.0.0 版的全部文件和目录,覆盖服务器上的 3.1.2<br>2. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='version'");
		if(!in_array(($db->result($query, 0)), array('3.1', '3.1.2'))) {
			exit('您当前数据库数据版本不是3.1或3.1.2,无法升级');
		}

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		$query = $db->query("SELECT username, count(*) AS count FROM {$tablepre}members GROUP BY username HAVING count>1");
		while($member = $db->fetch_array($query)) {
			$uids = 0;
			$member['count']--;
			$member['username'] = addslashes($member['username']);
			$querymem = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$member[username]' ORDER BY adminid>0, postnum, credit LIMIT $member[count]", 'SILENT');
			while($mem = $db->fetch_array($querymem)) {
				$uids .= ",$mem[uid]";
			}
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
		}

		runquery($upgrade1);

		echo "第 1 步升级成功<br><br>";
		redirect("?action=upgrade&step=2");

	} elseif($step == 2 || $step == 3) {
		
		$fieldids = array();
		$str_table = '';
		$query = $db->query("SELECT fieldid, size FROM {$tablepre}profilefields");
		while($field = $db->fetch_array($query)) {
			$str_table .= "\nfield_$field[fieldid] varchar($field[size]) NOT NULL default '',";
			$fieldids[] = $field['fieldid'];
		}

		if($step == 2) {
			eval("\$upgrade2 = \"$upgrade2\";");
			runquery($upgrade2);

			echo "第 2 步升级成功<br><br>";
			redirect("?action=upgrade&step=3&start=0");
		} else {

			$start = intval($start);
			$fields = 'uid, site, icq, oicq, yahoo, msn, location, bio, signature, avatar, avatarwidth, avatarheight, customstatus, ignorepm';
			$fields .= $fieldids ? ', field_'.implode(', field_', $fieldids) : '';

			$query = $db->query("SELECT $fields FROM {$tablepre}members LIMIT $start, 3000");
			while($member = $db->fetch_array($query)) {
				$member = daddslashes($member);

				$fieldadd = '';
				foreach($fieldids as $fieldid) {
					$fieldadd .= ", '".$member['field_'.$fieldid]."'";
				}

				$db->query("INSERT INTO {$tablepre}memberfields ($fields, sightml)
					VALUES('$member[uid]', '$member[site]', '$member[icq]', '$member[oicq]', '$member[yahoo]', '$member[msn]', '$member[location]', '$member[bio]', '$member[signature]', '$member[avatar]', '$member[avatarwidth]', '$member[avatarheight]', '$member[customstatus]', '$member[ignorepm]' $fieldadd, '$member[signature]')");
			}

			if($db->num_rows($query)) {
				echo "正在进行第 3 步升级 起始列数: $start<br><br>";
				redirect("?action=upgrade&step=3&start=".($start+3000));
			} else {
				echo "第 3 步升级成功<br><br>";
				redirect("?action=upgrade&step=4");
			}

		}

	} elseif($step == 4) {

		runquery(intval(mysql_get_server_info()) == 3 ? $upgrade4_3 : $upgrade4_4);

		echo "第 4 步升级成功<br><br>";
		redirect("?action=upgrade&step=5");

	} elseif($step == 5) {

		runquery(intval(mysql_get_server_info()) == 3 ? $upgrade5_3 : $upgrade5_4);

		loginit('ratelog');
		loginit('illegallog');
		loginit('modslog');
		loginit('cplog');
		loginit('errorlog');
		loginit('banlog');

		echo "第 5 步升级成功<br><br>";
		redirect("?action=upgrade&step=6");

	} elseif($step == 6) {

		runquery(intval(mysql_get_server_info()) == 3 ? $upgrade6_3 : $upgrade6_4);

		echo "第 6 步升级成功<br><br>";
		redirect("?action=upgrade&step=7");

	} elseif($step == 7) {

		$str_fields = '';
		$query = $db->query("SELECT fieldid, size FROM {$tablepre}profilefields");
		while($field = $db->fetch_array($query)) {
			$str_fields .= ", DROP field_$field[fieldid]";
		}

		$query = $db->query("SELECT * FROM {$tablepre}forums");
		while($forum = $db->fetch_array($query)) {
			$forum['moderator'] = addslashes(str_replace(' ', '', $forum['moderator']));
			$query1 = $db->query("SELECT uid FROM {$tablepre}members WHERE username IN ('".str_replace(',', '\',\'', $forum['moderator'])."')");
			while($member = $db->fetch_array($query1)) {
				$db->query("REPLACE INTO {$tablepre}moderators (fid, uid) VALUES
					('$forum[fid]', '$member[uid]')");
			}
			$db->query("REPLACE INTO {$tablepre}forumfields (fid, icon, moderators, password, viewperm, postperm, replyperm, getattachperm)
				VALUES ('$forum[fid]', '".addslashes($forum['icon'])."', '".str_replace(',', "\t", addslashes($forum['moderator']))."', '".addslashes($forum['password'])."', '$forum[viewperm]', '$forum[postperm]', '$forum[replyperm]', '$forum[getattachperm]')");
		}

		eval("\$upgrade7 = \"$upgrade7\";");
		runquery($upgrade7);

		$query = $db->query("SELECT s.styleid, sv.substitute FROM {$tablepre}styles s
			LEFT JOIN {$tablepre}stylevars sv ON s.styleid=sv.styleid AND sv.variable='fontsize'");
		while($style = $db->fetch_array($query)) {
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$style[styleid]', 'innerborderwidth', '1')");
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$style[styleid]', 'innerbordercolor', '#D6E0EF')");
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$style[styleid]', 'msgfontsize', '$style[substitute]')");
		}

		echo "第 7 步升级成功<br><br>";
		redirect("?action=upgrade&step=8");

	} elseif($step == 8) {

		runquery($upgrade8);

		$q = $db->query("SELECT fid FROM {$tablepre}forums");
		while($f = $db->fetch_array($q)) {
			$fid = $f['fid'];
			$query = $db->query("SELECT COUNT(*) AS threadcount, SUM(t.replies)+COUNT(*) AS replycount
				FROM {$tablepre}threads t, {$tablepre}forums f
				WHERE f.fid='$fid' AND t.fid=f.fid AND t.displayorder>='0'");

			extract($db->fetch_array($query));

			$query = $db->query("SELECT tid, subject, lastpost, lastposter FROM {$tablepre}threads
				WHERE fid='$fid' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");

			$thread = $db->fetch_array($query);
			$thread['subject'] = addslashes($thread['subject']);
			$thread['lastposter'] = addslashes($thread['lastposter']);

			$db->query("UPDATE {$tablepre}forums SET posts='$replycount', threads='$threadcount', lastpost='$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]' WHERE fid='$fid'", 'UNBUFFERED');
		}

		echo "第 8 步升级成功<br><br>";
		redirect("?action=upgrade&step=9");

	} elseif($step == 9) {

		runquery($upgrade9);
		$db->query("ALTER TABLE {$tablepre}pms DROP INDEX folder", 'SILENT');
		$db->query("ALTER TABLE {$tablepre}styles DROP INDEX themename", 'SILENT');
		
		$query = $db->query("SELECT fid, description, postcredits, replycredits FROM {$tablepre}forums");
		while($forum = $db->fetch_array($query)) {
			$postcredits = $forum['postcredits'] == -1 ? '' : addslashes(serialize(array(1 => $forum['postcredits'])));
			$replycredits = $forum['replycredits'] == -1 ? '' : addslashes(serialize(array(1 => $forum['replycredits'])));
			$db->query("UPDATE {$tablepre}forumfields SET description='".addslashes($forum['description'])."', postcredits='$postcredits', replycredits='$replycredits' WHERE fid='$forum[fid]'");
		}
		$db->query("ALTER TABLE {$tablepre}forums DROP description, DROP postcredits, DROP replycredits");

		echo "第 9 步升级成功<br><br>";
		redirect("?action=upgrade&step=10");

	} elseif($step == 10) {

		//这里可能需要分步骤操作
		$query = $db->query("SELECT pid, dateline FROM {$tablepre}posts WHERE attachment>0");
		while($post = $db->fetch_array($query)) {
			$db->query("UPDATE {$tablepre}attachments SET dateline='$post[dateline]' WHERE pid='$post[pid]'");
		}

		echo "第 10 步升级成功<br><br>";
		redirect("?action=upgrade&step=11");

	} elseif($step == 11) {

		$num = 10000; // parameter
		$start = intval($start);

		$pids = 0;
		$query = $db->query("SELECT pid FROM {$tablepre}threads t, {$tablepre}posts p
			WHERE p.tid=t.tid AND p.dateline=t.dateline AND p.authorid=t.authorid
			LIMIT $start, $num");
		while($post = $db->fetch_array($query)) {
			$pids .= ','.$post['pid'];
		}

		$db->query("UPDATE {$tablepre}posts SET first='1' WHERE pid IN ($pids)");

		if($db->num_rows($query)) {
			echo "正在进行第 11 步升级 起始列数: $start<br><br>";
			redirect("?action=upgrade&step=11&start=".($start+$num));
		} else {
			echo "第 11 步升级成功<br><br>";
			redirect("?action=upgrade&step=12");
		}

	} elseif($step == 12) {

		$query = $db->query("SELECT uid, buddyid, count(*) AS count FROM {$tablepre}buddys GROUP BY uid, buddyid HAVING count>1");
		while($buddy = $db->fetch_array($query)) {
			$db->query("DELETE FROM {$tablepre}buddys WHERE uid='$buddy[uid]' AND buddyid='$buddy[buddyid]' LIMIT ".($buddy['count'] - 1));
		}

		$credits = array();
		$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable LIKE '%credits%'");
		while($c = $db->fetch_array($query)) {
			$credits[$c['variable']] = $c['value'];
		}

		$extcredits = Array
		(
		1 => Array
			(
			'title' => '威望',
			'showinthread' => '',
			'available' => 1
			),
		2 => Array
			(
			'title' => '金钱',
			'showinthread' => '',
			'available' => 1
			)
		);
		$creditspolicy = Array
		(
		'post' => Array
			(
			1 => $credits['postcredits']
			),
		'reply' => Array
			(
			1 => $credits['replycredits']
			),
		'digest' => Array
			(
			1 => $credits['digestcredits']
			),
		'postattach' => Array
			(
			),
		'getattach' => Array
			(

			),
		'pm' => Array
			(
			),
		'search' => Array
			(
			)
		);

		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('creditspolicy', '".addslashes(serialize($creditspolicy))."')");

		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('extcredits', '".addslashes(serialize($extcredits))."')");

		$db->query("DELETE FROM {$tablepre}settings WHERE variable IN ('deletedcredits', 'digestcredits', 'postcredits', 'replycredits')");

		$query = $db->query("SELECT m.uid, m.groupid, m.groupexpiry, mf.origgroup FROM {$tablepre}members m, {$tablepre}memberfields mf WHERE mf.origgroup<>'' AND m.uid=mf.uid AND m.groupexpiry>0");
		while($member = $db->fetch_array($query)) {
			$member['origgroup'] = explode("\t", $member['origgroup']);
			if($member['origgroup'][0] || $member['origgroup'][1]) {
				$array = array('main' => array('time' => $member['groupexpiry'], 'groupid' => $member['origgroup'][1], 'adminid' => $member['origgroup'][0]), 'ext' => array($member['groupid'] => $member['groupexpiry']));
				$db->query("UPDATE {$tablepre}memberfields SET groupterms='".addslashes(serialize($array))."' WHERE uid='$member[uid]'");
			}
		}

		$db->query("ALTER TABLE {$tablepre}memberfields DROP origgroup");

		@unlink('./forumdata/cache/cache_settings.php');
		echo "恭喜您升级成功,请务必删除本程序. 进行以下操作后才能最后完成:<br>";

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
	echo"setTimeout('redirect();', 500);\n";
	echo"</script>";
	echo"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";

}

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

?>