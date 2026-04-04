<?php
require_once '../../config/api_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$keyword = trim($_POST['keyword'] ?? 'ocean indonesia');
$keyword = urlencode($keyword);

$ch = curl_init("https://api.pexels.com/v1/search?query={$keyword}&per_page=6&orientation=landscape");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: ' . PEXELS_API_KEY]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'Pexels API error: ' . $httpCode]);
    exit;
}

$data   = json_decode($response, true);
$photos = [];

foreach ($data['photos'] ?? [] as $foto) {
    $photos[] = [
        'url'       => $foto['src']['large'],
        'url_kecil' => $foto['src']['medium'],
        'credit'    => $foto['photographer'],
        'alt'       => $foto['alt'] ?? $keyword
    ];
}

echo json_encode(['photos' => $photos]);