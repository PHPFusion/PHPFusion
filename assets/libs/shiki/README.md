# Shiki PHP browser bundle

This directory vendors a project-owned browser bundle built from Shiki 4.4.3.
It includes only the PHP grammar, Ayu Dark, a GitHub Light fallback, and the
JavaScript regular-expression engine used by the PHPFusion error source viewer.

The generated `shiki-php.min.js` is an IIFE and requires no runtime package
loader or CDN request. Rebuild it from the repository root with esbuild after
installing the pinned Shiki version in a temporary dependency directory.

Upstream: https://github.com/shikijs/shiki
License: MIT; see `LICENSE`.
