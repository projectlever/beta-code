<?php 
session_start();

$out = array("schools"=>array(),"departments"=>array());
$university = $_SESSION["university"];

$schools = array();
$departments = array();

$univData = json_decode(file_get_contents("/home/svetlana/www/import/data/".$university."/importers/schoolDepartmentData.json"),true);
foreach ( $univData as $schoolName => $departmentNames ){
  $schools[] = $schoolName;
  for ( $i = 0, $n = count($departmentNames); $i < $n; $i++ ){
    if (!$departments[$departmentNames[$i]]){
      $departments[$departmentNames[$i]] = array($schoolName);
    }
    elseif(!in_array($schoolName,$departments[$departmentNames[$i]])){
      array_push($departments[$departmentNames[$i]],$schoolName);
    }
  }
}

asort($schools);
ksort($departments);

$out["schools"] = $schools;
$out["departments"] = $departments;

echo json_encode($out);

?>
