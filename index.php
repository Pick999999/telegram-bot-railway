<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// รับค่าจาก Environment
/*
$botToken = getenv('BOT_TOKEN');
if (!$botToken) {
    file_put_contents("error.log", date("c") . " : BOT_TOKEN ไม่ถูกตั้งค่า\n", FILE_APPEND);
    exit;
}
*/
$raw_chat_id = '8068993219' ;
$botToken = "7309653342:AAFalGA-wBjF1AauCR47r0xkHP2OueYfBFo";

$apiURL = "https://api.telegram.org/bot$botToken/";
$content = file_get_contents("php://input");
//file_put_contents("telegram_log.txt", date("c") . " : " . $content . "\n", FILE_APPEND);

$update = json_decode($content, true);
if (isset($update["message"]["chat"]["id"], $update["message"]["text"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];
    $reply = "Echo: " . $text;
    //sendTelegramMessage($chatId, $reply, $apiURL);
    $response = ManageBOTMessage($chatId,$text,$apiURL);
} else {
    $raw_chat_id = '8068993219' ;
    $reply = $_GET['message'] ;
    sendTelegramMessage($raw_chat_id, $reply, $apiURL);
}

http_response_code(200);


function sendTelegramMessage($chatId, $text, $apiURL) {

    $ch = curl_init($apiURL . "sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $chatId,
            'text' => $text
        ],
    ]);
    $res = curl_exec($ch);
    if ($err = curl_error($ch)) {
        file_put_contents("curl_error.txt", date("c") . " : " . $err . "\n", FILE_APPEND);
    }
    curl_close($ch);
    //file_put_contents("telegram_response.txt", date("c") . " : " . $res . "\n", FILE_APPEND);
}


function ManageBOTMessage($chatId,$textRecive,$apiURL) { 


    //$chatId = $update["message"]["chat"]["id"];
    //$textRecive = $update["message"]["text"];
	
     $st = substr($textRecive,0,3);
     if (strtolower($textRecive) === 'ot-' ) {
	    $responseText = UpdatePageTradeStatus('Y',$textRecive,$chatId,$apiURL); 
		sendTelegramMessage($chatId, $responseText, $apiURL);
		return ;
     } 
	 if (strtolower($textRecive) === 'clt' ) {
	    $responseText = UpdatePageTradeStatus('N',$textRecive,$chatId,$apiURL); 
		sendTelegramMessage($chatId, $responseText, $apiURL);
		return ;
     } 

     $reply = 'echo->' . $textRecive ;
     sendTelegramMessage($chatId, $reply, $apiURL);
     
} // end function


function UpdatePageTradeStatus($tradeStatus,$textRecive,$chatId,$apiURL) { 

$st = substr($textRecive,0,3);
$assetCode2 = '';
if (strtolower($st) === 'ot-' ) {
	$stAr = explode("-",$st);
	$assetCode = $stAr[1] ;
	if ($assetCode===1) { $assetCode2 = 'R_25' ;  }
	if ($assetCode===2) { $assetCode2 = 'R_50' ;  }
	if ($assetCode===3) { $assetCode2 = 'R_75' ;  }
	if ($assetCode===4) { $assetCode2 = 'R_100' ;  }
}  

$url = 'https://thepapers.in/deriv/updatePageTrade.php';
$parameters = array(
    'assetCode' => $assetCode2,
    'isOpenTrade' => $tradeStatus,
    'moneyTrade' => 1,
    'targetTrade' => 1.5
);

// สร้าง query string จาก array ของ parameters
$queryString = http_build_query($parameters);
$fullUrl = $url . '?' . $queryString;

// เริ่มต้น cURL session
$ch = curl_init();
// ตั้งค่า cURL options
curl_setopt($ch, CURLOPT_URL, $fullUrl); // กำหนด URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ให้ cURL ส่งผลลัพธ์กลับมาเป็น string แทนที่จะแสดงออกทางหน้าจอ

// ในกรณีที่ URL เป็น HTTPS และมีปัญหาเรื่อง SSL certificate, คุณอาจจะต้องเพิ่มบรรทัดนี้
// แต่ควรใช้ด้วยความระมัดระวังและทำความเข้าใจความเสี่ยงด้านความปลอดภัย
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// ประมวลผล cURL request และเก็บผลลัพธ์
$response = curl_exec($ch);

// ตรวจสอบว่ามี error เกิดขึ้นหรือไม่
if (curl_errno($ch)) {
   // echo 'cURL Error: ' . curl_error($ch);
} else {
    // แสดงผลลัพธ์
    //echo 'API Response: <pre>';
    //echo htmlentities($response); // ใช้ htmlentities เพื่อป้องกันปัญหาการแสดงผล HTML/script ที่มาจาก response
    //echo '</pre>';
}

curl_close($ch);


return $response ;


} // end function

function sendTelegramTable($apiURL, $chatId,$headTable, $tableData) {
    // สร้างตารางด้วย Monospace font
    $message = "<b>📊 รายงานข้อมูล</b>\n\n";
    $message .= "<code>";
    //$message .= "ชื่อ        อายุ   เงินเดือน\n";
    $message .=  $headTable . "\n";
    $message .= "─────────────────────────\n";
    
    foreach ($tableData as $row) {
        $message .= sprintf("%-10s %3d   %8s\n", 
            $row['name'], 
            $row['age'], 
            number_format($row['salary']));
    }
    $message .= "</code>";
    
    //$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
	$url = $apiURL ;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

?>
