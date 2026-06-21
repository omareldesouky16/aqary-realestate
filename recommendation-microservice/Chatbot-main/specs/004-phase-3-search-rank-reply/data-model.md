# Phase 3 Data Model: Search, Rank, and Reply

## Search Criteria

Represents the resolved buyer preferences used to decide whether search can run and how listings are scored.

**Fields**

- `session_id`: UUID for the authenticated chat session.
- `property_type_id`: Required canonical property-type ID.
- `property_type_name`: Required canonical display name.
- `location_id`: Required canonical location ID.
- `location_name`: Required canonical display name.
- `max_budget`: Required numeric buyer budget in EGP.
- `budget_window_max`: Derived numeric maximum equal to `max_budget * 1.2`.
- `area`: Optional numeric target or range when available.
- `bedrooms`: Optional numeric target when available.
- `bathrooms`: Optional numeric target when available.
- `feature_ids`: Optional resolved canonical feature IDs.
- `language`: Buyer reply language/register indicator from existing chat state.
- `search_ready`: Boolean true only when required slots are complete and required resolution outcomes are resolved.

**Validation Rules**

- `session_id` must be owner-bound to the authenticated user.
- `property_type_id` and `location_id` must come from resolver-approved canonical records.
- `max_budget` must be positive before search can run.
- Optional unresolved features must not block search and must not be used as filters or matched-feature claims.
- Installment, down-payment, or monthly-payment intent prevents search until the cash-only redirect path is satisfied.

## Candidate Listing

An active cash listing that matches required scope and is eligible for ranking.

**Fields**

- `id`: Listing ID.
- `title`: Seller-supplied title, treated as untrusted display text.
- `url`: Listing page URL.
- `price`: Listing price in EGP.
- `area`: Listing area when available.
- `bedrooms`: Bedroom count when available.
- `bathrooms`: Bathroom count when available.
- `furnished_status`: Furnished status when available.
- `location_id`: Canonical location ID.
- `location_name`: Canonical location display name.
- `property_type_id`: Canonical property-type ID.
- `feature_ids`: Listing feature IDs when available.
- `cover_image_url`: Cover image preview URL when available.
- `is_promoted`: Promotion flag or signal.
- `status`: Listing publication status.
- `payment_type`: Listing payment type.

**Validation Rules**

- Must be `active`.
- Must be cash-only.
- Must match resolved `property_type_id` and `location_id`.
- Must be priced at or below `budget_window_max` for normal result search.
- Seller phone/contact fields are never copied into candidate summaries for bulk search.
- Seller-supplied text must be escaped/sanitized by downstream rendering.

## Ranking Score

Explainable score metadata used to order candidates.

**Fields**

- `listing_id`: Candidate listing ID.
- `total_score`: Final numeric score.
- `price_score`: Score for closeness to buyer budget and not exceeding stated budget where possible.
- `area_score`: Optional score for area preference fit.
- `bedroom_score`: Optional score for bedroom preference fit.
- `bathroom_score`: Optional score for bathroom preference fit.
- `feature_score`: Optional score for matched resolved features.
- `promotion_boost`: Capped minor boost for promoted listings.
- `matched_feature_names`: Feature names supported by listing data.
- `rank_position`: Stable 1-based position in the retained result set.

**Validation Rules**

- Buyer-fit component must dominate `promotion_boost`.
- Promotion may only affect ordering when buyer-fit relevance is close.
- Score components must be reviewable in logs or persisted state.
- Missing listing fields contribute no invented facts and no unsupported matched-feature claims.

## Ranked Result Set

The stable ordered list retained for the current search.

**Fields**

