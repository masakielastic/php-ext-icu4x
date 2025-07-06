# icu4x_segmenter Function Implementation Plan

## Overview
`icu4x_segmenter`関数の実装計画。既存のクラスベースAPIに加えて、より簡潔な関数型APIを提供する。

## Function Specification

### Signature
```php
function icu4x_segmenter(string $str, string $mode = 'grapheme', ?string $locale = null): ICU4X\SegmentIterator
```

### Parameters
- `$str` (string, required): セグメント化する対象文字列
- `$mode` (string, optional): セグメンテーションモード。デフォルト: 'grapheme'
- `$locale` (string|null, optional): ロケール指定。デフォルト: null

### Return Value
- `ICU4X\SegmentIterator`: 既存のsegmentメソッドと同じイテレーターオブジェクト

## Implementation Strategy

### 1. 関数の実装アプローチ

**Option A: 内部でSegmenterクラスを使用**
```rust
#[php_function]
pub fn icu4x_segmenter(input: String, mode: Option<String>, locale: Option<String>) -> PhpResult<SegmentIterator> {
    let segmenter = Segmenter::__construct(mode, locale)?;
    segmenter.segment(input)
}
```

**Option B: 直接実装**
```rust
#[php_function]
pub fn icu4x_segmenter(input: String, mode: Option<String>, locale: Option<String>) -> PhpResult<SegmentIterator> {
    let mode = mode.unwrap_or_else(|| "grapheme".to_string());
    
    if mode != "grapheme" {
        return Err(PhpException::default(format!("Unsupported mode: {}", mode)));
    }

    let segmenter = GraphemeClusterSegmenter::new();
    let break_iterator = segmenter.segment_str(&input);
    
    let mut segments = Vec::new();
    let mut start = 0;
    
    for end in break_iterator {
        if end > start {
            segments.push(input[start..end].to_string());
        }
        start = end;
    }

    Ok(SegmentIterator::new(segments))
}
```

### 2. Recommended Approach: Option A

**理由:**
- コードの重複を避ける
- 既存のSegmenterクラスのロジックを再利用
- 将来の機能拡張に対する一貫性を保つ
- メンテナンスが容易

## Implementation Steps

### Phase 1: Function Registration
- [ ] `lib.rs`に関数を登録
- [ ] ext-php-rs `#[php_function]`属性を使用
- [ ] モジュールビルダーに`.function`で追加

### Phase 2: Function Implementation
- [ ] `functions.rs`モジュールを作成
- [ ] パラメータ処理の実装
- [ ] 既存のSegmenterクラスを活用した実装
- [ ] エラーハンドリングの実装

### Phase 3: Testing
- [ ] 基本機能テストの追加
- [ ] パラメータバリデーションテスト
- [ ] 既存のsegmentメソッドとの一貫性テスト

### Phase 4: Documentation
- [ ] 関数の使用例を追加
- [ ] API仕様書の更新

## Detailed Implementation Plan

### 1. File Structure
```
src/
├── lib.rs              # 関数登録を追加
├── segmenter.rs        # 既存
├── iterator.rs         # 既存  
├── functions.rs        # 新規作成
└── tests/
    └── function_test.rs
```

### 2. lib.rs Changes
```rust
mod functions;
use functions::icu4x_segmenter;

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .class::<Segmenter>()
        .class::<InternalIterator>()
        .class::<SegmentIterator>()
        .function("icu4x_segmenter", icu4x_segmenter)
}
```

### 3. functions.rs Implementation
```rust
use ext_php_rs::prelude::*;
use crate::segmenter::Segmenter;
use crate::iterator::SegmentIterator;

#[php_function]
pub fn icu4x_segmenter(
    input: String, 
    mode: Option<String>, 
    locale: Option<String>
) -> PhpResult<SegmentIterator> {
    // Segmenterクラスのインスタンスを内部で作成
    let segmenter = Segmenter::__construct(mode, locale)?;
    
    // 既存のsegmentメソッドを呼び出し
    segmenter.segment(input)
}
```

### 4. Error Handling
- 不正なモードパラメータ
- 無効な文字列入力
- ICU4Xライブラリエラー
- メモリ不足エラー

### 5. Performance Considerations
- Segmenterオブジェクトの作成コスト
- 大きなテキストでの処理効率
- メモリ使用量の最適化

## Usage Examples

### Basic Usage
```php
// シンプルな関数呼び出し
$iterator = icu4x_segmenter("Hello World");
foreach ($iterator as $segment) {
    echo $segment . "\n";
}
```

### With Parameters
```php
// モードとロケールを指定
$iterator = icu4x_segmenter("こんにちは👋世界", 'grapheme', 'ja');
echo count($iterator); // Countableインターフェイス
```

### Comparison with Class API
```php
// Function API (新規)
$iterator1 = icu4x_segmenter($text, 'grapheme');

// Class API (既存)
$segmenter = new ICU4X\Segmenter('grapheme');
$iterator2 = $segmenter->segment($text);

// 両方とも同じ ICU4X\SegmentIterator を返す
```

## Testing Strategy

### 1. Unit Tests
- パラメータ処理の正確性
- エラーケースの処理
- 戻り値の型と内容

### 2. Integration Tests
- 既存のSegmenterクラスとの一貫性
- SPLインターフェイスの動作
- 大きなテキストでの処理

### 3. Performance Tests
- 関数呼び出しのオーバーヘッド
- メモリ使用量の測定

## Migration and Compatibility

### Backward Compatibility
- 既存のクラスAPIは変更なし
- 新しい関数APIは追加のみ
- 既存のテストは影響を受けない

### API Design Philosophy
- **Class API**: オブジェクト指向、状態を持つ処理
- **Function API**: 関数型、ステートレス、簡潔

## Success Criteria
- [ ] `icu4x_segmenter`関数が正常に動作
- [ ] 既存のsegmentメソッドと同じ結果を返す
- [ ] SPLインターフェイス（IteratorAggregate, Countable）が動作
- [ ] エラーハンドリングが適切
- [ ] パフォーマンスが許容範囲内
- [ ] テストカバレッジが十分

## Timeline
1. **Day 1**: functions.rsの実装とlib.rsの更新
2. **Day 2**: テストの作成と基本動作確認
3. **Day 3**: エラーハンドリングとパフォーマンス最適化
4. **Day 4**: ドキュメント更新と最終テスト