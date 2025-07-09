# ICU4X East Asian Width 実装計画

## 概要

`icu4x_eaw_width` 関数を実装し、ICU4C の `icu4c_eaw_width` 関数と同等の機能を提供します。この関数は文字の East Asian Width プロパティに基づいて表示幅を計算します。

## 関数仕様

**関数名**: `icu4x_eaw_width`  
**引数**: 
- `input: String` - 幅を計算する文字列（必須）
- `locale: Option<String>` - ロケール（オプション、デフォルトは None）

**戻り値**: `i32` - 表示幅（1 または 2）、エラー時は -1

## 技術的詳細

### 1. 必要な依存関係

`Cargo.toml` に以下を追加:
```toml
[dependencies]
icu_properties = "2.0"
```

### 2. 実装アプローチ

#### A. Rust 側の実装 (`src/lib.rs`)
```rust
use icu::properties::{maps, EastAsianWidth};

#[php_function]
#[php(name = "icu4x_eaw_width")]
pub fn eaw_width(input: String, locale: Option<String>) -> i32 {
    // 入力文字列の最初の文字を取得
    let first_char = match input.chars().next() {
        Some(c) => c,
        None => return -1, // 空文字列はエラー
    };
    
    // East Asian Width プロパティを取得
    let eaw = maps::east_asian_width().get(first_char);
    
    // 表示幅を計算
    calculate_display_width(eaw, locale)
}

fn calculate_display_width(eaw: EastAsianWidth, locale: Option<String>) -> i32 {
    match eaw {
        EastAsianWidth::Fullwidth | EastAsianWidth::Wide => 2,
        EastAsianWidth::Ambiguous => {
            // ロケールが東アジア系の場合は幅 2、そうでなければ幅 1
            if is_east_asian_locale(locale) {
                2
            } else {
                1
            }
        }
        EastAsianWidth::Halfwidth | EastAsianWidth::Narrow | EastAsianWidth::Neutral => 1,
    }
}

fn is_east_asian_locale(locale: Option<String>) -> bool {
    match locale {
        Some(loc) => {
            let loc_lower = loc.to_lowercase();
            loc_lower.starts_with("ja") || loc_lower.starts_with("zh") || loc_lower.starts_with("ko")
        }
        None => false,
    }
}
```

#### B. モジュール登録
```rust
#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .class::<Segmenter>()
        .class::<InternalIterator>()
        .class::<SegmentIterator>()
        .function(wrap_function!(test_segmenter))
        .function(wrap_function!(icu_segmenter))
        .function(wrap_function!(eaw_width))  // 新しい関数を追加
}
```

### 3. エラーハンドリング戦略

- **入力検証**: 空文字列チェック
- **文字変換**: 無効な UTF-8 文字の処理
- **メモリ管理**: Rust の所有権システムによる自動管理
- **戻り値**: エラー時は -1、正常時は 1 または 2

### 4. ICU4C との互換性

| ICU4C Value | ICU4X Value | 表示幅 |
|-------------|-------------|--------|
| U_EA_FULLWIDTH | EastAsianWidth::Fullwidth | 2 |
| U_EA_WIDE | EastAsianWidth::Wide | 2 |
| U_EA_AMBIGUOUS | EastAsianWidth::Ambiguous | ロケール依存 (1 or 2) |
| U_EA_HALFWIDTH | EastAsianWidth::Halfwidth | 1 |
| U_EA_NARROW | EastAsianWidth::Narrow | 1 |
| U_EA_NEUTRAL | EastAsianWidth::Neutral | 1 |

### 5. 実装ステップ

1. **依存関係の追加**: `Cargo.toml` に `icu_properties` を追加
2. **Rust 関数の実装**: `src/lib.rs` に `eaw_width` 関数を実装
3. **モジュール登録**: `get_module` 関数に新しい関数を追加
4. **ビルドとテスト**: `cargo build` でビルド確認
5. **PHP テスト**: 基本的な動作確認用テストを作成

### 6. テスト方針

```php
// 基本テスト
echo icu4x_eaw_width("A");        // 1 (Narrow)
echo icu4x_eaw_width("あ");       // 2 (Wide)
echo icu4x_eaw_width("ｱ");        // 1 (Halfwidth)
echo icu4x_eaw_width("ア");       // 2 (Wide)

// ロケールテスト
echo icu4x_eaw_width("§", "ja");  // 2 (Ambiguous in Japanese)
echo icu4x_eaw_width("§", "en");  // 1 (Ambiguous in English)
```

この実装により、ICU4C の `icu4c_eaw_width` 関数と同等の機能を ICU4X で実現できます。

## 参考資料

- [ICU4X East Asian Width API](https://unicode-org.github.io/icu4x/rustdoc/icu_properties/maps/fn.east_asian_width.html)
- [ICU4C icu4c_eaw_width 実装](php-ext-icu4c/icu4c.c:58-98)
- [Unicode Standard Annex #11: East Asian Width](https://www.unicode.org/reports/tr11/)