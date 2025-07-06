<?php

// Basic test for ICU4X PHP extension
if (!extension_loaded('icu4x')) {
    echo "ICU4X extension not loaded\n";
    echo "Available extensions:\n";
    print_r(get_loaded_extensions());
    exit(1);
}

// Test basic functionality
try {
    $segmenter = new ICU4X\Segmenter();
    echo "Segmenter created successfully\n";
    
    // Test with simple ASCII text
    $text = "Hello World";
    $iterator = $segmenter->segment($text);
    
    echo "Segments for '$text':\n";
    foreach ($iterator as $i => $segment) {
        echo "  [$i] => '$segment'\n";
    }
    
    // Test count (both methods)
    echo "Total segments (method): " . $iterator->count() . "\n";
    echo "Total segments (count()): " . count($iterator) . "\n";
    
    // Test with emoji and complex text
    $complex_text = "こんにちは👋世界";
    $iterator2 = $segmenter->segment($complex_text);
    
    echo "\nSegments for '$complex_text':\n";
    foreach ($iterator2 as $i => $segment) {
        echo "  [$i] => '$segment'\n";
    }
    echo "Total segments (method): " . $iterator2->count() . "\n";
    echo "Total segments (count()): " . count($iterator2) . "\n";
    
    // Test IteratorAggregate interface (when implemented)
    echo "\nTesting SPL interfaces:\n";
    echo "instanceof IteratorAggregate: " . ($iterator instanceof IteratorAggregate ? "Yes" : "No") . "\n";
    echo "instanceof Countable: " . ($iterator instanceof Countable ? "Yes" : "No") . "\n";
    
    // Test getIterator method
    echo "\nTesting getIterator method:\n";
    try {
        $internal_iterator = $iterator->getIterator();
        echo "getIterator() returned: " . get_class($internal_iterator) . "\n";
    } catch (Exception $e) {
        echo "getIterator() error: " . $e->getMessage() . "\n";
    }
    
    echo "\nAll tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}