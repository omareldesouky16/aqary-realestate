# Feature Specification: Phase 3 Search, Rank, and Reply

**Feature Branch**: `004-phase-3-search-rank-reply`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 3"

## Clarifications

### Session 2026-06-20

- Q: How should promoted listings affect ranked search order? -> A: Relevance-first: promoted listings may receive a small boost but must not outrank clearly better preference matches.
- Q: What latency target should Phase 3 search results meet in normal validation scenarios? -> A: Results should appear within 3 seconds in normal validation scenarios.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Show Ranked Search Results (Priority: P1)

An authenticated buyer who has completed the required search preferences can receive a concise set of matching active cash listings, ranked by how well they fit the buyer's budget and preferences. The reply includes enough listing details for comparison and uses the buyer's language and conversational register.

**Why this priority**: This is the first point where slot collection and resolution produce direct buyer value. Without ranked results, the chatbot cannot complete the core property-search journey.

**Independent Test**: Complete a search with resolved property type, resolved location, and maximum budget, plus optional preferences. The chatbot must return the best matching active cash listings first, include core listing facts and clickable listing titles, and avoid exposing seller phone numbers in the bulk result reply.

**Acceptance Scenarios**:

1. **Given** an authenticated buyer has resolved required preferences and matching active cash listings exist, **When** the search is ready, **Then** the chatbot shows up to 5 ranked listings with title, price, area, bedrooms, bathrooms, furnished status, location, and listing link.
2. **Given** optional preferences such as area, bedrooms, bathrooms, or features are known, **When** listings are ranked, **Then** listings that better satisfy those preferences appear ahead of weaker matches even when weaker matches are promoted.
3. **Given** a listing title or feature text contains instruction-like or unsafe content, **When** results are shown, **Then** it is treated as listing text only and does not change assistant behavior or unsafe rendering.
4. **Given** a buyer requests search results, **When** the chatbot returns the result set, **Then** seller phone numbers are not included in the bulk search reply.

---

### User Story 2 - Handle Low Budget With Minimum Available Price (Priority: P2)

When no listings fit the buyer's stated budget window, the chatbot can still help by identifying the minimum available price for the same required search scope and asking whether the buyer wants to adjust their budget.

**Why this priority**: A zero-result search should not feel like a dead end. Budget fallback helps buyers understand the market and continue the journey.

**Independent Test**: Search with a budget that is too low for the chosen location and property type. The chatbot must explain that no listings fit the current budget, provide the minimum available price for that scope when available, and invite the buyer to adjust criteria without losing the search state.

**Acceptance Scenarios**:

1. **Given** no active cash listings fit the buyer's budget window but listings exist in the same location and property type, **When** the search runs, **Then** the chatbot tells the buyer the minimum available price and asks whether they want to adjust their budget.
2. **Given** the buyer accepts a higher budget after the fallback, **When** the new budget is provided, **Then** the search can continue using the existing location, property type, and optional preferences.
3. **Given** no active cash listings exist for the required location and property type at any price, **When** the search runs, **Then** the chatbot says no matching listings are currently available and offers to adjust the location or property type.
4. **Given** a budget fallback reply is produced, **When** the reply is shown, **Then** it remains warm and language-appropriate rather than blunt or discouraging.

---

### User Story 3 - Page Through More Results (Priority: P3)

After the first set of search results is shown, the buyer can ask for more options and receive the next ranked listings from the same search without changing criteria or restarting the conversation.

**Why this priority**: Buyers often want to compare more than the first page. Show-more behavior improves exploration while preserving the meaning of positional references to the current visible page.

**Independent Test**: Complete a search with more than 5 matching listings, ask for more results, and verify the chatbot shows the next ranked listings, updates the visible result references, and tells the buyer when no more results remain.

**Acceptance Scenarios**:

1. **Given** more ranked results remain after the first page, **When** the buyer asks for more options, **Then** the chatbot shows the next page of up to 5 listings from the same search.
2. **Given** a new page of results is shown, **When** the buyer refers to "the first" or "the second" listing afterward, **Then** those references apply to the current visible page, not the previous one.
3. **Given** no more ranked results remain, **When** the buyer asks for more options, **Then** the chatbot says there are no more results for the current search and offers to adjust criteria.
4. **Given** the buyer asks for more results before any search has produced listings, **When** the message is handled, **Then** the chatbot asks for the missing search preferences instead of inventing results.

---

### User Story 4 - Preserve Search Context and Reset Correctly (Priority: P4)

The chatbot preserves ranked results, shown listings, and search progress across result turns, but starts a fresh search when the buyer clearly changes the core search after results have already been shown.

