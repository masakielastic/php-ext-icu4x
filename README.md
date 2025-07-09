# ICU4X PHP Extension

A PHP extension for Unicode text segmentation using ICU4X, built with ext-php-rs.

## Features

- **Grapheme Cluster Segmentation**: Proper handling of Unicode text including emojis and complex scripts
- **East Asian Width Support**: Calculate display width of Unicode characters for proper text layout
- **Multiple APIs**: Both object-oriented class API and functional API
- **SPL Interface Support**: Full integration with PHP's Standard PHP Library interfaces
- **ICU4X 2.0**: Built on the latest ICU4X Unicode library
- **Memory Efficient**: Rust-based implementation with zero-copy optimizations

## Installation

### Prerequisites

- Rust 1.70+
- PHP 8.0+
- ICU4X 2.0
- ext-php-rs 0.14.0

### Building

1. Clone the repository:
```bash
git clone <repository-url>
cd php-ext-icu4x
```

2. Build the extension:
```bash
./build.sh
```

3. Install the extension:
```bash
# Copy the shared library to your PHP extensions directory
sudo cp target/release/libicu4x.so /usr/lib/php/extensions/

# Add to php.ini
echo "extension=icu4x" >> /etc/php/8.2/cli/php.ini
```

## Usage

### Function API (Recommended)

```php
<?php

// Basic usage
$iterator = icu4x_segmenter("Hello World");
foreach ($iterator as $segment) {
    echo $segment . "\n";
}

// With parameters
$iterator = icu4x_segmenter("こんにちは👋世界", "grapheme", null);
echo "Total segments: " . count($iterator) . "\n";

// Complex Unicode text
$text = "🇺🇸🏳️‍🌈👨‍👩‍👧‍👦";
$segments = icu4x_segmenter($text);
foreach ($segments as $i => $segment) {
    echo "[$i] => '$segment'\n";
}
```

### Class API

```php
<?php

// Create segmenter instance
$segmenter = new ICU4X\Segmenter('grapheme', null);

// Segment text
$iterator = $segmenter->segment("Hello World");

// Iterate over segments
foreach ($iterator as $segment) {
    echo $segment . "\n";
}

// Use SPL interfaces
echo "Count: " . count($iterator) . "\n";
echo "Is countable: " . ($iterator instanceof Countable ? "Yes" : "No") . "\n";
echo "Is iterable: " . ($iterator instanceof IteratorAggregate ? "Yes" : "No") . "\n";
```

## API Reference

### Function API

#### `icu4x_segmenter(string $text, string $mode = 'grapheme', ?string $locale = null): ICU4X\SegmentIterator`

Segments the input text into grapheme clusters.

**Parameters:**
- `$text` (string): The text to segment
- `$mode` (string, optional): Segmentation mode, currently only 'grapheme' is supported
- `$locale` (string|null, optional): Locale for segmentation rules

**Returns:** `ICU4X\SegmentIterator` - An iterator over text segments

#### `icu4x_eaw_width(string $char, ?string $locale = null): int`

Calculate the display width of a Unicode character based on its East Asian Width property.

**Parameters:**
- `$char` (string): The character to calculate width for (only first character is used)
- `$locale` (string|null, optional): Locale for ambiguous character handling

**Returns:** `int` - Display width (1 or 2), or -1 on error

**Examples:**
```php
echo icu4x_eaw_width('A');        // 1 (Narrow)
echo icu4x_eaw_width('あ');       // 2 (Wide)
echo icu4x_eaw_width('ｱ');        // 1 (Halfwidth)
echo icu4x_eaw_width('Ａ');       // 2 (Fullwidth)
echo icu4x_eaw_width('§');        // 1 (Ambiguous, default)
echo icu4x_eaw_width('§', 'ja');  // 2 (Ambiguous, Japanese locale)
```

### Class API

#### `ICU4X\Segmenter`

Main segmenter class for text segmentation.

**Constructor:**
```php
new ICU4X\Segmenter(string $mode = 'grapheme', ?string $locale = null)
```

**Methods:**
- `segment(string $text): ICU4X\SegmentIterator` - Segment the input text
- `getMode(): string` - Get the current segmentation mode
- `getLocale(): ?string` - Get the current locale

#### `ICU4X\SegmentIterator`

