<!DOCTYPE html>
<html>
<body>

<?php

$payment_files = getIsPayment();
formatPaymentTxt();
// echo '<pre>';
// echo "Search status array size: ", var_dump($payment_files), "</br>";
// echo '</pre>';

function getIsPayment()
{
    $serverName = "localhost";
    $userName = "root";
    $password = "Abcd1234";
    $dbName = "tokyo_payment_test";

  // Create connection
  $mysqli = new mysqli($serverName, $userName, $password, $dbName);
    mysqli_query($mysqli, "SET CHARACTER SET UTF8");
  // Check connection
  if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
  }

  // SQL 取出fee_order符合資料
  $searchStatusSqlCmd = "SELECT id, household_number, status
                         FROM fee_order
                         WHERE status
                         LIKE BINARY '%已繳費%'
                         AND upload_time IS NULL";
    $searchStatusFromSql = $mysqli->query($searchStatusSqlCmd);
    if ($searchStatusFromSql) {
        $i = 0;
        while ($row=$searchStatusFromSql->fetch_array(MYSQLI_ASSOC)) {
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

function formatPaymentTxt()
{
    $success = 10;
    $error = 0;

    $id = '34';
    $accountId = 'testasaaaa';
    $creatDate='10608';
    $virtualAccount='jgagjalsgmcadf';
    $payDate='1060710';
    $inBankDate='1060713';
    $collectionStore='asdfghjj';
    $collectionMoney=200;
    $fees=30;
    $inBankMoney=200;

    $id=str_pad($id,5,'_',STR_PAD_LEFT);
    $accountId=str_pad($accountId,10,'_', STR_PAD_LEFT);
    $creatDate=str_pad($creatDate, 5, '_', STR_PAD_LEFT);
    $virtualAccount =str_pad($virtualAccount, 14, '_', STR_PAD_LEFT);
    $payDate=str_pad($payDate, 7, '_', STR_PAD_LEFT);
    $inBankDate=str_pad($inBankDate, 7, '_', STR_PAD_LEFT);
    $collectionStore=str_pad($collectionStore, 8, '_', STR_PAD_LEFT);
    $collectionMoney=str_pad($collectionMoney, 14, 0, STR_PAD_LEFT);
    $fees=str_pad($fees, 7, 0 , STR_PAD_LEFT);
    $inBankMoney=str_pad($inBankMoney, 14, 0, STR_PAD_LEFT);

    if (strlen($id) != 5) {
        echo '[1] id length error.', '</br>';
        $error++;
    }

    if (strlen($accountId) != 10) {
        echo '[2] accountId length error.', '</br>';
        $error++;
    }

    if (strlen($creatDate) != 5) {
        echo '[3] create date length error', '</br>';
        $error++;
    }

    if (strlen($virtualAccount) != 14) {
        echo '[4] virtual account length error', '</br>';
        $error++;
    }

    if (strlen($payDate) != 7) {
        echo '[5] pay date length error', '</br>';
        $error++;
    }

    if (strlen($inBankDate) != 7) {
        echo '[6] in bank date length error', '</br>';
        $error++;
    }

    if (strlen($collectionStore) != 8) {
        echo '[7] collection store length error', '</br>';
        $error++;
    }

    if ($collectionMoney == 0) {
        echo '[8] collection money length error', '</br>';
        $error++;
    }

    if ($fees < 0) {
        echo '[9] fees length error', '</br>';
        $error++;
    }

    if ($inBankMoney == 0) {
        echo '[10] in bank money length error', '</br>';
        $error++;
    }

    echo '</br>';
    echo 'Check Upload Format, Success: '. ($success - $error). ', Error: '. $error. '</br>';

    echo $id.$accountId.$creatDate.$virtualAccount.$payDate.$inBankDate.$collectionStore.$collectionMoney.$fees.$inBankMoney;




}

function saveUploadTxtFile() {
  date_default_timezone_set("Asia/Taipei");
  $nowDate = date('Ymd');


  $fileName = 'ToCVMS -'.$nowDate.'.txt';
  $myfile = fopen($fileName, "w") or die("Unable to open file!");
  $txt = "Hello World PHP";
  fwrite($myfile, $txt);
  fclose($myfile);
}

function uploadFileToFtp() {
  $file = 'upload_file.txt';   ### 上傳的檔案
  $fp = fopen($file, 'r');

  ### 連接的 FTP 伺服器是 localhost
  $conn_id = ftp_connect('localhost');

  ### 登入 FTP, 帳號是 USERNAME, 密碼是 PASSWORD
  $login_result = ftp_login($conn_id, 'root', 'Abcd1234');

  $logMessage = '';
  if (ftp_fput($conn_id, $file, $fp, FTP_ASCII)) {
      echo "成功上傳 $file\n";
      $logMessage = 'upload success.';
  } else {
      echo "上傳檔案 $file 失敗\n";
      $logMessage = 'upload error.';
  }
  saveLogTxtFile($logMessage);

  ftp_close($conn_id);
  fclose($fp);
}

function saveLogTxtFile($message) {
  date_default_timezone_set("Asia/Taipei");
  $nowDate = date('Y-m-d H:i:s');


  $fileName = 'TokyoCVMSLog.txt';
  $myfile = fopen($fileName, 'a') or die("Unable to open file!");
  $txt = $nowDate.'      '.$message.PHP_EOL;
  fwrite($myfile, $txt);
  fclose($myfile);
}

?>

</body>
</html>
