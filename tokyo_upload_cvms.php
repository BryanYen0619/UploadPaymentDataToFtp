<!DOCTYPE html>
<html>
<body>

<?php

$payment_files = getIsPayment();

echo '<pre>';
echo "Search status array size: ", var_dump($payment_files), "</br>";
echo '</pre>';

function getIsPayment(){
  $serverName = "localhost";
  $userName = "root";
  $password = "Abcd1234";
  $dbName = "tokyo_payment_test";

  // Create connection
  $mysqli = new mysqli($serverName, $userName, $password, $dbName);
  mysqli_query($mysqli,"SET CHARACTER SET UTF8");
  // Check connection
  if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
  }

  // SQL 取出fee_tky_parsered所有資料
  $searchStatusSqlCmd = "SELECT id, household_number, status
                         FROM fee_order
                         WHERE status
                         LIKE BINARY '%已繳費%'
                         AND upload_time IS NULL";
  $searchStatusFromSql = $mysqli->query($searchStatusSqlCmd);
  // if ($searchStatusFromSql) {
  //     $row = $searchStatusFromSql->fetch_array(MYSQLI_ASSOC);
  //     echo "DB search status count: ", $searchStatusFromSql->num_rows, "</br>";
  // } else {
  //     echo "Search not found From fee_order.", "</br>";
  // }

  if ($searchStatusFromSql) {
    $i = 0;
    while($row=$searchStatusFromSql->fetch_array(MYSQLI_ASSOC)){
        $payment_files[$i] = $row['household_number'];
        $i++;
    }
  } else {
      echo "Search not found From fee_order.", "</br>";
  }

  $searchStatusFromSql ->free();
  // Close DB
  $mysqli->close();

  return $payment_files;
}

?>

</body>
</html>
