# Tasks: Phase 3 Search, Rank, and Reply

**Input**: Design documents from `specs/004-phase-3-search-rank-reply/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/chat-api.yaml`, `quickstart.md`

**Tests**: Included because the feature specification defines mandatory independent tests, measurable validation outcomes, and quickstart validation scenarios.

**Organization**: Tasks are grouped by user story so each story can be implemented and tested independently after shared search foundations are complete.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare shared documentation, fixtures, and contract references needed by all Phase 3 stories.

- [X] T001 Create Phase 3 backend documentation stub in backend/docs/chat-phase-3-search-rank-reply.md
- [X] T002 [P] Add Phase 3 API contract notes from specs/004-phase-3-search-rank-reply/contracts/chat-api.yaml to backend/docs/chat-api.md
- [X] T003 [P] Add reusable Phase 3 listing, criteria, and search-state fixture builders to backend/tests/Support/ChatTestFactory.php
- [X] T004 [P] Add frontend ranked-result and search-state payload helpers to frontend/src/app/chatbot/testing/chatbot-test.factory.ts
- [X] T005 [P] Add Phase 3 quickstart validation checklist references to backend/docs/chat-phase-3-search-rank-reply.md

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core listing models, search DTOs, response contracts, and state plumbing required before any user story can be implemented.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T006 Create listings migration for active cash search fields in backend/database/migrations/2026_06_20_000005_create_chatbot_listings_table.php
- [X] T007 Create listing features pivot migration in backend/database/migrations/2026_06_20_000006_create_chatbot_listing_feature_table.php
- [X] T008 [P] Create ChatbotListing model in backend/app/Models/ChatbotListing.php
- [X] T009 [P] Create ChatbotListingFeature model in backend/app/Models/ChatbotListingFeature.php
- [X] T010 [P] Create Phase 3 search DTO value objects in backend/app/Services/Chat/SearchData.php
- [X] T011 Implement search criteria extraction from resolved chat state in backend/app/Services/Chat/SearchCriteriaService.php
- [X] T012 Implement ranked result persistence and visible reference state helpers in backend/app/Services/Chat/SearchResultStateService.php
- [X] T013 Implement search outcome event formatting for reviewable chat state in backend/app/Services/Chat/SearchOutcomeService.php
- [X] T014 Extend chat log state snapshots for search criteria, ranked results, shown pages, fallback outcomes, and search events in backend/app/Services/Chat/ChatLogService.php
- [X] T015 Extend NLU validation for show-more result intent and search refinement fields in backend/app/Services/Chat/NluResultValidator.php
- [X] T016 Extend intent routing for show-more, core search change, and refinement detection in backend/app/Services/Chat/IntentDetectionService.php
- [X] T017 Extend chat response serialization with `search`, enriched `properties`, `has_more`, and `min_price_fallback` fields in backend/app/Http/Controllers/ChatController.php
- [X] T018 [P] Add TypeScript `SearchState`, `SearchResultItem`, `RankingScore`, and `BudgetFallback` contract types in frontend/src/app/chatbot/chat.types.ts
- [X] T019 Update Angular chat service parsing and defaults for Phase 3 search payloads in frontend/src/app/chatbot/chat.service.ts
- [X] T020 [P] Add contract baseline tests for Phase 3 response shape in backend/tests/Feature/Chat/SearchContractTest.php
- [X] T021 [P] Add frontend contract parsing tests for Phase 3 response shape in frontend/src/app/chatbot/chat.service.spec.ts

**Checkpoint**: Foundation ready. Search stories can now proceed in priority order or in parallel where capacity allows.

---

## Phase 3: User Story 1 - Show Ranked Search Results (Priority: P1) MVP

**Goal**: Return up to 5 active cash listings ranked by buyer fit after required resolved criteria are complete.

**Independent Test**: Complete a search with resolved property type, resolved location, maximum budget, and optional preferences; verify the chatbot returns the best active cash listings first with core facts, clickable titles, no seller phone numbers, and buyer-language reply text.

### Tests for User Story 1

- [X] T022 [P] [US1] Add unit tests for search readiness, active cash filtering, 20 percent budget window, and unresolved-required blocking in backend/tests/Unit/Chat/PropertySearchServiceTest.php
- [X] T023 [P] [US1] Add unit tests for relevance-first scoring, optional preference scoring, and capped promotion boost in backend/tests/Unit/Chat/PropertyScoringServiceTest.php
- [X] T024 [P] [US1] Add feature tests for authenticated ranked first-page search through /api/chat in backend/tests/Feature/Chat/RankedSearchFlowTest.php
- [X] T025 [P] [US1] Add frontend tests for rendering up to 5 ranked results with safe links and available facts in frontend/src/app/chatbot/message-list/message-list.component.spec.ts

