<?php
require_once 'functions.php';

$file = fopen("lots.csv","r");
$a=array();
$noowner = array();
$ii=0;

global $conn;
dbConnect();

$sql = "INSERT INTO `lot`(`code`, `dateacquired`, `lotsize`,`lot`, `block`, `phase`,`user`) VALUES ";
$inserts = array();
while(! feof($file))
  {
    $row = fgetcsv($file);
    $tname = explode(" ", trim($row[4]));
    $tfname =trim($tname[0]);
    $tlname =  array_pop($tname);
    
    
    $stmt=$conn->prepare("SELECT id,formatName(lastname,firstname,middlename) AS fullname FROM homeowner WHERE active=1 AND UPPER(TRIM(lastname)) LIKE '%".$tlname."' AND UPPER(TRIM(firstname)) LIKE '".$tfname."%' ");
    if($stmt === false) {
        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
    }    
    $stmt->bind_param('ss',$tlname,$tfname);
    
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($uid,$fname);
    while($stmt->fetch()){}
//        array_push($row, $uid);
//        array_push($row, $fname);
//        array_push($row, $tlname);
//        array_push($row, $tfname);
//        $ii++;
//    }
    array_push($row, $row[1]."B".str_pad($row[2],2,'0',STR_PAD_LEFT)."L".str_pad($row[3],2,'0',STR_PAD_LEFT));
    if($stmt->num_rows == 1)
    {
        
        array_push($row, $uid);
        array_push($row, $fname);
        array_push($row, $tlname);
        array_push($row, $tfname);
        array_push($a,$row);  
        
//        array_push($inserts,"('".$row[5]."',".$row[6].",'".$row[0]."',0,'".$row[3]."','".$row[2]."','".$row[1]."',1)");
    }
    else
    {
        array_push($noowner,$row);
        array_push($inserts,"('".$row[5]."','".$row[0]."',0,'".$row[3]."','".$row[2]."','".$row[1]."',1)");
    }
    $stmt->close();
      echo '"'.$row[5].'","'.$row[0].'","'.$row[1].'","'.$row[2].'","'.$row[3].'","'.$row[4].'"'."\n";
  }
  $sql .= implode(",", $inserts);
dbClose();
//echo $sql;
//echo "numrows = ".$ii;
//print_r($a);
//echo "<!-- *************************************************************************************** -->
//    ";
//var_dump($noowner);

fclose($file);