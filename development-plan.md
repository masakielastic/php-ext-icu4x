# PHP ICU4X Extension Development Plan

## Overview
ICU4X 2.0とext-php-rs v0.14.0を使用して、書記素クラスターを扱うPHP拡張を開発する計画です。

## Requirements Analysis

### Core Functionality
- 書記素クラスターのセグメンテーション
- 複数のセグメンテーションモード対応（grapheme等）
- ロケール対応
- イテレーター機能の提供

### Target API
```php
$segmenter = new ICU4X\Segmenter($mode = 'grapheme', $locale = null);
foreach ($segmenter->segment($str) as $seg) { ... }
```

## Architecture Design

### 1. Module Structure (ext-php-rs v0.14.0)

```rust
// lib.rs
use ext_php_rs::prelude::*;

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .name("icu4x")
        .class::<segmenter::Segmenter>()
        .class::<segmenter::SegmentIterator>()
}
```

### 2. Segmenter Class

```rust
// segmenter.rs
use ext_php_rs::prelude::*;
use icu_segmenter::GraphemeClusterSegmenter;

#[php_class]
pub struct Segmenter {
    mode: String,
    locale: Option<String>,
    segmenter: GraphemeClusterSegmenter,
}

#[php_impl]
impl Segmenter {
    pub fn __construct(mode: String, locale: Option<String>) -> PhpResult<Self> {
        // ICU4X GraphemeClusterSegmenter の初期化
        let segmenter = GraphemeClusterSegmenter::new();
        
        Ok(Self {
            mode,
            locale,
            segmenter,
        })
    }
    
    pub fn segment(&self, input: String) -> PhpResult<SegmentIterator> {
        let segments = self.segmenter.segment_str(&input);
        Ok(SegmentIterator::new(segments))
    }
}
```

### 3. Iterator Class with SPL Interfaces

```rust
// iterator.rs
use ext_php_rs::prelude::*;
use icu_segmenter::GraphemeClusterBreakIterator;

#[php_class]
#[php(implements = ce::iterator_aggregate)]
#[php(implements = ce::countable)]
pub struct SegmentIterator {
    segments: Vec<String>,
    position: usize,
}

#[php_impl]
impl SegmentIterator {
    pub fn new(break_iterator: GraphemeClusterBreakIterator) -> Self {
        // イテレーターから文字列セグメントを収集
        let segments = break_iterator.collect();
        
        Self {
            segments,
            position: 0,
        }
    }
    
    // Countable interface
    pub fn count(&self) -> usize {
        self.segments.len()
    }
    
    // IteratorAggregate interface
    pub fn get_iterator(&self) -> PhpResult<InternalIterator> {
        Ok(InternalIterator::new(&self.segments))
    }
}

#[php_class]
#[php(implements = ce::iterator)]
pub struct InternalIterator {
    segments: Vec<String>,
    position: usize,
}

#[php_impl]
impl InternalIterator {
    pub fn new(segments: &[String]) -> Self {
        Self {
            segments: segments.to_vec(),
            position: 0,
        }
    }
    
    pub fn current(&self) -> Option<&String> {
        self.segments.get(self.position)
    }
    
    pub fn key(&self) -> usize {
        self.position
    }
    
    pub fn next(&mut self) {
        self.position += 1;
    }
    
    pub fn rewind(&mut self) {
        self.position = 0;
    }
    
    pub fn valid(&self) -> bool {
        self.position < self.segments.len()
    }
}
```

## Development Phases

### Phase 1: Project Setup
- [ ] Cargo.toml設定
- [ ] ext-php-rs v0.14.0依存関係追加
- [ ] ICU4X segmenter クレート追加
- [ ] 基本的なモジュール構造作成

### Phase 2: Core Implementation
- [ ] Segmenterクラスの実装
- [ ] ICU4X GraphemeClusterSegmenterの統合
- [ ] 基本的なsegmentメソッドの実装

### Phase 3: Iterator Implementation
- [ ] SegmentIteratorクラスの実装
- [ ] SPLインターフェイス（IteratorAggregate, Countable）の実装
- [ ] 内部Iteratorクラスの実装

### Phase 4: Testing & Refinement
- [ ] PHPUnit テストの作成
- [ ] 複数言語でのテスト
- [ ] エラーハンドリングの改善
- [ ] メモリ管理の最適化

### Phase 5: Documentation
- [ ] API ドキュメント作成
- [ ] 使用例の作成
- [ ] インストール手順の整備

## Technical Considerations

### Memory Management
- Rustの所有権システムを活用
- PHPのメモリ管理との適切な統合
- リソースの適切な解放

### Error Handling
- ICU4Xエラーの適切なPHP例外への変換
- 不正な入力データの処理
- ロケールエラーの処理

### Performance
- 大きなテキストでの効率的な処理
- メモリ使用量の最適化
- イテレーターの遅延評価

## File Structure

```
src/
├── lib.rs              # モジュールエントリーポイント
├── segmenter.rs        # Segmenterクラス
├── iterator.rs         # Iterator関連クラス
├── utils.rs            # ユーティリティ関数
└── tests/
    ├── segmenter_test.rs
    └── iterator_test.rs

php_tests/
├── SegmenterTest.php
└── IteratorTest.php

Cargo.toml
```

## Dependencies

```toml
[dependencies]
ext-php-rs = "0.14.0"
icu_segmenter = "2.0"
```

## Success Criteria
- 書記素クラスターの正確なセグメンテーション
- PHP標準のSPLインターフェイスとの互換性
- メモリ効率的な実装
- 包括的なテストカバレッジ
- 明確なドキュメント