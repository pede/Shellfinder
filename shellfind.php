<?php
@require("config/config.php");
$db = new mysqli(_HOST,_USER,_PASS,_DB);
if($db->connect_errno > 0){
    die('DB CONNECTION ERROR');
}

$db->set_charset("utf8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">
  
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<title>Shell finder</title>
<style type="text/css">
.head {
  background:#6762FF;
  color:#FFFFFF;
  float:left;
  margin-right:10px;
  margin-bottom:5px;
  margin-top:10px;
  width:99%;
  position:relative;
  padding:5px
}
.info {
  background:#FFB757;
  color:#000000;
  float:left;
  margin-left:52px;
  margin-bottom:5px;
  width:91%;
  padding:5px
}
.fail {
  background:red;
  color:#000000;
  float:left;
  margin-left:52px;
  margin-bottom:5px;
  width:91%;
  padding:5px
}
b {
margin-right:20px;
}
</style>
</head><body>
<?php

$search = array("passwd","base64_decode","edoced_46esab","eval(","system(","exec(","safe_mode","myshellexec","shell_exec","backdoor","passthru","proc_open");
$shells = array("UnixOn","C99madShell","Spamfordz","Locus7s","c100","c99","x2300","cgitelnet","cybershell","STUNSHELL","Pr!v8","PHPShell","KaMeLeOn","S4T","oRb","tryag","sniper","r57","PHPJackal","PhpSpy","GiX","Fx29SheLL","w4ck1ng","milw0rm","PhpShell","k1r4","FeeLCoMz","FaTaLisTiCz","Ve_cENxShell");

function get_file_dir_tree($dir){
   $path = '';
   $stack[] = $dir;
   while ($stack) {
       $thisdir = array_pop($stack);
       if ($dircont = scandir($thisdir)) {
           $i=0;
           while (isset($dircont[$i])) {
               if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
                   $current_file = $thisdir.'/'.$dircont[$i];
                   if (is_file($current_file)) {
                       $path[] = $thisdir.'/'.$dircont[$i];
                   } elseif (is_dir($current_file)) {
                       $path[] = $thisdir.'/'.$dircont[$i];
                       $stack[] = $current_file;
                   }
               }
               $i++;
           }
       }
   }
   return $path;
}

function check_code($search, $file_code)
{
	$return = '';
	foreach ($search as $key)
	{
		if(stripos($file_code, $key))
		{
			if($return == '')
			{
				$return = $key;
			}else{
				$return = $return.' | '.$key;
			}
		}
	}
	return $return;
}

function BytetoFilesize($file) {
	$size =  filesize($file);
    $mod = 1024;
    $units = explode('|','B|KB|MB|GB|TB|PB');
    for ($i = 0; $size > $mod; $i++) {
        $size = $size / $mod;
    }
    return round($size, 2).' '.$units[$i];
}


foreach(get_file_dir_tree('www') as $file_path) // foreach file
{
	if (!stripos($file_path, basename(__FILE__))) // skip self
	{
		if (stripos($file_path, '.php') || stripos($file_path, '.txt') || stripos($file_path, '.phtml')) //  .txt, .php and .phtml
		{
			if(filesize($file_path) > 0)
			{

				$fingerprint = md5($file_path.filemtime($file_path).filesize($file_path));
				
				$dato = date('d-m-Y H:i:s');

				$result_file_path = $db->query("SELECT * FROM `shellfind` WHERE `file_path` = '".$file_path."'");
				$num_rows_file_path = $result_file_path->num_rows;
				
				$result_fingerprint = $db->query("SELECT * FROM `shellfind` WHERE `fingerprint` = '".$fingerprint."' AND `file_path` = '".$file_path."'");
				$num_rows_fingerprint = $result_fingerprint->num_rows;
				

				
				if (!$num_rows_file_path)
				{				
					$contents = '';
					$handle = fopen($file_path, 'r');
					$contents = fread($handle, filesize($file_path));
					fclose($handle);
					
					if(check_code($search, $contents)) //check the code
					{
						$bakfile = './temp/'.basename($file_path).'.bak';
						if(copy($file_path, $bakfile))
						{
							?>
								<span class="head"><strong><?php echo $file_path; ?></strong></span><br><br>
								<span class="info"><b>Found tags:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php echo check_code($search, $contents); ?></span>
								<span class="info"><b>Last file change:</b> <?php echo date('d-m-Y H:i:s', filemtime($file_path)); ?></span>
								<span class="info"><b>Filesize:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo BytetoFilesize($file_path); ?></span>
								<span class="info"><b>Possibly Shell:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo check_code($shells, $contents); ?></span><br>
								<span class="info"><b>Fingerprint:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $fingerprint; ?></span><br>
							<?php
							if ($num_rows_fingerprint)
							{
								$db->query("UPDATE `shellfind` SET `fingerprint`='".$fingerprint."',`last_scan_date`='".$dato."' WHERE `file_path` = '".$file_path."'");
							}else{
								$db->query("INSERT INTO `shellfind`(`fingerprint`, `file_path`, `last_scan_date`) VALUES ('".$fingerprint."', '".$file_path."', '".$dato."')");
							}
							unlink($file_path);
						}else{
							?>
								<span class="head"><strong><?php echo $file_path; ?></strong></span><br><br>
								<span class="fail"><b>Found tags:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php echo check_code($search, $contents); ?></span>
								<span class="fail"><b>Last file change:</b> <?php echo date('d-m-Y H:i:s', filemtime($file_path)); ?></span>
								<span class="fail"><b>Filesize:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo BytetoFilesize($file_path); ?></span>
								<span class="fail"><b>Possibly Shell:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo check_code($shells, $contents); ?></span><br>
								<span class="fail"><b>Fingerprint:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $fingerprint; ?></span><br>
							<?php

						}
						

					}

				}		
			}
		}
	}
}

?>
</body>
</html>