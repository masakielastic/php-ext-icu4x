use ext_php_rs::prelude::*;
use icu_properties::CodePointMapData;
use icu_properties::props::EastAsianWidth;

mod segmenter;
mod iterator;

use segmenter::Segmenter;
use iterator::{SegmentIterator, InternalIterator};

/// Simple test to create SegmentIterator
#[php_function]
pub fn test_segmenter() -> SegmentIterator {
    SegmentIterator::new(vec!["Hello".to_string(), "World".to_string()])
}

/// ICU4X segmenter function for direct text segmentation
#[php_function]
#[php(name = "icu4x_segmenter")]
pub fn icu_segmenter(
    input: String,
    mode: Option<String>,
    locale: Option<String>
) -> SegmentIterator {
    // Create internal Segmenter instance using existing class logic
    let segmenter = match Segmenter::__construct(mode, locale) {
        Ok(seg) => seg,
        Err(_) => {
            // Fallback to default on error
            Segmenter::__construct(Some("grapheme".to_string()), None).unwrap()
        }
    };
    
    // Use existing segment method to ensure consistency
    match segmenter.segment(input) {
        Ok(iterator) => iterator,
        Err(_) => {
            // Return empty iterator on error
            SegmentIterator::new(vec![])
        }
    }
}

/// ICU4X East Asian Width function
#[php_function]
#[php(name = "icu4x_eaw_width")]
pub fn eaw_width(input: String, locale: Option<String>) -> i32 {
    // 入力文字列の最初の文字を取得
    let first_char = match input.chars().next() {
        Some(c) => c,
        None => return -1, // 空文字列はエラー
    };
    
    // East Asian Width プロパティを取得
    let eaw_data = CodePointMapData::<EastAsianWidth>::new();
    let eaw = eaw_data.get(first_char);
    
    // 表示幅を計算
    calculate_display_width(eaw, locale)
}

/// 表示幅を計算する関数
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
        _ => 1, // デフォルトは幅 1
    }
}

/// 東アジア系ロケールかどうかを判定する関数
fn is_east_asian_locale(locale: Option<String>) -> bool {
    match locale {
        Some(loc) => {
            let loc_lower = loc.to_lowercase();
            loc_lower.starts_with("ja") || loc_lower.starts_with("zh") || loc_lower.starts_with("ko")
        }
        None => false,
    }
}

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .class::<Segmenter>()
        .class::<InternalIterator>()
        .class::<SegmentIterator>()
        .function(wrap_function!(test_segmenter))
        .function(wrap_function!(icu_segmenter))
        .function(wrap_function!(eaw_width))
}