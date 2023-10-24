<?php
ob_start();
//config
setlocale(LC_ALL, 'ms_MY');
date_default_timezone_set('Asia/Kuala_Lumpur');
$folder = 'C:\wwwroot\\';
$delay_seconds = "30"; 

echo "<br>Monitoring : {$folder} ";
echo "<br>Every : {$delay_seconds} seconds...";
echo "<br>Start time:".date('Y-m-d h:i:s a');

//recurse all files
function listFilesRecursively($folder)
{
    $files = [];

    $directory = new RecursiveDirectoryIterator($folder);
    $iterator = new RecursiveIteratorIterator($directory);

    foreach ($iterator as $file) {
        if ($file->isFile()) {
			$files[md5($file->getPathname())] = [
				"path"=>$file->getPathname(),
				"mtime"=>$file->getMTime(),
				//"mtime"=>date("Y-m-d h:i:s a",$file->getMTime()),
			];
        }
    }

    return $files;
}

// Specify the folder to list files recursively
$now_files = listFilesRecursively($folder);
//print_r($now_files);

//if previous files exists
if( file_exists("previous.php") ){
	$previous_files = unserialize(file_get_contents("previous.php"));
	//print_r($previous_files);
	//check new files
	//check for changed files
	//check deleted files
	
	$changed_files = [];
	if( file_exists("changed.php") ){
		if( unserialize(file_get_contents("changed.php")) !== FALSE ){
			$changed_files = unserialize(file_get_contents("changed.php"));
		}
	}	
	
	foreach($previous_files as $key=>$prev){
		if(!empty($now_files[$key])){
			
			if( empty($changed_files[$key]) ){
				$changed_files[$key] = [];
			}
			
			echo "<br>comparing:" .$prev['path'];
			echo "<br>Previous version modify time:".$prev["mtime"] ;
			echo "<br>Current version modify time:".$now_files[$key]['mtime'] ;
			if( $prev["mtime"] != $now_files[$key]['mtime'] ){
				echo "<br>File change detected :".$now_files[$key]["path"];
				$time = date("Y-m-d h:i a");
				if( @!in_array($time,$changed_files[$key]) ){
					$changed_files[$key][] = $time;
				}
			}			
			//changed
			//if( $prev["mtime"] != $now_files[$key]['mtime'] ){
				//echo "<br>Changed file :".$now_files["path"];
				//$time = date("Y-m-d h:i a");
				//if( !in_array($time,$changed_files[$key]) ){
					//$changed_files[$key][] = $time;
				//}
			//}
		}else{
			//deleted
		}
	}
	
	if( !empty($changed_files) ){
		file_put_contents("changed.php",serialize($changed_files));
	}
}

//update previous file
file_put_contents("previous.php",serialize($now_files));

echo '
<head>
  <meta http-equiv="Refresh" content="'.$delay_seconds.'; URL='.basename(__FILE__).'" />
</head>
';

echo "<br>End time:".date('Y-m-d h:i:s a');

echo "<br>Changed files:";
@print_r($changed_files);

ob_clean();
ob_start();

echo '
<head>
  <meta http-equiv="Refresh" content="'.$delay_seconds.'; URL='.basename(__FILE__).'" />
  <title>Monitoring Files</title>
</head>
';


?>


<!-- Font Awesome -->
<link
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
  rel="stylesheet"
/>
<!-- Google Fonts -->
<link
  href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
  rel="stylesheet"
/>
<!-- MDB -->
<link
  href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css"
  rel="stylesheet"
/>

<!-- MDB -->
<script
  type="text/javascript"
  src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"
></script>

<!-- Start your project here-->
<div class="container">
  <div class="d-flex" style="height: 100vh">
    <div class="text-left">
		File progress : Running at <?php echo date('h:i:s A') ?>
		<table class="table table-bordered sortable">
			<thead>
				<tr>
					<th>#</th>
					<th>Path</th>
					<th>Changed Times</th>
					<th id="row-total">Total (mins)</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$total_minutes = 0;
					$bil = 1;
					foreach($changed_files as $md5=>$time){
				?>
				<tr>
					<td><?php echo $bil++?></td>
					<td><?php echo $now_files[$md5]['path'] ?></td>
					<td>
						<?php 
						if( is_array($time) ){
							$times = implode("<br>",$time);
							echo $times;
							$total_minutes += count($time) ;
						}
						?>
					</td>
					<td>
						<?php 
						echo @count( $time );
						?>
					</td>
				</tr>	
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td><?php echo $total_minutes ?></td>
				</tr>			
			</tfoot>
		</table>
    </div>
  </div>
</div>
<!-- End your project here-->

<link href="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/sortable.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/sortable.min.js"></script>

<script>
  window.addEventListener('load', function () {
    const el = document.getElementById('row-total');
    if (el) {
      el.click();
    }
  })
</script>
