<?php
// ��ȡ�û����͵���Ϣ
$message = $_POST['message'];

// ʹ��GPT API���лظ�
$apiKey = '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'; // �滻Ϊ���API��Կ
$modelId = 'gpt-3.5-turbo'; // �����������ѡ���ʵ���ģ��ID

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

// ���ػظ�
echo json_encode(['reply' => $reply]);
?>