### Implementation for User Story 1

- [X] T026 [US1] Implement active cash listing query and 20 percent budget window in backend/app/Services/Chat/PropertySearchService.php
- [X] T027 [US1] Implement explainable buyer-fit ranking and minor promotion boost in backend/app/Services/Chat/PropertyScoringService.php
- [X] T028 [US1] Implement first-page result selection, top-20 retention, and score metadata storage in backend/app/Services/Chat/SearchResultStateService.php
- [X] T029 [US1] Integrate search execution after resolved search readiness in backend/app/Services/Chat/IntentDetectionService.php
- [X] T030 [US1] Add grounded ranked result reply composition inputs in backend/app/Services/Chat/OpenRouterService.php
- [X] T031 [US1] Return ranked result summaries without seller phone numbers from backend/app/Http/Controllers/ChatController.php
- [X] T032 [US1] Render ranked result title, price, area, bedrooms, bathrooms, furnished status, location, and link in frontend/src/app/chatbot/message-list/message-list.component.ts
- [X] T033 [US1] Document MVP ranked search behavior and ranking rules in backend/docs/chat-phase-3-search-rank-reply.md

**Checkpoint**: User Story 1 is independently functional and testable as the MVP.

---

## Phase 4: User Story 2 - Handle Low Budget With Minimum Available Price (Priority: P2)

**Goal**: When no listings fit the budget window, provide the minimum available price for the same resolved location and property type and preserve state for budget adjustment.

**Independent Test**: Search with a budget below all active cash listings in the resolved scope; verify the chatbot returns the minimum available price, invites budget adjustment, and can continue after a higher budget without repeating unrelated preferences.

### Tests for User Story 2

- [X] T034 [P] [US2] Add unit tests for same-scope minimum available price fallback in backend/tests/Unit/Chat/BudgetFallbackServiceTest.php
- [X] T035 [P] [US2] Add feature tests for low-budget fallback and later budget adjustment through /api/chat in backend/tests/Feature/Chat/BudgetFallbackFlowTest.php
- [X] T036 [P] [US2] Add feature tests for no active cash listings in scope returning no-results adjustment prompt in backend/tests/Feature/Chat/NoListingsSearchFlowTest.php
- [X] T037 [P] [US2] Add frontend tests for budget fallback display and empty properties payload in frontend/src/app/chatbot/message-list/message-list.component.spec.ts

### Implementation for User Story 2

- [X] T038 [US2] Implement same-scope minimum price lookup in backend/app/Services/Chat/BudgetFallbackService.php
- [X] T039 [US2] Integrate budget fallback and no-listings outcomes after empty normal search in backend/app/Services/Chat/PropertySearchService.php
- [X] T040 [US2] Persist budget fallback state and preserve criteria for adjustment in backend/app/Services/Chat/SearchResultStateService.php
- [X] T041 [US2] Add language-appropriate budget fallback reply inputs in backend/app/Services/Chat/OpenRouterService.php
- [X] T042 [US2] Return `search.status`, `min_price_fallback`, and empty result payloads for fallback outcomes in backend/app/Http/Controllers/ChatController.php
- [X] T043 [US2] Render budget fallback replies without stale result cards in frontend/src/app/chatbot/message-list/message-list.component.ts
- [X] T044 [US2] Document low-budget and no-listings fallback behavior in backend/docs/chat-phase-3-search-rank-reply.md

**Checkpoint**: User Story 2 works independently while preserving User Story 1 behavior.

---

## Phase 5: User Story 3 - Page Through More Results (Priority: P3)

**Goal**: Let buyers request the next page of retained ranked results without changing criteria or repeating shown listings.

**Independent Test**: Complete a search with more than 5 matching listings, ask for more results, and verify the chatbot returns the next ranked page, updates visible references, and reports exhaustion when no more retained results remain.

### Tests for User Story 3

