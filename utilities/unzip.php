<?php

define('APPNAME', 'Discuz!');
define('ZIPFILE', 'discuz.zip');


if(empty($_POST['path'])) {

showheader();

?>
<form method="post">
请输入解压缩的路径: <input name="path" />
<input type="submit" value="确定" />
</form>
<?

showfooter();

exit;

} else {

	showheader();

	$unzip = new SimpleUnzip();
	$unzip->ReadFile(ZIPFILE);

	if($unzip->Count() == 0 || $unzip->GetError(0) != 0) {
		instmsg('压缩包错误，请重新上传');
	}

	$filecount = 0;
	$sizecount = 0;
	foreach($unzip->Entries as $entry) {
		!file_exists($_POST['path'].'/'.$entry->Path) && make_dir($_POST['path'].'/'.$entry->Path);
		$filename = $_POST['path'].'/'.$entry->Path.'/'.$entry->Name;
		if(is_writable($filename)) {
			if(!$handle = @fopen($filename, 'a')) {
				instmsg("不能打开文件 $filename");
			}
			if(@fwrite($handle, $entry->Data) === FALSE) {
				instmsg("不能写入到文件 $filename");
			}
			@fclose($handle);
		} else {
			instmsg("文件 $filename 不可写");
		}
		$filecount++;
	}

	echo '解压缩完毕，创建'.$filecount.' 个文件，<a href="'.$_POST['path'].'/install/">点击这里安装 '.APPNAME.'</a>';

	showfooter();


}

function make_dir($path) {
	$chunk = explode('/', $path);
	$pp = '';
	foreach($chunk as $p) {
		if(substr($p, -1) != '.' && !is_dir($pp.$p)) {
			@mkdir($pp.$p, 0777);
		}
		$pp .= $p.'/';
	}
	return is_dir($path);
}

function instmsg($message, $url_forward = '') {
	global $lang, $msglang;

	$message = $msglang[$message] ? $msglang[$message] : $message;
	echo '<center><b>系统提示信息</b><br />'.$message.'<br /><br /></center>';


	$url = "<a href=\"javascript:history.go(-1);\">返回</a>";

	echo $url ? "<center>$url</center>" : '';
	showfooter();
}

