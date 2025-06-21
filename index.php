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
    file_put_contents("telegram_response.txt", date("c") . " : " . $res . "\n", FILE_APPEND);
}


function ManageBOTMessage($chatId,$textRecive,$apiURL) { 

    $chatId = $update["message"]["chat"]["id"];
    $textRecive = $update["message"]["text"];
	$reply = 'echo->' . $textRecive ;
    
     if (strtolower($textRecive) == 'starttrade' ) {
	    UpdatePageTradeStatus();
     } else {
        sendTelegramMessage($ChatId, $reply, $apiURL);
     }

} // end function

function UpdatePageTradeStatus() { 

$url = 'https://thepapers.in/deriv/updatePageTrade.php';

// กำหนด parameter ที่ต้องการส่งไปกับ GET request
$parameters = array(
    'assetCode' => 'R_100',
    'isOpenTrade' => 'Y',
    'moneyTrade' => 1,
    'targetTrade' => 1.5
);

// สร้าง query string จาก array ของ parameters
$queryString = http_build_query($parameters);

// รวม URL และ query string เข้าด้วยกัน
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

// ปิด cURL session
curl_close($ch);

return $response ;


} // end function

?>