- [X] T045 [P] [US3] Add unit tests for result pagination, shown count updates, visible reference replacement, and exhaustion in backend/tests/Unit/Chat/SearchResultStateServiceTest.php
- [X] T046 [P] [US3] Add feature tests for show-more pagination and no-repeat behavior through /api/chat in backend/tests/Feature/Chat/ShowMoreResultsFlowTest.php
- [X] T047 [P] [US3] Add feature tests for show-more before any result set asking for missing preferences in backend/tests/Feature/Chat/ShowMoreWithoutSearchFlowTest.php
- [X] T048 [P] [US3] Add frontend tests for has_more state, current visible page positions, and exhausted message behavior in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts

### Implementation for User Story 3

- [X] T049 [US3] Implement show-more intent detection and routing in backend/app/Services/Chat/IntentDetectionService.php
- [X] T050 [US3] Implement next-page slicing, shown count advancement, and exhaustion status in backend/app/Services/Chat/SearchResultStateService.php
- [X] T051 [US3] Load retained listing summaries for show-more pages in backend/app/Services/Chat/PropertySearchService.php
- [X] T052 [US3] Update positional property reference resolution to use current visible page in backend/app/Services/Chat/SlotExtractor.php
- [X] T053 [US3] Return show-more result pages and `has_more` state from backend/app/Http/Controllers/ChatController.php
- [X] T054 [US3] Render next-page results without previous-page position leakage in frontend/src/app/chatbot/message-list/message-list.component.ts
- [X] T055 [US3] Document show-more pagination and exhaustion rules in backend/docs/chat-phase-3-search-rank-reply.md

**Checkpoint**: User Story 3 works independently with stable retained order and updated visible references.

---

## Phase 6: User Story 4 - Preserve Search Context and Reset Correctly (Priority: P4)

**Goal**: Preserve result context across result turns, clear stale references on core criteria changes, and recompute refined searches for non-core preference changes.

**Independent Test**: Complete a search, ask for more results, then change property type or location; verify old result references clear and a fresh search starts from explicit new criteria while non-core changes act as refinements.

### Tests for User Story 4

- [X] T056 [P] [US4] Add unit tests for core criteria reset, non-core refinement, search digest changes, and preserved failure state in backend/tests/Unit/Chat/SearchContextResetTest.php
- [X] T057 [P] [US4] Add feature tests for changing location or property type after results through /api/chat in backend/tests/Feature/Chat/SearchResetFlowTest.php
- [X] T058 [P] [US4] Add feature tests for budget, area, bedroom, bathroom, and feature refinements after results in backend/tests/Feature/Chat/SearchRefinementFlowTest.php
- [X] T059 [P] [US4] Add frontend tests for clearing stale visible references after fresh search payloads in frontend/src/app/chatbot/message-list/message-list.component.spec.ts

### Implementation for User Story 4

- [X] T060 [US4] Implement criteria snapshot digest and core-change detection in backend/app/Services/Chat/SearchCriteriaService.php
- [X] T061 [US4] Implement result-state clearing for location and property type changes in backend/app/Services/Chat/SearchResultStateService.php
- [X] T062 [US4] Implement refinement recomputation for budget, area, bedrooms, bathrooms, and features in backend/app/Services/Chat/PropertySearchService.php
- [X] T063 [US4] Preserve computed result context after temporary reply failures in backend/app/Services/Chat/ChatLogService.php
- [X] T064 [US4] Add fallback reply path for post-search reply-generation failures in backend/app/Services/Chat/OpenRouterService.php
- [X] T065 [US4] Clear stale frontend result references when `search.search_id` changes in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts
- [X] T066 [US4] Document reset, refinement, and reply-failure preservation behavior in backend/docs/chat-phase-3-search-rank-reply.md

**Checkpoint**: User Story 4 protects search continuity while preventing stale references after buyer pivots.

---

## Phase 7: User Story 5 - Keep Result Replies Safe and Grounded (Priority: P5)

**Goal**: Ensure result replies only state returned facts, keep seller contact private, render safe listing links and cover image indicators, and offer photo viewing without fetching full galleries.

**Independent Test**: Seed listings with missing fields, unsafe title text, and cover images; run a search and verify replies contain only available facts, safe links, no unsafe markup, no seller phone numbers, and a photo-viewing offer.

### Tests for User Story 5