function showheader() {
	global $version_old, $version_new;
	$APPNAME = APPNAME;

	print <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>$APPNAME 解压缩程序</title>
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
$APPNAME 解压缩程序</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr>
<td align="center">
<b>本解压缩程序负责程序的解压缩，请确认已经上传所有文件，并做好数据备份<br />
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

class SimpleUnzip {

        var $Comment = '';
        var $Entries = array();
        var $Name = '';
        var $Size = 0;
        var $Time = 0;

	function SimpleUnzip($in_FileName = '') {
		if($in_FileName !== '') {
			SimpleUnzip::ReadFile($in_FileName);
		}
	}

	function Count() {
		return count($this->Entries);
	}

        function GetData($in_Index) {
		return $this->Entries[$in_Index]->Data;
        }

        function GetEntry($in_Index) {
		return $this->Entries[$in_Index];
        }

        function GetError($in_Index) {
		return $this->Entries[$in_Index]->Error;
        }

        function GetErrorMsg($in_Index) {
		return $this->Entries[$in_Index]->ErrorMsg;
        }

        function GetName($in_Index) {
		return $this->Entries[$in_Index]->Name;
        }

        function GetPath($in_Index) {
		return $this->Entries[$in_Index]->Path;
        }

        function GetTime($in_Index) {
		return $this->Entries[$in_Index]->Time;
        }

        function ReadFile($in_FileName) {
		$this->Entries = array();

		$this->Name = $in_FileName;
		$this->Time = filemtime($in_FileName);
		$this->Size = filesize($in_FileName);

		$oF = fopen($in_FileName, 'rb');
		$vZ = fread($oF, $this->Size);
		fclose($oF);
		$aE = explode("\x50\x4b\x05\x06", $vZ);
		$aP = unpack('x16/v1CL', $aE[1]);
		$this->Comment = substr($aE[1], 18, $aP['CL']);

		$this->Comment = strtr($this->Comment, array("\r\n" => "\n", "\r"   => "\n"));

		$aE = explode("\x50\x4b\x01\x02", $vZ);
		$aE = explode("\x50\x4b\x03\x04", $aE[0]);
		array_shift($aE);

		foreach($aE as $vZ) {
			$aI = array();
			$aI['E']  = 0;
			$aI['EM'] = '';

			$aP = unpack('v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL', $vZ);

			$bE = ($aP['GPF'] && 0x0001) ? TRUE : FALSE;
			$nF = $aP['FNL'];

			if($aP['GPF'] & 0x0008) {
				$aP1 = unpack('V1CRC/V1CS/V1UCS', substr($vZ, -12));
				$aP['CRC'] = $aP1['CRC'];
				$aP['CS']  = $aP1['CS'];
				$aP['UCS'] = $aP1['UCS'];
				$vZ = substr($vZ, 0, -12);
			}

			$aI['N'] = substr($vZ, 26, $nF);
			if(substr($aI['N'], -1) == '/') {
				continue;
			}

			$aI['P'] = dirname($aI['N']);
			$aI['P'] = $aI['P'] == '.' ? '' : $aI['P'];
			$aI['N'] = basename($aI['N']);
			$vZ = substr($vZ, 26 + $nF);
			if(strlen($vZ) != $aP['CS']) {
				$aI['E']  = 1;
				$aI['EM'] = 'Compressed size is not equal with the value in header information.';
			} else {
				if($bE) {
					$aI['E']  = 5;
					$aI['EM'] = 'File is encrypted, which is not supported from this class.';
				} else {
					switch($aP['CM']) {
						case 0:
						break;
						case 8:
							$vZ = gzinflate($vZ);
						break;
						case 12:
							if(!extension_loaded('bz2')) {
                                    				if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
									@dl('php_bz2.dll');
								} else {
									@dl('bz2.so');
								}
							}
							if(extension_loaded('bz2')) {
								$vZ = bzdecompress($vZ);
							} else {
								$aI['E']  = 7;
								$aI['EM'] = "PHP BZIP2 extension not available.";
							}
						break;
						default:
							$aI['E']  = 6;
							$aI['EM'] = "De-/Compression method {$aP['CM']} is not supported.";
					}

					if(!$aI['E']) {
						if($vZ === FALSE) {
							$aI['E']  = 2;
							$aI['EM'] = 'Decompression of data failed.';
						} else {
							if(strlen($vZ) != $aP['UCS']) {
								$aI['E']  = 3;
								$aI['EM'] = 'Uncompressed size is not equal with the value in header information.';
							} else {
								if(crc32($vZ) != $aP['CRC']) {
									$aI['E']  = 4;
									$aI['EM'] = 'CRC32 checksum is not equal with the value in header information.';
								}
							}
						}
					}
				}
			}
			$aI['D'] = $vZ;

			$aI['T'] = mktime(($aP['FT']  & 0xf800) >> 11,
				($aP['FT']  & 0x07e0) >>  5,
				($aP['FT']  & 0x001f) <<  1,
				($aP['FD']  & 0x01e0) >>  5,
				($aP['FD']  & 0x001f),
				(($aP['FD'] & 0xfe00) >>  9) + 1980);
			$this->Entries[] = &new SimpleUnzipEntry($aI);
		}
		return $this->Entries;
	}

}

class SimpleUnzipEntry {

        var $Data = '';
        var $Error = 0;
        var $ErrorMsg = '';
        var $Name = '';
        var $Path = '';
        var $Time = 0;

        function SimpleUnzipEntry($in_Entry) {
		$this->Data     = $in_Entry['D'];
		$this->Error    = $in_Entry['E'];
		$this->ErrorMsg = $in_Entry['EM'];
		$this->Name     = $in_Entry['N'];
		$this->Path     = $in_Entry['P'];
		$this->Time     = $in_Entry['T'];
	}

}

?>