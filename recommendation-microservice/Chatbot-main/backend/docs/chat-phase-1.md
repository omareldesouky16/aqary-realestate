# Phase 1 Chatbot Implementation Notes

Phase 1 implements the server-side AI boundary, authenticated session ownership, last-10-turn
memory, deterministic state merge, property-reference resolution, installment redirects,
complaint signal classification, new-search reset behavior, and safe fallback handling.

Known limits:

- Full slot collection is deferred to Phase 2.
- Canonical location, feature, and property-type resolution are deferred to Phase 2.5.
- Search execution, ranking, seller phone lookup, images, and complaint phone collection are out of scope.
- The current workspace contains a Laravel-shaped skeleton; install framework dependencies before running `php artisan test`.