Iterator class for accessing segmentation results.

**Implements:** `IteratorAggregate`, `Countable`

**Methods:**
- `count(): int` - Get the number of segments
- `getIterator(): ICU4X\InternalIterator` - Get internal iterator
- `toArray(): array` - Convert to array

## Examples

### Basic Text Segmentation

```php
// English text
$text = "Hello, world!";
$segments = icu4x_segmenter($text);
// Output: ['H', 'e', 'l', 'l', 'o', ',', ' ', 'w', 'o', 'r', 'l', 'd', '!']
```

### Unicode and Emoji Support

```php
// Japanese text with emoji
$text = "こんにちは👋世界";
$segments = icu4x_segmenter($text);
// Output: ['こ', 'ん', 'に', 'ち', 'は', '👋', '世', '界']

// Complex emoji sequences
$text = "👨‍👩‍👧‍👦";
$segments = icu4x_segmenter($text);
// Output: ['👨‍👩‍👧‍👦'] (single family emoji)
```

### Working with SPL Interfaces

```php
$iterator = icu4x_segmenter("Hello");

// Countable interface
echo count($iterator); // 5

// IteratorAggregate interface
foreach ($iterator as $index => $segment) {
    echo "Position $index: $segment\n";
}

// Check interface implementation
var_dump($iterator instanceof Countable);        // true
var_dump($iterator instanceof IteratorAggregate); // true
```

### East Asian Width Examples

```php
// Basic width calculation
$text = "Hello世界";
$width = 0;
for ($i = 0; $i < mb_strlen($text); $i++) {
    $char = mb_substr($text, $i, 1);
    $width += icu4x_eaw_width($char);
}
echo "Display width: $width\n"; // 9

// Locale-specific handling of ambiguous characters
$ambiguous = "§±×÷";
echo "Default: ";
for ($i = 0; $i < mb_strlen($ambiguous); $i++) {
    $char = mb_substr($ambiguous, $i, 1);
    echo icu4x_eaw_width($char);
}
echo "\n"; // 1111

echo "Japanese: ";
for ($i = 0; $i < mb_strlen($ambiguous); $i++) {
    $char = mb_substr($ambiguous, $i, 1);
    echo icu4x_eaw_width($char, 'ja');
}
echo "\n"; // 2222
```

## Testing

Run the test suite:

```bash
# Basic functionality test
php -d extension=target/debug/libicu4x.so tests/basic_test.php

# Function API test
php -d extension=target/debug/libicu4x.so tests/function_test.php

# East Asian Width test
php -d extension=target/debug/libicu4x.so tests/eaw_width_test.php

# Demo scripts
php -d extension=target/debug/libicu4x.so demo_eaw_width.php
```

For release builds:

```bash
# Build in release mode
cargo build --release

# Run tests with release build
php -d extension=target/release/libicu4x.so tests/basic_test.php
```

## Performance

The extension is built on Rust and ICU4X, providing:

- **High Performance**: Rust's zero-cost abstractions and ICU4X optimizations
- **Memory Efficiency**: Minimal memory overhead with proper resource management
- **Unicode Compliance**: Full Unicode 15.0+ support with correct grapheme cluster handling

## Supported Unicode Features

- ✅ Grapheme cluster segmentation
- ✅ East Asian Width property calculation
- ✅ Emoji sequences (including ZWJ sequences)
- ✅ Complex scripts (Arabic, Devanagari, etc.)
- ✅ Regional indicator sequences (flag emojis)
- ✅ Modifier sequences
- ✅ Locale-aware ambiguous character handling

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

### Development Setup

```bash
# Install Rust
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh

# Install dependencies
cargo build

# Run tests
./build.sh
php -d extension=target/release/libicu4x.so tests/basic_test.php
```

## Commit Message Convention

This project follows [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` - New features
- `fix:` - Bug fixes  
- `docs:` - Documentation changes
- `chore:` - Maintenance tasks
- `test:` - Test additions or modifications

## License

[Add your license information here]

## Acknowledgments

- [ICU4X](https://github.com/unicode-org/icu4x) - Unicode components for Rust
- [ext-php-rs](https://github.com/davidcole1340/ext-php-rs) - PHP extension framework for Rust
- [Unicode Consortium](https://unicode.org/) - Unicode standards