**Why this priority**: Search results must remain stable enough for follow-up references and pagination, while still allowing buyers to pivot to a new property type or location without stale results.

**Independent Test**: Complete a search, ask for more results, then change the property type or location. The chatbot must clear old result references, keep session-level counters, and seed the new search from the latest explicit preferences.

**Acceptance Scenarios**:

1. **Given** a search has returned results, **When** the buyer continues asking about the same criteria, **Then** the ranked result list and shown listing context remain available.
2. **Given** a search has returned results, **When** the buyer explicitly changes property type or location, **Then** the old results are cleared and a fresh search starts from the new explicit criteria.
3. **Given** the buyer changes only budget, area, bedrooms, bathrooms, or optional features, **When** results have already been shown, **Then** the chatbot treats it as a refinement rather than an unrelated new search.
4. **Given** a temporary failure happens after results are computed, **When** the user continues the conversation, **Then** previously computed result context remains available.

---

### User Story 5 - Keep Result Replies Safe and Grounded (Priority: P5)

The chatbot replies about search results only from listing data that was actually returned for the current turn, keeps seller contact private during bulk search, and offers photo viewing as a next step without fetching full galleries yet.

**Why this priority**: Search replies influence financial decisions. Buyers need trustworthy, grounded listing summaries, and sellers need private contact data protected until a later explicit-contact journey.

**Independent Test**: Seed listings with missing fields, unsafe title text, and cover images, then run a search. The chatbot must state only available facts, render safe links, avoid raw unsafe markup, not expose seller phone numbers, and offer to show photos without claiming unavailable details.

**Acceptance Scenarios**:

1. **Given** a returned listing lacks a field, **When** the chatbot summarizes it, **Then** it does not invent or estimate the missing value.
2. **Given** result titles are shown as links, **When** the buyer sees the reply, **Then** each title clearly opens the corresponding listing page.
3. **Given** cover images are available for returned listings, **When** results are shown, **Then** the buyer can see which listings have visual previews.
4. **Given** a search result reply is complete, **When** the chatbot finishes the reply, **Then** it asks whether the buyer wants to see photos for any shown property.

### Edge Cases

