use ext_php_rs::prelude::*;

mod segmenter;
mod iterator;

use segmenter::Segmenter;
use iterator::{SegmentIterator, InternalIterator};

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .class::<Segmenter>()
        .class::<InternalIterator>()
        .class::<SegmentIterator>()
}