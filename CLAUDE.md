# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commit Rules

This project follows Conventional Commits specification for commit messages:

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- **refactor**: A code change that neither fixes a bug nor adds a feature
- **perf**: A code change that improves performance
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to the build process or auxiliary tools and libraries

**Format**: `type(scope): description`

**Examples**:
- `feat(icu4x): add DateTimeFormatter class`
- `fix(collator): handle null pointer in comparison`
- `docs(readme): update installation instructions`
- `test(formatter): add unit tests for date formatting`

## Project Architecture

This is a PHP extension for ICU4X (International Components for Unicode). Key architectural considerations:

### PHP Extension Structure
- Extension entry point and module definitions
- Zend Engine integration for PHP classes and functions
- Memory management using PHP's memory allocation functions
- Error handling using PHP's exception system

### ICU4X Integration
- FFI bindings to ICU4X Rust library
- Data provider management for Unicode data
- Locale and formatting API wrappers
- Thread-safe operations for multi-threaded environments

### Development Commands

Since this is a PHP extension project, typical commands will include:

```bash
# Build extension
phpize
./configure
make

# Install extension
sudo make install

# Run tests
make test

# Clean build artifacts
make clean
phpize --clean
```

## Key Development Guidelines

- Use PHP's memory management functions (emalloc, efree, etc.)
- Implement proper error handling with PHP exceptions
- Follow PHP extension naming conventions
- Ensure thread safety for ZTS builds
- Handle resource cleanup properly in destructors
- Use appropriate Zend macros for parameter parsing