<?php
require_once '../../config/api_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$topik    = trim($_POST['topik'] ?? '');
$kategori = trim($_POST['kategori'] ?? 'biota');

if (empty($topik)) {
    echo json_encode(['error' => 'Topik kosong']);
    exit;
}

// ================================================
// STEP 1: Fetch ringkasan dari Wikipedia (gratis!)
// ================================================
function fetchWikipedia($query) {
    // Coba bahasa Indonesia dulu
    $url = 'https://id.wikipedia.org/api/rest_v1/page/summary/' . urlencode($query);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'PermatabiruNusantara/1.0',
        CURLOPT_TIMEOUT        => 10
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (!empty($data['extract'])) {
            return [
                'judul'  => $data['title'] ?? $query,
                'isi'    => $data['extract'],
                'url'    => $data['content_urls']['desktop']['page'] ?? '',
                'bahasa' => 'id'
            ];
        }
    }

    // Fallback: coba bahasa Inggris
    $queryEn = str_replace(' ', '_', $query);
    $urlEn   = 'https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($queryEn);
    $ch      = curl_init($urlEn);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'PermatabiruNusantara/1.0',
        CURLOPT_TIMEOUT        => 10
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (!empty($data['extract'])) {
            return [
                'judul'  => $data['title'] ?? $query,
                'isi'    => $data['extract'],
                'url'    => $data['content_urls']['desktop']['page'] ?? '',
                'bahasa' => 'en'
            ];
        }
    }

    return null;
}

// ================================================
// STEP 2: Cari juga topik terkait (3 artikel)
// ================================================
function searchWikipedia($query, $limit = 3) {
    $url = 'https://id.wikipedia.org/w/api.php?' . http_build_query([
        'action'   => 'query',
        'list'     => 'search',
        'srsearch' => $query,
        'srlimit'  => $limit,
        'format'   => 'json',
        'utf8'     => 1
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'PermatabiruNusantara/1.0',
        CURLOPT_TIMEOUT        => 10
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data    = json_decode($response, true);
    $results = $data['query']['search'] ?? [];
    $konteks = [];

    foreach ($results as $item) {
        // Ambil ringkasan tiap artikel terkait
        $wiki = fetchWikipedia($item['title']);
        if ($wiki) {
            $konteks[] = "### {$wiki['judul']}\n{$wiki['isi']}";
        }
    }

    return $konteks;
}

// Jalankan pencarian Wikipedia
$wikiUtama  = fetchWikipedia($topik);
$wikiTerkait = searchWikipedia($topik, 3);

// Gabungkan semua konteks
$konteksWiki = '';
if ($wikiUtama) {
    $konteksWiki .= "### {$wikiUtama['judul']} (Artikel Utama)\n{$wikiUtama['isi']}\n\n";
}
foreach ($wikiTerkait as $w) {
    $konteksWiki .= $w . "\n\n";
}

// ================================================
// STEP 3: Kirim ke AI dengan konteks Wikipedia
// ================================================
$prompt = "Kamu adalah penulis konten untuk portal kelautan Indonesia bernama Permata Biru Nusantara.

Tulis artikel tentang: {$topik}
Kategori: {$kategori}

Gunakan referensi berikut dari Wikipedia sebagai dasar penulisan:
---
{$konteksWiki}
---

Petunjuk:
- Tulis artikel yang informatif, menarik, dan mudah dipahami
- Fokus pada aspek kelautan dan kemaritiman Indonesia
- Jangan menyebut Wikipedia sebagai sumber secara langsung
- Kembangkan informasi dari referensi di atas

Jawab HANYA dengan JSON berikut, tanpa teks lain:
{
  \"judul\": \"judul artikel yang menarik dan spesifik\",
  \"kategori\": \"{$kategori}\",
  \"isi\": \"isi artikel minimal 4 paragraf. Pisahkan paragraf dengan \\n\\n\",
  \"slug\": \"judul-format-slug-huruf-kecil\",
  \"keyword_gambar\": \"2-4 kata bahasa Inggris untuk cari foto di Pexels\"
}";

$payload = [
    'model'       => AI_MODEL,
    'messages'    => [
        [
            'role'    => 'system',
            'content' => 'Kamu adalah penulis artikel kelautan Indonesia. Selalu jawab dalam format JSON yang diminta.'
        ],
        [
            'role'    => 'user',
            'content' => $prompt
        ]
    ],
    'max_tokens'  => 5000,
    'temperature' => 0.7
];

$ch = curl_init(AI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . AI_API_KEY
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'API error: ' . $httpCode, 'detail' => $response]);
    exit;
}

$result  = json_decode($response, true);
$rawText = $result['choices'][0]['message']['content'] ?? '';

// Bersihkan markdown code fence jika ada
$rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
$rawText = preg_replace('/```$/', '',       trim($rawText));

$artikel = json_decode(trim($rawText), true);

if (!$artikel) {
    echo json_encode(['error' => 'Gagal parse JSON', 'raw' => $rawText]);
    exit;
}

$artikel['tanggal']      = date('Y-m-d');
$artikel['wiki_sumber']  = $wikiUtama['url'] ?? '';

echo json_encode($artikel);