<?php
$botToken = "7944561871:AAHxvvdNPU193pJeY4ZsOnHYIML8L8NxvwY";
$authDate = time();

$payload = [
    "id" => 123456789,
    "first_name" => "Test",
    "username" => "testuser",
    "auth_date" => $authDate,
];

$check = $payload;
ksort($check);

$dataCheckString = implode("\n", array_map(
    fn($k, $v) => "$k=$v",
    array_keys($check),
    array_values($check)
));

$secretKey = hash("sha256", $botToken, true);
$computedHash = hash_hmac("sha256", $dataCheckString, $secretKey);

$payload["hash"] = $computedHash;

echo "data_check_string:\n" . $dataCheckString . "\n\n";
echo "computed_hash: " . $computedHash . "\n\n";
echo "JSON payload:\n" . json_encode($payload) . "\n";