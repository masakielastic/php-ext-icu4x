<?php
/**
 * Demo script for icu4x_eaw_width function
 * 
 * This demonstrates the basic usage of the East Asian Width function
 * implemented in ICU4X.
 */

echo "ICU4X East Asian Width Demo\n";
echo "===========================\n\n";

// Simple usage examples
echo "Basic Usage:\n";
echo "icu4x_eaw_width('A') = " . icu4x_eaw_width('A') . "\n";
echo "icu4x_eaw_width('あ') = " . icu4x_eaw_width('あ') . "\n";
echo "icu4x_eaw_width('ｱ') = " . icu4x_eaw_width('ｱ') . "\n";
echo "icu4x_eaw_width('Ａ') = " . icu4x_eaw_width('Ａ') . "\n";

echo "\nLocale-specific Examples:\n";
echo "icu4x_eaw_width('§') = " . icu4x_eaw_width('§') . " (default)\n";
echo "icu4x_eaw_width('§', 'ja') = " . icu4x_eaw_width('§', 'ja') . " (Japanese)\n";
echo "icu4x_eaw_width('§', 'en') = " . icu4x_eaw_width('§', 'en') . " (English)\n";

echo "\nString Width Calculation Example:\n";
$text = "Hello世界";
$width = 0;
for ($i = 0; $i < mb_strlen($text); $i++) {
    $char = mb_substr($text, $i, 1);
    $char_width = icu4x_eaw_width($char);
    $width += $char_width;
    echo "'{$char}' = {$char_width}\n";
}
echo "Total width: {$width}\n";

echo "\nDone!\n";
?>