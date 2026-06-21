# Phase 3 Research: Search, Rank, and Reply

## Decision: Keep search execution fully server-side behind authenticated chat

**Rationale**: The constitution requires authenticated Laravel Sanctum access, owner-bound `session_id`, server-side provider calls, and private data protection. Search uses resolved session state and listing records that include seller-private fields, so the browser must receive only the curated response payload.

**Alternatives considered**: Client-side search was rejected because it would expose listing fields and session state. Direct OpenRouter search/ranking was rejected because it would make ranking opaque and allow untrusted model output to affect result selection.

## Decision: Search only after canonical required criteria are resolved

**Rationale**: Phase 2.5 resolves property type and location to known canonical values. Phase 3 should only run when property type, location, and maximum budget are complete and required resolution status is `resolved`, preventing ambiguous phrases from becoming SQL filters.

**Alternatives considered**: Searching on raw text was rejected because alias and ambiguity handling would be bypassed. Running partial searches without required criteria was rejected because it would produce noisy or misleading results.

## Decision: Apply budget as a maximum plus 20% window

**Rationale**: The spec requires allowing listings priced up to 20% above the stated budget. This keeps the search helpful in markets where buyer budgets are approximate while preserving the buyer's stated ceiling as a scoring signal.

**Alternatives considered**: A strict maximum was rejected because it conflicts with FR-003. Unlimited above-budget search was rejected because it would reduce buyer trust and weaken relevance.

## Decision: Use explainable weighted scoring with promotion as a minor tie-breaker

**Rationale**: Ranking must prefer buyer fit across price closeness, area, bedrooms, bathrooms, and matched features before promotion. A weighted score with capped promotion points is transparent, testable, and auditable. Promotion can influence close matches but cannot overcome a clearly stronger preference match.

**Alternatives considered**: Database ordering by promotion first was rejected because it violates relevance-first behavior. LLM-generated ranking was rejected because it is hard to reproduce and audit. Randomized result rotation was rejected because it would break stable pagination and positional references.

## Decision: Retain a stable ranked result set for the current search

**Rationale**: Show-more pagination and positional references require stable ordering. Retaining up to the top 20 ranked listing IDs with score metadata in session state lets later turns page without reranking or repeating listings.

**Alternatives considered**: Re-running the full search for each show-more request was rejected because listing changes or score drift could repeat or skip items. Persisting every matching candidate was rejected because Phase 3 only needs the top browsing set and should limit session payload size.

## Decision: Store only safe result summary fields in chat response payloads

**Rationale**: Bulk search replies need listing facts, safe links, and cover image previews, but not seller phone numbers or raw unsafe markup. Returning a bounded summary payload gives Angular everything needed for display while maintaining privacy and rendering safety.

**Alternatives considered**: Returning full listing records was rejected because it risks exposing private or irrelevant fields. Returning text-only replies was rejected because the spec requires clickable titles and cover image preview availability.

## Decision: Budget fallback queries ignore the budget window but keep required scope

**Rationale**: When no listings fit the budget window, the useful fallback is the minimum available price for the same resolved location and property type among active cash listings. Optional preferences may be preserved in state but should not hide the market minimum for the required scope.

**Alternatives considered**: Returning generic "no results" was rejected because it does not support buyer adjustment. Computing minimum price across all locations or property types was rejected because it would be misleading.

## Decision: Treat core criteria changes as new searches and other changes as refinements

**Rationale**: Changing property type or location after results are shown invalidates result references, so old ranked result state must be cleared. Budget, area, bedrooms, bathrooms, and features can refine the existing journey while still clearing and recomputing the ranked result set.

**Alternatives considered**: Keeping old results after any change was rejected because references would become stale. Starting over for every budget or optional preference change was rejected because it would force buyers to repeat known preferences.

## Decision: Preserve computed result context on reply-generation failure

**Rationale**: Search and ranking are deterministic and may succeed before the LLM or reply composer fails. Persisting computed result context before reply wording allows a language-appropriate fallback while retaining show-more and reference continuity.

**Alternatives considered**: Rolling back computed results on reply failure was rejected because it loses useful state. Exposing raw error details was rejected because it is poor UX and can leak provider behavior.
