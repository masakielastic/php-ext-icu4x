<?php
/**
 * Test file for icu4x_eaw_width function
 * 
 * This file tests the East Asian Width functionality implemented in ICU4X
 * and compares it with the expected behavior from the ICU4C reference.
 */

// Test basic functionality
echo "=== Basic East Asian Width Tests ===\n";

// Test ASCII characters (should be width 1)
echo "ASCII 'A': " . icu4x_eaw_width("A") . "\n";
echo "ASCII '1': " . icu4x_eaw_width("1") . "\n";
echo "ASCII ' ': " . icu4x_eaw_width(" ") . "\n";

// Test wide characters (should be width 2)
echo "Japanese あ: " . icu4x_eaw_width("あ") . "\n";
echo "Chinese 中: " . icu4x_eaw_width("中") . "\n";
echo "Korean 가: " . icu4x_eaw_width("가") . "\n";

// Test halfwidth characters (should be width 1)
echo "Halfwidth ｱ: " . icu4x_eaw_width("ｱ") . "\n";
echo "Halfwidth ｶ: " . icu4x_eaw_width("ｶ") . "\n";

// Test fullwidth characters (should be width 2)
echo "Fullwidth Ａ: " . icu4x_eaw_width("Ａ") . "\n";
echo "Fullwidth １: " . icu4x_eaw_width("１") . "\n";

echo "\n=== Locale-dependent Tests (Ambiguous Characters) ===\n";

// Test ambiguous characters with different locales
$ambiguous_chars = ["§", "±", "×", "÷"];

foreach ($ambiguous_chars as $char) {
    echo "Character '$char':\n";
    echo "  Default (no locale): " . icu4x_eaw_width($char) . "\n";
    echo "  Japanese locale: " . icu4x_eaw_width($char, "ja") . "\n";
    echo "  Chinese locale: " . icu4x_eaw_width($char, "zh") . "\n";
    echo "  Korean locale: " . icu4x_eaw_width($char, "ko") . "\n";
    echo "  English locale: " . icu4x_eaw_width($char, "en") . "\n";
}

echo "\n=== Edge Cases ===\n";

// Test empty string (should return -1)
echo "Empty string: " . icu4x_eaw_width("") . "\n";

// Test multi-character string (should return width of first character)
echo "Multi-char 'Hello': " . icu4x_eaw_width("Hello") . "\n";
echo "Multi-char 'あいう': " . icu4x_eaw_width("あいう") . "\n";

echo "\n=== Comparison with Expected Values ===\n";

$test_cases = [
    // [character, expected_width, expected_width_ja]
    ["A", 1, 1],
    ["あ", 2, 2],
    ["ｱ", 1, 1],
    ["Ａ", 2, 2],
    ["§", 1, 2],  // Ambiguous: narrow in non-EA locales, wide in EA locales
    ["±", 1, 2],  // Ambiguous
    ["×", 1, 2],  // Ambiguous
];

foreach ($test_cases as [$char, $expected_default, $expected_ja]) {
    $actual_default = icu4x_eaw_width($char);
    $actual_ja = icu4x_eaw_width($char, "ja");
    
    $default_ok = $actual_default == $expected_default ? "✓" : "✗";
    $ja_ok = $actual_ja == $expected_ja ? "✓" : "✗";
    
    echo "Character '$char': Default {$default_ok} ({$actual_default}), Japanese {$ja_ok} ({$actual_ja})\n";
}

echo "\n=== Performance Test ===\n";

$start_time = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    icu4x_eaw_width("あ");
}
$end_time = microtime(true);
$elapsed = ($end_time - $start_time) * 1000;

echo "1000 calls took " . round($elapsed, 2) . " ms\n";

echo "\nTest completed.\n";
?>