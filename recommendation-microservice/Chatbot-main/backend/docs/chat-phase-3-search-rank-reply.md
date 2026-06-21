# Phase 3 Search, Rank, and Reply

This document captures the Phase 3 chat behavior for ranked property search, low-budget fallback, show-more pagination, and safe result rendering.

## Validation Checklist

- Search readiness requires resolved property type, resolved location, and a numeric cash budget.
- Active cash listings only are eligible for normal ranked search.
- Results are capped at 5 per visible page and 20 retained for browsing.
- Show-more requests page through retained results without reranking.
- No seller phone numbers are exposed in bulk search replies.
- Low-budget searches return the minimum available same-scope active cash price.
- Core location and property type changes clear stale references.
- Budget fallback state keeps the same resolved scope and minimum active cash price so a later budget adjustment can continue without repeating unrelated preferences.
- Show-more uses the retained ranked listing IDs and current visible page references, not a fresh rerank.
- Search replies omit missing listing facts, expose cover preview availability only as a flag/URL, and keep seller contact fields private.

## Behavior Notes

- **Ranked search**: once property type, location, and budget are resolved, active cash listings in the same scope are ranked deterministically and retained up to 20 results. The visible response page is capped at 5 listings.
- **Low-budget fallback**: if no listing fits the 20 percent budget window but active cash listings exist in the same property type and location, the response status is `budget_fallback`, `properties` is empty, and `min_price_fallback` contains the minimum same-scope price.
- **No listings in scope**: if no active cash listings exist for the resolved property type and location, the response status is `no_results`, no stale property cards are returned, and the buyer is invited to adjust location or property type.
- **Show-more pagination**: show-more requests advance from the retained ranked order and replace the visible reference map so positional references apply only to the current page.
- **Reset and refinement**: property type or location changes clear stale result references. Budget, area, bedroom, bathroom, and feature changes are treated as refinements that recompute the search from preserved resolved criteria.
- **Safety boundaries**: seller-supplied title text is untrusted, seller phone numbers are excluded from bulk search payloads, missing facts are omitted, safe links are rendered by the frontend, and replies can offer photo viewing only from cover preview availability.
- **Reply failure preservation**: deterministic result state is stored before reply composition, allowing a friendly fallback without losing retained results or visible references.

## Quickstart Scenarios

Use the scenarios in `specs/004-phase-3-search-rank-reply/quickstart.md` to validate:

1. Ranked first-page search.
2. Relevance beating promotion.
3. Budget fallback.
4. No listings in scope.
5. Show-more pagination.
6. Result reference reset and refinement.
7. Safe and grounded rendering.
8. Reply-failure state preservation.
9. Cash-only redirect precedence.
