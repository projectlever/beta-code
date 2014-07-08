<?php 
$files = glob("./backups/*.json");
$old_data = json_decode(file_get_contents($files[count($files)-1]),true);
$backups = array();
$backups["date"] = "Backup occurred on " . date("m/d/y") . " at " . date("H:i:s");

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if (mysqli_connect_errno($con))
  die("Failed to connect to MySQL: " . mysqli_connect_error($con));
foreach ($old_data as $type=>$ids){
  foreach ($ids as $id=>$data){
    $sql = "UPDATE `$type` SET `Department`='".mysqli_real_escape_string($con,$data["Department"])."',`School`='".mysqli_real_escape_string($con,$data["School"])."',`University`='".mysqli_real_escape_string($con,$data["University"])."',`Block`='".mysqli_real_escape_string($con,$data["Block"])."',`Header`='".mysqli_real_escape_string($con,$data["Header"])."',`Info`='".mysqli_real_escape_string($con,$data["Info"])."',`Blob`='".mysqli_real_escape_string($con,$data["Blob"])."',`Picture`='".mysqli_real_escape_string($con,$data["Picture"])."',`Link`='".mysqli_real_escape_string($con,$data["Link"])."',`Processed_Text`='".mysqli_real_escape_string($con,$data["Processed_Text"])."' WHERE `".$type."_ID`=".$id;
    $result = mysqli_query($con,$sql);
    if ( !$result )
      die("ERROR! " . mysqli_error($con));
    else {
      $backups[$id] = $data;
      echo "Successfully backed up $type #$id<br/>";
    }
  }
}
file_put_contents("backup_log.json",json_encode($backups));
mysqli_close($con);
echo "<pre>";
print_r($backups);
echo "</pre>";
?>
