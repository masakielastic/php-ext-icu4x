use ext_php_rs::prelude::*;

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

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .class::<Segmenter>()
        .class::<InternalIterator>()
        .class::<SegmentIterator>()
        .function(wrap_function!(test_segmenter))
        .function(wrap_function!(icu_segmenter))
}