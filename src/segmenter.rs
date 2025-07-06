use ext_php_rs::prelude::*;
use icu_segmenter::GraphemeClusterSegmenter;
use crate::iterator::SegmentIterator;

#[php_class]
#[php(name = "ICU4X\\Segmenter")]
pub struct Segmenter {
    mode: String,
    locale: Option<String>,
}

#[php_impl]
impl Segmenter {
    pub fn __construct(mode: Option<String>, locale: Option<String>) -> PhpResult<Self> {
        let mode = mode.unwrap_or_else(|| "grapheme".to_string());
        
        if mode != "grapheme" {
            return Err(PhpException::default(format!("Unsupported mode: {}", mode)));
        }

        Ok(Self {
            mode,
            locale,
        })
    }

    pub fn segment(&self, input: String) -> PhpResult<SegmentIterator> {
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

    pub fn get_mode(&self) -> &str {
        &self.mode
    }

    pub fn get_locale(&self) -> Option<&str> {
        self.locale.as_deref()
    }
}