- [X] T067 [P] [US5] Add unit tests for safe result summary formatting, missing-field omission, matched-feature claims, and phone exclusion in backend/tests/Unit/Chat/SearchReplySafetyTest.php
- [X] T068 [P] [US5] Add feature tests for prompt-injection listing text, unsafe markup, cover images, and no seller phone leakage through /api/chat in backend/tests/Feature/Chat/SafeSearchReplyFlowTest.php
- [X] T069 [P] [US5] Add frontend tests for safe title links, cover image preview indicators, missing fields, and sanitized result text in frontend/src/app/chatbot/message-list/message-list.component.spec.ts
- [X] T070 [P] [US5] Add frontend markdown sanitization regression tests for result replies in frontend/src/app/chatbot/safe-chat-markdown.pipe.ts

### Implementation for User Story 5

- [X] T071 [US5] Implement safe result summary formatting and matched-feature filtering in backend/app/Services/Chat/SearchReplyFormatter.php
- [X] T072 [US5] Delimit seller-supplied listing text as untrusted data in reply composition prompts in backend/app/Services/Chat/OpenRouterService.php
- [X] T073 [US5] Enforce seller phone exclusion and missing-field omission in backend/app/Services/Chat/PropertySearchService.php
- [X] T074 [US5] Add cover image preview fields and `show_image_offer` behavior to chat responses in backend/app/Http/Controllers/ChatController.php
- [X] T075 [US5] Render cover image preview indicators and safe listing links in frontend/src/app/chatbot/message-list/message-list.component.ts
- [X] T076 [US5] Harden result markdown/link sanitization for assistant replies in frontend/src/app/chatbot/safe-chat-markdown.pipe.ts
- [X] T077 [US5] Document safe grounded reply, photo-offer, and seller-contact boundaries in backend/docs/chat-phase-3-search-rank-reply.md

**Checkpoint**: User Story 5 hardens all result replies against unsafe content, privacy leaks, and unsupported claims.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Validation, documentation, performance, and hardening across all stories.

- [X] T078 [P] Update frontend Phase 3 payload documentation comments in frontend/src/app/chatbot/chat.types.ts
- [X] T079 [P] Add Phase 3 quickstart scenario coverage notes to backend/docs/chat-phase-3-search-rank-reply.md
- [X] T080 [P] Add search latency measurement and reviewable event assertions to backend/tests/Feature/Chat/RankedSearchFlowTest.php
- [X] T081 Run backend Phase 3 unit and feature validation for backend/tests/Unit/Chat and backend/tests/Feature/Chat with php artisan test --filter=Search from backend/
- [X] T082 Run backend full chat regression validation for backend/tests/Feature/Chat and backend/tests/Unit/Chat with php artisan test --filter=Chat from backend/
- [X] T083 Run frontend chat validation for frontend/src/app/chatbot with npm test -- --include chat from frontend/
- [X] T084 Validate prompt-injection, phone privacy, budget fallback, show-more exhaustion, reply failure, and installment redirect scenarios against specs/004-phase-3-search-rank-reply/quickstart.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories.
- **User Stories (Phase 3+)**: Depend on Foundational completion.
- **Polish (Phase 8)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **User Story 1 (P1)**: MVP; can start after Foundational and has no dependency on other stories.
- **User Story 2 (P2)**: Can start after Foundational; depends on shared search query foundations and should preserve US1 behavior.
- **User Story 3 (P3)**: Best after US1 because it pages through retained ranked results produced by US1.
- **User Story 4 (P4)**: Best after US1-US3 because it hardens reset, refinement, and failure behavior around computed result sets.
- **User Story 5 (P5)**: Can start after Foundational, but complete validation benefits from US1 result payloads and US3 visible page behavior.

### Within Each User Story

- Tests are listed first and should fail before implementation.
- Models and DTOs before services.
- Search/scoring/state services before controller response integration.
- Backend payload support before frontend rendering validation.
- Story checkpoint validation before moving to the next priority.

---

## Parallel Opportunities

- Setup tasks T002, T003, T004, and T005 can run in parallel after T001 is understood.
- Foundational model/type/test tasks T008, T009, T010, T018, T020, and T021 can run in parallel.
- Test tasks within each user story marked [P] can run in parallel.
- US2 and US5 can be staffed in parallel with US3 after the foundation is complete if teams coordinate the shared response contract.
- Frontend rendering tasks can run in parallel with backend service implementation once T017-T019 establish contract shapes.

---

## Parallel Example: User Story 1