- `search_id`: Stable identifier or content digest for the current criteria.
- `criteria_snapshot`: Resolved `Search Criteria` used to compute the set.
- `ranked_listing_ids`: Ordered listing IDs, limited to 20.
- `ranking_scores`: Score metadata for retained listings.
- `total_ranked_count`: Count of retained ranked listings.
- `shown_count`: Number of retained listings already shown.
- `has_more`: Boolean true when `shown_count < total_ranked_count`.
- `created_at`: Time result set was computed.
- `last_shown_at`: Time most recent page was shown.

**State Transitions**

- `not_started` -> `computed`: Search-ready criteria produce at least one ranked listing.
- `computed` -> `paged`: A result page is shown and `shown_count` advances.
- `paged` -> `exhausted`: A show-more request consumes the final retained listings.
- `computed` or `paged` -> `cleared`: Buyer changes core location or property type, or explicitly starts over.
- `computed` or `paged` -> `refined`: Buyer changes budget, area, bedrooms, bathrooms, or features; old result references are cleared and a new result set is computed.

## Shown Result Page

The current visible group of listings used for comparison and positional references.

**Fields**

- `page_number`: 1-based page number for the current result set.
- `items`: Up to 5 `Search Result Item` summaries.
- `visible_reference_map`: Mapping from page positions 1 through 5 to listing IDs.
- `shown_from_rank`: First rank position included on this page.
- `shown_to_rank`: Last rank position included on this page.
- `has_more_after_page`: Boolean.

**Validation Rules**

- Page size must be 1 to 5.
- New pages replace previous visible positional references.
- "First", "second", and similar references apply to this page only after it is shown.

## Search Result Item

Buyer-facing summary of one ranked listing.

**Fields**

- `id`: Listing ID.
- `position`: 1-based position within the current visible page.
- `rank_position`: Stable rank within the retained result set.
- `title`: Safe display title.
- `url`: Listing page URL.
- `price`: Price when available.
- `area`: Area when available.
- `bedrooms`: Bedroom count when available.
- `bathrooms`: Bathroom count when available.
- `furnished_status`: Furnished status when available.
- `location`: Location display name when available.
- `cover_image_url`: Cover image preview URL when available.
- `has_cover_image`: Boolean.
- `matched_features`: Supported requested features when listing data proves them.

**Validation Rules**

- Missing fields are omitted or represented as unavailable; they are not estimated.
- Seller phone numbers are excluded.
- Title and other seller text are untrusted and must not alter assistant instructions or unsafe rendering.
- URLs must point to the corresponding listing page.

## Budget Fallback

No-results outcome for budgets below available market prices.

**Fields**

- `scope_property_type_id`: Required property-type ID.
- `scope_location_id`: Required location ID.
- `stated_max_budget`: Buyer budget.
- `budget_window_max`: Derived search window maximum.
- `minimum_available_price`: Minimum active cash listing price in the same required scope.
- `available_listing_count_in_scope`: Count of active cash listings in the same required scope.
- `prompted_for_budget_adjustment`: Boolean.

**State Transitions**

- `not_needed` -> `shown`: No listings fit the budget window but same-scope active cash listings exist.
- `shown` -> `adjusted`: Buyer provides a higher budget and search continues with existing scope.
- `shown` -> `criteria_adjustment`: Buyer chooses to change location or property type.

## Search Outcome Event

Reviewable record for search behavior and reliability.

**Fields**

- `session_id`: Chat session UUID.
- `event_type`: `search_results`, `no_results`, `budget_fallback`, `show_more`, `show_more_exhausted`, or `reply_fallback`.
- `criteria_snapshot`: Resolved search criteria at event time.
- `candidate_count`: Number of eligible candidates before retained limit.
- `returned_count`: Number of listings shown in the current reply.
- `retained_count`: Number of ranked listings retained for browsing.
- `minimum_available_price`: Present for budget fallback.
- `latency_ms`: Search and reply latency when available.
- `fallback`: Boolean for friendly fallback paths.

**Validation Rules**

- Event records must not expose seller phone numbers or provider secrets.
- Reply failures after computed results must preserve the ranked result set and visible reference state.
