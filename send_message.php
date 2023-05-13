<?php
// 获取用户发送的消息
$message = $_POST['message'];

// 使用GPT API进行回复
$apiKey = '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'; // 替换为你的API密钥
$modelId = 'gpt-3.5-turbo'; // 根据你的需求选择适当的模型ID

$apiUrl = 'https://api.openai.com/v1/chat/completions';
$data = array(
  "model" => $modelId,
  "messages" => [
    ["role" => "user", "content" => $message]
  ]
);
$headers = array(
  'Content-Type: application/json',
  'Authorization: Bearer ' . $apiKey,
  organization: "org-RlUVYh0IWMGbAMSncSrNuRNu"
);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$reply = json_decode($response, true)['choices'][0]['message']['content'];

// 返回回复
echo json_encode(['reply' => $reply]);
?>