```bash
Task: "T022 [P] [US1] Add unit tests for search readiness, active cash filtering, 20 percent budget window, and unresolved-required blocking in backend/tests/Unit/Chat/PropertySearchServiceTest.php"
Task: "T023 [P] [US1] Add unit tests for relevance-first scoring, optional preference scoring, and capped promotion boost in backend/tests/Unit/Chat/PropertyScoringServiceTest.php"
Task: "T024 [P] [US1] Add feature tests for authenticated ranked first-page search through /api/chat in backend/tests/Feature/Chat/RankedSearchFlowTest.php"
Task: "T025 [P] [US1] Add frontend tests for rendering up to 5 ranked results with safe links and available facts in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 2

```bash
Task: "T034 [P] [US2] Add unit tests for same-scope minimum available price fallback in backend/tests/Unit/Chat/BudgetFallbackServiceTest.php"
Task: "T035 [P] [US2] Add feature tests for low-budget fallback and later budget adjustment through /api/chat in backend/tests/Feature/Chat/BudgetFallbackFlowTest.php"
Task: "T036 [P] [US2] Add feature tests for no active cash listings in scope returning no-results adjustment prompt in backend/tests/Feature/Chat/NoListingsSearchFlowTest.php"
Task: "T037 [P] [US2] Add frontend tests for budget fallback display and empty properties payload in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 3

```bash
Task: "T045 [P] [US3] Add unit tests for result pagination, shown count updates, visible reference replacement, and exhaustion in backend/tests/Unit/Chat/SearchResultStateServiceTest.php"
Task: "T046 [P] [US3] Add feature tests for show-more pagination and no-repeat behavior through /api/chat in backend/tests/Feature/Chat/ShowMoreResultsFlowTest.php"
Task: "T047 [P] [US3] Add feature tests for show-more before any result set asking for missing preferences in backend/tests/Feature/Chat/ShowMoreWithoutSearchFlowTest.php"
Task: "T048 [P] [US3] Add frontend tests for has_more state, current visible page positions, and exhausted message behavior in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts"
```

## Parallel Example: User Story 4

```bash
Task: "T056 [P] [US4] Add unit tests for core criteria reset, non-core refinement, search digest changes, and preserved failure state in backend/tests/Unit/Chat/SearchContextResetTest.php"
Task: "T057 [P] [US4] Add feature tests for changing location or property type after results through /api/chat in backend/tests/Feature/Chat/SearchResetFlowTest.php"
Task: "T058 [P] [US4] Add feature tests for budget, area, bedroom, bathroom, and feature refinements after results in backend/tests/Feature/Chat/SearchRefinementFlowTest.php"
Task: "T059 [P] [US4] Add frontend tests for clearing stale visible references after fresh search payloads in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 5

```bash
Task: "T067 [P] [US5] Add unit tests for safe result summary formatting, missing-field omission, matched-feature claims, and phone exclusion in backend/tests/Unit/Chat/SearchReplySafetyTest.php"
Task: "T068 [P] [US5] Add feature tests for prompt-injection listing text, unsafe markup, cover images, and no seller phone leakage through /api/chat in backend/tests/Feature/Chat/SafeSearchReplyFlowTest.php"
Task: "T069 [P] [US5] Add frontend tests for safe title links, cover image preview indicators, missing fields, and sanitized result text in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
Task: "T070 [P] [US5] Add frontend markdown sanitization regression tests for result replies in frontend/src/app/chatbot/safe-chat-markdown.pipe.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate ranked first-page search independently.
5. Demo active cash search, relevance-first ranking, privacy exclusion, and buyer-language reply before adding fallback or pagination.

### Incremental Delivery

1. Setup + Foundational establish schema, DTOs, state persistence, and response contract.
2. Add US1 for ranked active cash search MVP.
3. Add US2 for no-result budget fallback and market minimum guidance.
4. Add US3 for show-more pagination and current-page references.
5. Add US4 for reset/refinement/failure preservation.
6. Add US5 for grounded safe rendering, cover preview indicators, and photo-offer boundaries.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. Backend developer A owns US1 search and scoring.
3. Backend developer B owns US2 fallback and US3 pagination state.
4. Backend/frontend developer C owns US5 safety and rendering.
5. One developer owns US4 reset/refinement once US1-US3 interfaces stabilize.

---

## Notes

- [P] tasks use different files or can proceed without depending on incomplete implementation tasks.
- [US1] through [US5] map directly to the five user stories in `spec.md`.
- Every implementation task names the exact file to create or modify.
- Tests should fail before implementing each story.
- Bulk search tasks must not expose seller phone numbers.
- Search ranking must remain deterministic and explainable; do not delegate ordering to the LLM.
