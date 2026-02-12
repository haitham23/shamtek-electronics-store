<?php
header('Content-Type: text/plain; charset=utf-8');

if (!isset($_POST['message'])) { echo "error:no_message"; exit; }

$msg = trim((string)$_POST['message']);
if ($msg === '') { echo "error:empty"; exit; }
if (mb_strlen($msg, 'UTF-8') > 256) { echo "too_long"; exit; }

$mapFile = __DIR__ . '/scoremap.json';
if (!file_exists($mapFile)) { echo "error:no_scoremap"; exit; }

$scoremap = json_decode(file_get_contents($mapFile), true);
if (!is_array($scoremap)) { echo "error:bad_scoremap"; exit; }

// lower + split على أي شيء ماشي حرف/رقم/' 
$lower = mb_strtolower($msg, 'UTF-8');
$tokens = preg_split('/[^\p{L}\p{N}\']+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);

if (!$tokens) { echo "ok"; exit; }

$total = 0.0;
$matched = 0;

foreach ($tokens as $w) {
  $w = trim($w, "'");
  if ($w === '') continue;

  if (array_key_exists($w, $scoremap)) {
    $total += (float)$scoremap[$w];
    $matched++;
  }
}

// منطق الـTP: مجموع النقاط > 0 => ok، وإلا => offensive
// إذا ما لقيناش حتى كلمة فـscoremap كنخليوها ok باش ما نرفضوش رسائل عادية.
if ($matched === 0) { echo "ok"; exit; }

echo ($total > 0) ? "ok" : "offensive";
