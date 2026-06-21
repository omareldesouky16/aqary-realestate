# Phase 3 Quickstart: Search, Rank, and Reply Validation

## Prerequisites

- Backend dependencies are installed for the Laravel test harness.
- Frontend dependencies are installed for the Angular chat widget tests.
- Test data includes authenticated buyers, owned chat sessions, canonical locations, property types, optional features, and active/inactive cash/installment listings.
- Phase 2.5 resolver behavior is available so required property type and location are canonical before search readiness.

## Setup

```powershell
cd backend
php artisan test
```

```powershell
cd frontend
npm test
```

Use focused test filters during implementation when available, then run the broader suites before completion.

## Scenario 1: Ranked First Page

1. Authenticate as a buyer and create or reuse an owned chat session.
2. Send a search message with resolved property type, resolved location, maximum budget, and optional preferences such as bedrooms, bathrooms, area, or features.
3. Verify the response has `intent: search_property`, `slot_collection.search_ready: true`, `search.status: results`, and 1 to 5 `properties`.
4. Verify every result is an active cash listing in the resolved scope and priced no higher than 120% of the stated budget.
5. Verify result items include available title, URL, price, area, bedrooms, bathrooms, furnished status, location, and cover image preview indicators.
6. Verify seller phone numbers are absent from the response and reply text.
7. Verify the reply asks whether the buyer wants to see photos.

Expected outcome: the best buyer-fit listings appear first, response time is within 3 seconds in normal validation, and no more than 5 listings are shown.

## Scenario 2: Relevance Beats Promotion

1. Seed at least two eligible listings where one promoted listing is clearly weaker against buyer preferences than a non-promoted listing.
2. Run the search with those preferences.
3. Compare `rank_position` and ranking diagnostics when exposed to tests.

Expected outcome: the stronger buyer-fit listing ranks ahead of the clearly weaker promoted listing. Promotion only changes order when relevance is close.

## Scenario 3: Budget Fallback

1. Search with resolved property type and location, but with a budget below every same-scope active cash listing.
2. Verify no normal result page is returned.
3. Verify `search.status: budget_fallback` and `min_price_fallback` match the minimum same-scope active cash listing price.
4. Send a higher budget in the next buyer message.
5. Verify the existing location, property type, and optional preferences are preserved and search can continue.

Expected outcome: the buyer receives the minimum available price for the same required scope and can adjust budget without repeating unrelated preferences.

## Scenario 4: No Listings In Scope

1. Search with resolved property type and location where no active cash listings exist at any price.
2. Verify `search.status: no_results`, `properties` is empty, and `min_price_fallback` is null.
3. Verify the reply offers to adjust location or property type.

Expected outcome: no fabricated alternatives are shown.

## Scenario 5: Show More Pagination

1. Seed more than 5 matching active cash listings for the same criteria.
2. Run the initial search and record the returned listing IDs and `search.search_id`.
3. Send a buyer message asking for more options.
4. Verify the next response returns the next ranked page, does not repeat previous listings, and keeps the same `search.search_id`.
5. Verify `visible_reference_map` contains positions for the current page only.
6. Continue asking for more until exhausted.

Expected outcome: each show-more request advances through the retained ranked order, updates positional references, and eventually returns `search.status: exhausted` with an adjustment offer.

## Scenario 6: Result References Reset And Refine

1. Complete a search and show at least one result page.
2. Change only budget, bedrooms, bathrooms, area, or features.
3. Verify old visible references are cleared and a refined search is computed from the latest criteria.
4. Complete another search, then explicitly change location or property type.
5. Verify previous result references and ranked result set are cleared before the fresh search starts.

Expected outcome: core changes start a fresh search, while non-core changes are treated as refinements without losing unrelated preferences.

## Scenario 7: Safe And Grounded Rendering

1. Seed listings with missing optional fields, unsafe title text, HTML-like text, instruction-like text, and cover images.
2. Run a search that returns those listings.
3. Verify missing fields are not invented or estimated.
4. Verify seller-supplied text does not alter assistant behavior.
5. Verify Angular renders titles as safe links and does not render unsafe markup.
6. Verify cover image availability is shown only when a cover image exists.

Expected outcome: the reply and UI contain only returned facts, safe links, safe image indicators, and no unsafe rendering.

## Scenario 8: Reply Failure Preserves Results

1. Simulate successful deterministic search and ranking followed by a reply-generation failure.
2. Verify the response uses a language-appropriate friendly fallback.
3. Verify ranked result state and visible references remain persisted.
4. Send a show-more or reference follow-up.

Expected outcome: computed search context is preserved despite the temporary reply failure.

## Scenario 9: Cash-Only Redirect Precedence

1. Send a message mentioning installment, down payment, or monthly payment during search criteria collection.
2. Verify `intent: installment_redirect` and `installment_redirect: true`.
3. Verify no search is run and no installment values become search filters.

Expected outcome: the existing cash-only redirect takes precedence over Phase 3 search.

## Contract Checks

- Backend responses must satisfy [contracts/chat-api.yaml](./contracts/chat-api.yaml).
- Angular `ChatResponse` and result-rendering types must include the `search` object and the enriched `SearchResultItem` payload.
- Bulk search responses must never include seller phone numbers.
- Search events must be reviewable through chat state, logs, or dedicated records without exposing provider secrets.
