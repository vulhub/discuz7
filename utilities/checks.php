<?
require_once './include/common.inc.php';

@set_time_limit(0);

$do = isset($do) ? $do : 'advance';

$lang = array(
	'filecheck_fullcheck' => '搜索未知文件',
	'filecheck_fullcheck_select' => '搜索未知文件 - 选择需要搜索的目录',
	'filecheck_fullcheck_selectall' => '[搜索全部目录]',
	'filecheck_fullcheck_start' => '开始时间:',
	'filecheck_fullcheck_current' => '当前时间:',
	'filecheck_fullcheck_end' => '结束时间:',
	'filecheck_fullcheck_file' => '当前文件:',
	'filecheck_fullcheck_foundfile' => '发现未知文件数: ',
	'filecheck_fullcheck_nofound' => '没有发现任何未知文件'
);

if(!$discuzfiles = @file('admin/discuzfiles.md5')) {
	cpmsg('filecheck_nofound_md5file');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$lang['filecheck_fullcheck']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="./images/admincp/admincp.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="include/javascript/common.js"></script>

<body>
<?
if($do == 'advance') {
	$dirlist = array();
	$starttime = date('Y-m-d H:i:s');
	$cachelist = $templatelist = array();
	if(empty($checkdir)) {
		checkdirs('./');
	} elseif($checkdir == 'all') {
		echo "\n<script>var dirlist = ['./'];var runcount = 0;var foundfile = 0</script>";
	} else {
		$checkdir = str_replace('..', '', $checkdir);
		$checkdir = $checkdir{0} == '/' ? '.'.$checkdir : $checkdir;
		checkdirs($checkdir.'/');
		echo "\n<script>var dirlist = ['$checkdir/'];var runcount = 0;var foundfile = 0</script>";
	}

	echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
		<tr class="header"><td><div style="float:left; margin-left:0px; padding-top:8px">'.(empty($checkdir) ? $lang['filecheck_fullcheck_select'] : $lang['filecheck_fullcheck'].($checkdir != 'all' ? ' - '.$checkdir : '')).'</div>
		</td></tr>';
	if(empty($checkdir)) {
		echo '<tr><td class="altbg1"><a href="checks.php?do=advance&start=yes&checkdir=all" target="_blank">'.$lang['filecheck_fullcheck_selectall'].'</a><br><ul>';
		foreach($dirlist as $dir) {
			$subcount = count(explode('/', $dir));
			echo '<li>'.str_repeat('&nbsp;', ($subcount - 1) * 4);
			echo '<a href="checks.php?do=advance&start=yes&checkdir='.rawurlencode($dir).'" target="_blank">'.basename($dir).'</a></li>';
		}
		echo '</ul></td></tr></table>';
	} else {
		echo '<tr><td class="altbg1">'.$lang['filecheck_fullcheck_start'].' '.$starttime.'<br><span id="msg"></span></td></tr><tr><td class="altbg2"><div id="checkresult"></div></td></tr></table>
			<iframe name="checkiframe" id="checkiframe" style="display: none"></iframe>';
		echo "<script>checkiframe.location = 'checks.php?do=advancenext&start=yes&dir=' + dirlist[runcount];</script>";
	}
	exit;
} elseif($do == 'advancenext') {
	$nopass = 0;
	foreach($discuzfiles as $line) {
		$md5files[] = trim(substr($line, 34));
	}
	$foundfile = checkfullfiles($dir);

	echo "<script>";
	if($foundfile) {
		echo "parent.foundfile += $foundfile;";
	}
	echo "parent.runcount++;
	if(parent.dirlist.length > parent.runcount) {
		parent.checkiframe.location = 'checks.php?do=advancenext&start=yes&dir=' + parent.dirlist[parent.runcount];
	} else {
		var msg = '';
		msg = '$lang[filecheck_fullcheck_end] ".addslashes(date('Y-m-d H:i:s'))."';
		if(parent.foundfile) {
			msg += '<br>$lang[filecheck_fullcheck_foundfile] ' + parent.foundfile;
		} else {
			msg += '<br>$lang[filecheck_fullcheck_nofound]';
		}
		parent.$('msg').innerHTML = msg;
	}</script>";
	exit;
}

function checkfullfiles($currentdir) {
	global $db, $tablepre, $md5files, $cachelist, $templatelist, $lang, $nopass;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		$file = $currentdir != './' ? preg_replace('/^\.\//', '', $file) : $file;
		$mainsubdir = substr($file, 0, strpos($file, '/'));
		if($entry != '.' && $entry != '..') {
			echo "<script>parent.$('msg').innerHTML = '$lang[filecheck_fullcheck_current] ".addslashes(date('Y-m-d H:i:s')."<br>$lang[filecheck_fullcheck_file] $file")."';</script>\r\n";
			if(is_dir($file)) {
    				checkfullfiles($file.'/');
			} elseif(is_file($file) && !in_array($file, $md5files)) {
				$pass = FALSE;
				if(in_array($file, array('./favicon.ico', './config.inc.php', './mail_config.inc.php', './robots.txt'))) {
					$pass = TRUE;
				}
				if($entry == 'index.htm' && filesize($file) < 5) {
					$pass = TRUE;
				}

				switch($mainsubdir) {
					case 'attachments' :
						if(!preg_match('/\.(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'images' :
						if(preg_match('/\.(gif|jpg|jpeg|png|ttf|wav|css)$/i', $entry)) {
							$pass = TRUE;
						}
					case 'customavatars' :
						if(preg_match('/\.(gif|jpg|jpeg|png)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'mspace' :
						if(preg_match('/\.(gif|jpg|jpeg|png|css|ini)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'forumdata' :
						$forumdatasubdir = str_replace('forumdata', '', dirname($file));
						if(substr($forumdatasubdir, 0, 8) == '/backup_') {
							if(preg_match('/\.(zip|sql)$/i', $entry)) {
								$pass = TRUE;
							}
						} else {
							switch ($forumdatasubdir) {
								case '' :
									if(in_array($entry, array('dberror.log', 'install.lock'))) {
										$pass = TRUE;
									}
								break;
								case '/templates':
									if(empty($templatelist)) {
										$query = $db->query("SELECT templateid, directory FROM {$tablepre}templates");
										while($template = $db->fetch_array($query)) {
											$templatelist[$template['templateid']] = $template['directory'];
										}
									}
									$tmp = array();
									$entry = preg_replace('/(\d+)\_(\w+)\.tpl\.php/ie', '$tmp = array(\1,"\2");', $entry);
									if(!empty($tmp) && file_exists($templatelist[$tmp[0]].'/'.$tmp[1].'.htm')) {
										$pass = TRUE;
									}

								break;
								case '/logs':
									if(preg_match('/(runwizardlog|\_cplog|\_errorlog|\_banlog|\_illegallog|\_modslog|\_ratelog|\_medalslog)\.php$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/cache':
									if(preg_match('/\.php$/i', $entry)) {
										if(empty($cachelist)) {
											$cachelist = checkcachefiles('forumdata/cache/');
											foreach($cachelist[1] as $nopassfile => $value) {
												$nopass++;
												echo "<script>parent.$('checkresult').innerHTML += '$nopassfile<br>';</script>\r\n";
											}
										}
										$pass = TRUE;
									} elseif(preg_match('/\.(css|log)$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/threadcaches':
									if(preg_match('/\.htm$/i', $entry)) {
										$pass = TRUE;
									}
								break;
							}
						}

					break;
					case 'templates' :
						if(preg_match('/\.(lang\.php|htm)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'include' :
						if(preg_match('/\.table$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'ipdata' :
						if($entry == 'wry.dat' || preg_match('/\.txt$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'admin' :
						if(preg_match('/\.md5$/i', $entry)) {
							$pass = TRUE;
						}
					break;
				}

				if(!$pass) {
					$nopass++;
					echo "<script>parent.$('checkresult').innerHTML += '$file<br>';</script>\r\n";
				}
			}
			ob_flush();
    			flush();
		}
	}
	return $nopass;
}

function checkdirs($currentdir) {
	global $dirlist;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..') {
			if(is_dir($file)) {
				$dirlist[] = $file;
				checkdirs($file.'/');
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $authkey;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen($file, "rb");
			$cachedata = fread($fp, filesize($file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Created: [\w\s,:]+\n\/\/Identify: (\w{32})\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$authkey) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = $addlist[$file] = '';
			}
		}

	}

	return array($showlist, $modifylist, $addlist);
}

?>