- If the buyer is unauthenticated, search must not run and no prior session preferences or listings may be exposed.
- If required property type or location is unresolved or ambiguous, search readiness must remain false and the chatbot must ask for clarification rather than searching.
- If optional feature preferences are unresolved, the search may proceed using the resolved required preferences and any clear optional preferences.
- If the buyer asks about installments, down payment, or monthly payment during search, the existing cash-only redirect must take precedence and no search should run until the buyer confirms cash listings.
- If no active cash listings exist for the resolved scope, the chatbot must avoid fabricated alternatives and offer criteria adjustment.
- If listing text contains prompt-injection instructions, HTML, script-like content, or unsafe markdown, it must be treated as inert listing text.
- If the reply generation temporarily fails after search results are computed, the buyer must receive a friendly fallback and the computed result context must be preserved.
- If more than 20 candidate listings match, only the top ranked set for the current search is retained for result browsing.
- If fewer than 5 listings remain when the buyer asks for more, the chatbot shows only the remaining listings and then marks that no more results remain.
- If the buyer asks for seller contact or full image galleries, those requests are acknowledged as later property-detail flows rather than bundled into bulk search results.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST run property search only after required property type, location, and budget preferences are complete and required real estate values are resolved.
- **FR-002**: The system MUST return only active cash listings for chatbot search results.
- **FR-003**: The system MUST apply the buyer's maximum budget as a search window that allows listings priced up to 20% above the stated budget.
- **FR-004**: The system MUST rank matching listings by relevance to the buyer's stated and optional preferences.
- **FR-005**: The system MUST rank by buyer-fit relevance first, considering area, bedrooms, bathrooms, resolved features, and price closeness before applying any listing promotion signal.
- **FR-006**: The system MUST allow promoted listings to receive only a minor ranking boost that cannot cause a clearly weaker preference match to outrank a clearly stronger match.
- **FR-007**: The system MUST return no more than 5 listings in the first search reply.
- **FR-008**: The system MUST retain the ranked result order for the current search so later "show more" requests continue from the same result set.
- **FR-009**: The system MUST track how many ranked results have already been shown for the current search.
- **FR-010**: The system MUST allow the buyer to request more results after a search and receive the next page of up to 5 listings.
- **FR-011**: The system MUST tell the buyer when no more results remain for the current search and offer to adjust criteria.
- **FR-012**: The system MUST update the visible shown-listing references whenever a new result page is shown.
- **FR-013**: The system MUST include listing title, listing link, price, area, bedrooms, bathrooms, furnished status, location, and cover image preview in search result presentation when available.
- **FR-014**: The system MUST mention matched requested features only when the listing data supports them, and must phrase them as based on the listing.
- **FR-015**: The system MUST ask whether the buyer wants to see photos after showing search results.
- **FR-016**: The system MUST NOT include seller phone numbers in bulk search results.
- **FR-017**: The system MUST NOT invent, estimate, or infer listing details that are not present in returned listing data.
- **FR-018**: The system MUST provide a minimum-available-price fallback when no listings fit the budget window but listings exist for the same resolved location and property type.
- **FR-019**: The system MUST preserve search state after budget fallback so the buyer can adjust budget without repeating unrelated preferences.
- **FR-020**: The system MUST clear previous result references and start a fresh search when the buyer changes core property type or location after results have been shown.
- **FR-021**: The system MUST treat changes to budget, area, bedrooms, bathrooms, or features after results as refinements unless the buyer clearly asks to start over.
- **FR-022**: The system MUST keep replies in the buyer's language and conversational register.
- **FR-023**: The system MUST record search outcome, result count, no-result outcome, budget fallback outcome, and show-more exhaustion in a reviewable form.
- **FR-SEC**: The system MUST keep private processing, session ownership checks, and seller contact data outside the user's device except when a later explicit-contact flow permits a single-property contact response.
- **FR-DATA**: The system MUST use only resolved and validated real estate preferences before search state, result selection, ranking, or user-visible readiness are affected.
- **FR-SAFE**: The system MUST treat seller-supplied listing content and user messages as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Search Criteria**: The resolved property type, resolved location, maximum budget, and optional preferences used to decide whether a search can run.
- **Candidate Listing**: An active cash listing that matches the required search scope and is eligible for ranking.
- **Ranked Result Set**: The ordered list of candidate listings retained for the current search, limited to the top search candidates available for browsing.
- **Shown Result Page**: The current visible group of up to 5 ranked listings used for buyer comparison and positional references.
- **Budget Fallback**: The no-results outcome that tells the buyer the minimum available price for the same required scope when budget is too low.
- **Search Reply**: The buyer-facing message and listing presentation that summarizes only returned listing facts and offers next steps.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, 100% of searches with complete resolved required preferences and matching active cash listings return at least one ranked result.
- **SC-002**: In validation scenarios, 100% of first-page search replies show no more than 5 listings.
- **SC-003**: In validation scenarios with more than 5 results, 100% of show-more requests return the next ranked listings without repeating previously shown listings from the same search.
- **SC-004**: In validation scenarios, 100% of exhausted show-more requests tell the buyer no more results remain and offer criteria adjustment.
- **SC-005**: In validation scenarios with no listings inside the budget window but listings available in scope, 100% of replies provide the minimum available price and invite budget adjustment.
- **SC-006**: In validation scenarios, 100% of bulk search replies exclude seller phone numbers.
- **SC-007**: In validation scenarios, 100% of result replies include only facts present in returned listing data.
- **SC-008**: In validation scenarios, 100% of searches with unresolved or ambiguous required values ask for clarification instead of running a search.
- **SC-009**: In validation scenarios, 100% of result pages update positional references to the current visible page.
- **SC-010**: In validation scenarios, 100% of result replies preserve the buyer's language and conversational register.
- **SC-011**: In validation scenarios, promoted listings outrank non-promoted listings only when buyer-fit relevance is otherwise close enough that the promoted listing is not a clearly weaker match.
- **SC-012**: In validation scenarios, the first page of normal search results appears within 3 seconds after the search becomes ready.
- **SC-SAFETY**: Prompt-injection or unsafe markup inside listing text changes assistant behavior or unsafe rendering in 0 validation scenarios.
- **SC-RELIABILITY**: In simulated temporary reply failures after results are computed, 100% of sessions preserve computed result context and return a language-appropriate fallback.

## Assumptions

- Phase 3 starts after authenticated session ownership, intent detection, slot collection, cash-only redirect, and deterministic preference resolution are available.
- Property type and location must already be resolved to known values before Phase 3 search can run.
- Optional preferences improve ranking but do not block search when absent or unresolved.
- Bulk search results include cover image previews when available, but full image galleries are handled by a later property-detail or image-viewing phase.
- Seller phone lookup is out of scope for Phase 3 bulk search and belongs to a later explicit single-property contact flow.
- Property details beyond the result summary, full gallery viewing, seller contact, complaint phone collection, and property-page entry behavior remain later-phase work.
- The chatbot uses the latest explicit buyer preferences for the current search and follows existing new-search reset rules from earlier phases.
