<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// รับค่าจาก Environment
$botToken = getenv('BOT_TOKEN');
if (!$botToken) {
    file_put_contents("error.log", date("c") . " : BOT_TOKEN ไม่ถูกตั้งค่า\n", FILE_APPEND);
    exit;
}

$apiURL = "https://api.telegram.org/bot$botToken/";
$content = file_get_contents("php://input");
file_put_contents("telegram_log.txt", date("c") . " : " . $content . "\n", FILE_APPEND);

$update = json_decode($content, true);
if (isset($update["message"]["chat"]["id"], $update["message"]["text"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];
    $reply = "Echo: " . $text;
    sendTelegramMessage($chatId, $reply, $apiURL);
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

?>