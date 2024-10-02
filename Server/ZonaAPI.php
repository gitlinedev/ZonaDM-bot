<?php

$message = $_GET['message'];
$type = $_GET['t'];
$user_id = $_GET['id'];

$token = 'vk1.a.55soK5BGy6pS9TFAMpVbZQ6ME57hwV7pr81w7k4zkDtkGkM57SbrwZGZqr2mvpNZlKr20x3oQQaM4b28azFR7g8HK_hPBF61kPrRo5xx-hYGa8pKhcU30z7Ot-w2kHWg7ocKLZM1NJXj-cHi06jlUVQnUUffFgNnNdwP6uyKzAew4hPKjX-KnFP6Z77lxOtj77S-IFTnSlWt1GeacdFAjg';

$message = iconv("cp1251", "utf-8", $message);
$message = urlencode($message);

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

if($_GET['t'] == 1) curl_setopt($curl, CURLOPT_URL, 'https://api.vk.com/method/messages.send?user_id='. $user_id .'&title=ЗонаДМ&message='. $message.'&access_token='. $token .'&v=5.81');
if($_GET['t'] == 2) curl_setopt($curl, CURLOPT_URL, 'https://api.vk.com/method/messages.send?chat_id=10&title=ЗонаДМ&message='. $message.'&access_token='. $token .'&v=5.81');

$response = curl_exec($curl);
curl_close($curl);

echo $response;