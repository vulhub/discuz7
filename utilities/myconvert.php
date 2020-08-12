<?php

// Upgrade Discuz! Board from 4.1.0 to 5.0.0

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

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

$upgrademsg = array(
	1 => '论坛升级第 1 步: 导入我的主题<br>',
	2 => '论坛升级第 2 步: 导入我的回复<br>',
	3 => '论坛升级第 3 步: 标记我的回复<br>',
	4 => '论坛升级第 4 步: 全部导入完毕<br>',
);

if(!$action) {
	echo 	"本程序用于导入 Discuz!5.0.0 我的主题功能, 请确认之前已经顺利升级到 Discuz!5.0.0 <br><br>",
		"<b><font color=\"red\">本程序用于升级 Discuz!5.0.0 我的主题功能</font></b><br>",
		"<b><font color=\"red\">请确认已经上传 Discuz!5.0.0 的全部文件和目录并升级成功</font></b><br>",
		"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!</font></b><br><br>",
		"正确的升级方法为:<br><ol><li>确认论坛升级成功，关闭论坛<li>上传本程序到您的论坛安装目录中<li>运行本程序,直到出现升级完成的提示</ol><br><br>",
		"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$step = intval($step);
	echo $upgrademsg[$step];
	flush();

	$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable IN ('myrecorddays')");
	$myrecorddays = $db->result($query, 0);
	$converttimp = time() - $myrecorddays * 86400;

	if($step == 1) {

		$limit = 500; // 每次导入会员数
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE lastactivity>$converttimp AND posts>0 LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$authorid[] = $data;
		}

		if(is_array($authorid)) {
			foreach($authorid as $author) {
				$query = $db->query("SELECT tid,dateline,authorid FROM {$tablepre}threads WHERE authorid=$author[uid] AND dateline>$converttimp");
				while($data = $db->fetch_array($query)) {
					$threads[] = $data;
				}
			}
		}

		if(is_array($threads)) {
			foreach($threads as $thread) {
				$db->query("REPLACE INTO {$tablepre}mythreads (uid, tid, dateline) VALUES ('$thread[authorid]', '$thread[tid]', '$thread[dateline]')", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 2) {

		$limit = 500; // 每次导入会员数
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE lastactivity>$converttimp AND posts>0 LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$authorid[] = $data;
		}

		if(is_array($authorid)) {
			foreach($authorid as $author) {
				$query = $db->query("SELECT pid,tid,dateline,authorid FROM {$tablepre}posts WHERE authorid=$author[uid] AND dateline>$converttimp AND first!='1'");
				while($data = $db->fetch_array($query)) {
					$posts[] = $data;
				}
			}
		}

		if(is_array($posts)) {
			foreach($posts as $post) {
				$db->query("REPLACE INTO {$tablepre}myposts (uid, tid, pid, position, dateline) VALUES ('$post[authorid]', '$post[tid]', '$post[pid]', '".($thread['replies'] + 1)."', '$post[dateline]')", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 3) {

		$limit = 500; // 每次导入会员数
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT tid FROM {$tablepre}myposts LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$threadid[] = $data;
		}

		if(is_array($threadid)) {
			foreach($threadid as $thread) {
				$query = $db->query("SELECT tid,authorid,replies FROM {$tablepre}threads WHERE tid=$thread[tid]");
				while($data = $db->fetch_array($query)) {
					$threads[] = $data;
				}
			}
		}

		if(is_array($threads)) {
			foreach($threads as $thread) {
				$position = $thread['replies'] + 1;
				$db->query("UPDATE {$tablepre}myposts SET position='$position' WHERE tid='$thread[tid]' AND uid='$thread[authorid]'", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} else {

		echo 	'<br>恭喜您论坛数据导入成功，接下来请您：必删除本程序<br><br>'.
			'<b>感谢您选用我们的产品！</b>';
		exit;
	}
}

function redirect($url) {

	echo 	"<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";
	flush();

}

?>