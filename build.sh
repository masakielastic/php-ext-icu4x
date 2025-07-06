#!/bin/bash

# Build script for ICU4X PHP extension

set -e

echo "Building ICU4X PHP extension..."

# Build the Rust library
cargo build --release

# Check if build was successful
if [ $? -eq 0 ]; then
    echo "✓ Rust library built successfully"
    
    # Show the built library
    if [ -f "target/release/libphp_ext_icu4x.so" ]; then
        echo "✓ Shared library created: target/release/libphp_ext_icu4x.so"
    elif [ -f "target/release/libphp_ext_icu4x.dylib" ]; then
        echo "✓ Shared library created: target/release/libphp_ext_icu4x.dylib"
    else
        echo "⚠ Shared library not found in expected location"
        ls -la target/release/
    fi
else
    echo "✗ Build failed"
    exit 1
fi

echo "Build completed!"
echo ""
echo "To test the extension:"
echo "1. Copy the shared library to your PHP extensions directory"
echo "2. Add 'extension=php_ext_icu4x' to your php.ini"
echo "3. Run: php tests/basic_test.php"