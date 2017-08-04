<?php require_once('Connections/link.php');?>
<?php

$reUpload = 0;

$payment_files = getIsPayment();
$formatPaymentData = formatPaymentTxt($payment_files);
echo '<pre>';
echo "Search status array size: ", var_dump($payment_files), "</br>";
echo '</pre>';

// $uploadFileName = saveUploadTxtFile($formatPaymentData);
// uploadFileToFtp($uploadFileName);

function getIsPayment()
{
    // SQL 取出fee_order符合資料
    $searchStatusSqlCmd = "SELECT *
                           FROM fee_order
                           WHERE status
                           LIKE BINARY '%已繳費%'
                           AND upload_time IS NULL";
    $searchStatusFromSql = mysql_query($searchStatusSqlCmd);
    if ($searchStatusFromSql) {
        $i = 0;
        while ($row= mysql_fetch_assoc($searchStatusFromSql)) {
            $payment_files[$i] = array(
              $row['account'],
              $row['household_number'],
              $row['begin_time'],
              $row['virtual_account_id'],
              $row['pay_time'],
              $row['payment_way'],
              $row['amount_payable']
            );

            $i++;
        }
    } else {
        echo "Search not found From fee_order.", "</br>";
    }

    return $payment_files;
}

function formatPaymentTxt($payment_files)
{
    $success = 10;
    $error = 0;

    $id = substr($payment_files[194][0], 0, 5);
    $accountId = $payment_files[194][1];
    $creatDate=$payment_files[194][2];
    $virtualAccount=$payment_files[194][3];
    $payDate=$payment_files[194][4];
    $paymentWay =$payment_files[194][5];
    $collectionStore='7-11';
    $collectionMoney='7';
    $fees='7';
    $inBankMoney=$payment_files[194][6];

    $id = str_pad($id, 5);
    $accountId = str_pad($accountId, 10);
    $creatDate = str_pad(convertDateToUploadFtpFormat($creatDate, 'Y-m'), 5);
    $virtualAccount = str_pad($virtualAccount, 14);
    $inBankDate = str_pad(getInBankDateFromPaymentWay($payDate, $paymentWay), 7);
    $payDate = str_pad(convertDateToUploadFtpFormat($payDate), 7);
    $collectionStore = str_pad($collectionStore, 8);
    $collectionMoney = str_pad($collectionMoney, 14);
    $fees = str_pad($fees, 7);
    $inBankMoney = str_pad($inBankMoney, 14);

    if (strlen($id) != 5) {
        echo '[1] id length error.', '</br>';
        $error++;
    }

    if (strlen($accountId) != 10) {
        echo '[2] accountId length error.'.'</br>';
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

    if (strlen($collectionMoney) < 0) {
        echo '[8] collection money length error', '</br>';
        $error++;
    }

    if (strlen($fees) < 0) {
        echo '[9] fees length error', '</br>';
        $error++;
    }

    if (strlen($inBankMoney) < 0) {
        echo '[10] in bank money length error', '</br>';
        $error++;
    }

    $formatPaymentData = $id.$accountId.$creatDate.$virtualAccount.$payDate.$inBankDate.$collectionStore.$collectionMoney.$fees.$inBankMoney;

    echo '</br>';
    echo 'Check Upload Format, Success: '. ($success - $error). ', Error: '. $error. '</br>';
    echo $formatPaymentData.'</br>';
    echo 'length : '.strlen($formatPaymentData).'</br>';


    return $formatPaymentData;
}

function saveUploadTxtFile($formatPaymentData)
{
    date_default_timezone_set("Asia/Taipei");
    $nowDate = date('Ymd');
    $fileName = 'ToCVMS -'.$nowDate.'.txt';
    $myfile = fopen($fileName, "w") or die("Unable to open file!");
    fwrite($myfile, $formatPaymentData);
    fclose($myfile);

    return $fileName;
}

function uploadFileToFtp($uploadFileName)
{
    $fp = fopen($uploadFileName, 'r');

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

        if ($reUpload < 3) {
            sleep(900);
            uploadFileToFtp();
            $reUpload++;
        } else {
            $logMessage = 'Re Upload All Error.';
        }
    }
    saveLogTxtFile($logMessage);

    ftp_close($conn_id);
    fclose($fp);
}

function saveLogTxtFile($message)
{
    date_default_timezone_set("Asia/Taipei");
    $nowDate = date('Y-m-d H:i:s');
    $fileName = 'TokyoCVMSLog.txt';
    $myfile = fopen($fileName, 'a') or die("Unable to open file!");
    $txt = $nowDate.'      '.$message.PHP_EOL;
    fwrite($myfile, $txt);
    fclose($myfile);
}

function convertDateToUploadFtpFormat($date, $format = 'Y-m-d', $addDayCount = 0)
{
    date_default_timezone_set("Asia/Taipei");
    $tempYear = date('Y', strtotime($date)) - 1911;
    $tempMonthAndDay = '';
    if ($format == 'Y-m-d') {
        $tempMonth = date('m', strtotime($date));
        $tempDay = date('d', strtotime($date)) + $addDayCount;
        $tempMonthAndDay = $tempMonth.$tempDay;
    }
    if ($format == 'Y-m') {
        $tempMonthAndDay = date('m', strtotime($date));
    }
    $convertDate = $tempYear.$tempMonthAndDay;

    echo 'convert date: '.$convertDate.'</br>';

    return $convertDate;
}

function getInBankDateFromPaymentWay($payDate, $paymentWay)
{
    date_default_timezone_set("Asia/Taipei");
    $weekday = date("w", $payDate);   // Sunday = 0, Saturday = 6
    $weekdayCount = 0;
    // 判斷是否在假日
    if ($weekday == 0) {
        $weekdayCount = 1;
    }
    if ($weekday == 6) {
        $weekdayCount = 2;
    }

    switch ($paymentWay) {
      case 0:
        // 信用卡
        $inBankDate = convertDateToUploadFtpFormat($payDate, 'Y-m-d', 1 + $weekdayCount);
        break;
      case 1:
        // 臨櫃匯款
        $inBankDate = convertDateToUploadFtpFormat($payDate, 'Y-m-d', 1 + $weekdayCount);
        break;
      case 2:
        // ATM轉帳
        $inBankDate = convertDateToUploadFtpFormat($payDate, 'Y-m-d', 1 + $weekdayCount);
        break;
      case 3:
        // 超商代收
        $inBankDate = convertDateToUploadFtpFormat($payDate, 'Y-m-d', 3 + $weekdayCount);
        break;
      default:
        $inBankDate = '00000000';
        // 沒繳錢
        break;
    }

    return $inBankDate;
}

function insertUploadTimeToDb() {

}

?>
