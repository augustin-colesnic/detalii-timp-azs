<?php
/**
 * api/og-image.php
 * Generates an Open Graph image with centered verse text on a gradient background.
 */

// 1. Basic configuration and DB connection
$config = null;
$configCandidates = [
    '/home/inspicio/azstulcea.ro/cgi-bin/config.php',
    __DIR__ . '/../cgi-bin/config.php',
    __DIR__ . '/config.php'
];
foreach ($configCandidates as $cfgPath) {
    if (file_exists($cfgPath)) {
        $config = require $cfgPath;
        break;
    }
}

// Default values if DB fails
$message = "Pregătește-te pentru închinare.";
$referinta = "Școala de Sabat";

if ($config) {
    try {
        $pdo = new PDO('sqlite:' . $config['db_path']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT message, referinta FROM default_message WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['message'])) {
            $message = trim($row['message']);
            $referinta = trim($row['referinta'] ?? '');
        }
    } catch (Exception $e) {
        // Fallback to defaults
    }
}

// 2. Image Setup (standard OG size 1200x630)
$width = 1200;
$height = 630;
$img = imagecreatetruecolor($width, $height);

// Enable high-quality anti-aliasing if possible
if (function_exists('imageantialias')) {
    imageantialias($img, true);
}

// 3. Draw Modern Gradient (Deep Blue to Royal Purple)
// #1e3a8a (30, 58, 138) -> #581c87 (88, 28, 135)
$startR = 30; $startG = 58; $startB = 138;
$endR = 88; $endG = 28; $endB = 135;

for ($y = 0; $y < $height; $y++) {
    $r = $startR + ($endR - $startR) * ($y / $height);
    $g = $startG + ($endG - $startG) * ($y / $height);
    $b = $startB + ($endB - $startB) * ($y / $height);
    $color = imagecolorallocate($img, (int)$r, (int)$g, (int)$b);
    imageline($img, 0, $y, $width, $y, $color);
}

// 4. Font Configuration
$fontDir = __DIR__ . '/../assets/fonts/Fira_Sans/';
$fontBold = $fontDir . 'FiraSans-SemiBold.ttf';
$fontRegular = $fontDir . 'FiraSans-Regular.ttf';
$fontItalic = $fontDir . 'FiraSans-Italic.ttf';

$canUseTTF = function_exists('imagettftext') && file_exists($fontBold);

$textColor = imagecolorallocate($img, 255, 255, 255);
$accentColor = imagecolorallocate($img, 253, 187, 45); // Yellow/Gold for reference

if ($canUseTTF) {
    // 5. Text Wrapping Logic
    function wrapText($fontSize, $angle, $fontFile, $text, $maxWidth) {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        foreach ($words as $word) {
            $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
            $bbox = imagettfbbox($fontSize, $angle, $fontFile, $testLine);
            $lineWidth = $bbox[2] - $bbox[0];
            if ($lineWidth <= $maxWidth) {
                $currentLine = $testLine;
            } else {
                if ($currentLine !== '') $lines[] = $currentLine;
                $currentLine = $word;
            }
        }
        if ($currentLine !== '') $lines[] = $currentLine;
        return $lines;
    }

    // 6. Dynamic Font Sizing based on message length
    $fontSize = 44;
    $charCount = mb_strlen($message);
    if ($charCount > 100) $fontSize = 40;
    if ($charCount > 200) $fontSize = 32;
    if ($charCount > 400) $fontSize = 26;
    if ($charCount > 600) $fontSize = 22;

    $wrappedLines = wrapText($fontSize, 0, $fontBold, $message, 1000);
    $lineHeight = $fontSize * 1.6;
    $totalTextHeight = count($wrappedLines) * $lineHeight;

    // Adjust Y to center the block
    $y = ($height - $totalTextHeight) / 2 + ($fontSize / 2);

    // 7. Draw the Main Verse
    foreach ($wrappedLines as $line) {
        $bbox = imagettfbbox($fontSize, 0, $fontBold, $line);
        $lineWidth = $bbox[2] - $bbox[0];
        $x = ($width - $lineWidth) / 2;
        imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $textColor, $fontBold, $line);
        $y += $lineHeight;
    }

    // 8. Draw the Reference
    if (!empty($referinta)) {
        $y += 40; // Gap
        $refSize = (int)($fontSize * 0.7);
        if ($refSize < 24) $refSize = 24;
        $bbox = imagettfbbox($refSize, 0, $fontItalic, $referinta);
        $lineWidth = $bbox[2] - $bbox[0];
        $x = ($width - $lineWidth) / 2;
        imagettftext($img, $refSize, 0, (int)$x, (int)$y, $accentColor, $fontItalic, $referinta);
    }

    // 9. Add Branding / Footer
    $brandText = "AZSTulcea.ro | Școala de Sabat";
    $brandSize = 18;
    $bbox = imagettfbbox($brandSize, 0, $fontRegular, $brandText);
    $brandWidth = $bbox[2] - $bbox[0];
    imagettftext($img, $brandSize, 0, (int)($width - $brandWidth - 60), (int)($height - 50), $textColor, $fontRegular, $brandText);
} else {
    // Basic fallback using imagestring if TTF is not available
    $display = $message . ($referinta ? " - " . $referinta : "");
    $display = mb_substr($display, 0, 500);
    imagestring($img, 5, 50, $height/2, $display, $textColor);
    imagestring($img, 3, $width - 250, $height-50, "AZSTulcea.ro | S. Sabat", $textColor);
}

// 10. Output Image
header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800'); // 1 week cache
imagepng($img);
imagedestroy($img);
