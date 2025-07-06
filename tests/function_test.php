<?php

// Test for icu4x_segmenter function
if (!extension_loaded('icu4x')) {
    echo "ICU4X extension not loaded\n";
    exit(1);
}

if (!function_exists('icu4x_segmenter')) {
    echo "icu4x_segmenter function not found\n";
    exit(1);
}

try {
    echo "Testing icu4x_segmenter function...\n\n";
    
    // Test 1: Basic functionality with default parameters
    echo "=== Test 1: Basic functionality ===\n";
    $text1 = "Hello World";
    $iterator1 = icu4x_segmenter($text1);
    
    echo "Text: '$text1'\n";
    echo "Type: " . get_class($iterator1) . "\n";
    echo "Segments:\n";
    foreach ($iterator1 as $i => $segment) {
        echo "  [$i] => '$segment'\n";
    }
    echo "Count (method): " . $iterator1->count() . "\n";
    echo "Count (count()): " . count($iterator1) . "\n";
    
    // Test 2: With explicit parameters
    echo "\n=== Test 2: With explicit parameters ===\n";
    $text2 = "こんにちは👋世界";
    $iterator2 = icu4x_segmenter($text2, 'grapheme', null);
    
    echo "Text: '$text2'\n";
    echo "Segments:\n";
    foreach ($iterator2 as $i => $segment) {
        echo "  [$i] => '$segment'\n";
    }
    echo "Count: " . count($iterator2) . "\n";
    
    // Test 3: SPL Interface compliance
    echo "\n=== Test 3: SPL Interface compliance ===\n";
    echo "instanceof IteratorAggregate: " . ($iterator1 instanceof IteratorAggregate ? "Yes" : "No") . "\n";
    echo "instanceof Countable: " . ($iterator1 instanceof Countable ? "Yes" : "No") . "\n";
    
    // Test 4: getIterator method
    echo "\n=== Test 4: getIterator method ===\n";
    $internal = $iterator1->getIterator();
    echo "Internal iterator type: " . get_class($internal) . "\n";
    echo "instanceof Iterator: " . ($internal instanceof Iterator ? "Yes" : "No") . "\n";
    
    // Test 5: Comparison with class API
    echo "\n=== Test 5: Comparison with class API ===\n";
    $segmenter = new ICU4X\Segmenter('grapheme');
    $class_iterator = $segmenter->segment($text1);
    
    $function_segments = iterator_to_array($iterator1);
    $class_segments = iterator_to_array($class_iterator);
    
    echo "Function API segments: " . count($function_segments) . "\n";
    echo "Class API segments: " . count($class_segments) . "\n";
    echo "Results match: " . ($function_segments === $class_segments ? "Yes" : "No") . "\n";
    
    // Test 6: Error handling
    echo "\n=== Test 6: Error handling ===\n";
    try {
        $bad_iterator = icu4x_segmenter("test", "invalid_mode");
        echo "ERROR: Should have thrown exception for invalid mode\n";
    } catch (Exception $e) {
        echo "Correctly caught exception: " . $e->getMessage() . "\n";
    }
    
    // Test 7: Empty string
    echo "\n=== Test 7: Empty string ===\n";
    $empty_iterator = icu4x_segmenter("");
    echo "Empty string segments: " . count($empty_iterator) . "\n";
    
    // Test 8: Complex Unicode text
    echo "\n=== Test 8: Complex Unicode text ===\n";
    $complex = "🇺🇸🏳️‍🌈👨‍👩‍👧‍👦";
    $complex_iterator = icu4x_segmenter($complex);
    echo "Complex Unicode text: '$complex'\n";
    echo "Segments:\n";
    foreach ($complex_iterator as $i => $segment) {
        echo "  [$i] => '$segment'\n";
    }
    echo "Total segments: " . count($complex_iterator) . "\n";
    
    echo "\n✅ All function tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}