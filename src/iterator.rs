use ext_php_rs::prelude::*;
use ext_php_rs::zend::ce;

#[php_class]
#[php(name = "ICU4X\\SegmentIterator")]
#[php(implements(ce = ce::aggregate, stub = "\\IteratorAggregate"))]
#[php(implements(ce = ce::countable, stub = "\\Countable"))]
pub struct SegmentIterator {
    segments: Vec<String>,
    position: usize,
}

#[php_impl]
impl SegmentIterator {
    pub fn new(segments: Vec<String>) -> Self {
        Self { 
            segments,
            position: 0,
        }
    }

    pub fn count(&self) -> usize {
        self.segments.len()
    }

    pub fn get_iterator(&self) -> PhpResult<InternalIterator> {
        Ok(InternalIterator::new(self.segments.clone()))
    }

    pub fn current(&self) -> Option<String> {
        self.segments.get(self.position).cloned()
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

    pub fn to_array(&self) -> Vec<String> {
        self.segments.clone()
    }
}

#[php_class]
#[php(name = "ICU4X\\InternalIterator")]
#[php(implements(ce = ce::iterator, stub = "\\Iterator"))]
pub struct InternalIterator {
    segments: Vec<String>,
    position: usize,
}

#[php_impl]
impl InternalIterator {
    pub fn new(segments: Vec<String>) -> Self {
        Self {
            segments,
            position: 0,
        }
    }

    pub fn current(&self) -> Option<String> {
        self.segments.get(self.position).cloned()
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