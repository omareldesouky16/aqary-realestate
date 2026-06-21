# Tasks: Phase 2.5 Location, Feature, and Property-Type Resolution

**Input**: Design documents from `specs/003-phase-2-5-resolution/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/chat-api.yaml`, `quickstart.md`

**Tests**: Included because the feature specification defines mandatory independent tests and measurable validation outcomes.

**Organization**: Tasks are grouped by user story so each story can be implemented and tested independently after shared foundations are complete.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare shared files and fixtures needed by all resolver stories.

- [X] T001 Create Phase 2.5 backend documentation stub in backend/docs/chat-phase-2-5-resolution.md
- [X] T002 [P] Add Phase 2.5 resolution API notes from specs/003-phase-2-5-resolution/contracts/chat-api.yaml to backend/docs/chat-api.md
- [X] T003 [P] Add reusable resolution fixture builders to backend/tests/Support/ChatTestFactory.php
- [X] T004 [P] Add frontend resolution test payload helpers to frontend/src/app/chatbot/testing/chatbot-test.factory.ts

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core schema, model, contract, and state plumbing required before any user story implementation.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T005 Create managed aliases migration in backend/database/migrations/2026_06_20_000003_create_managed_aliases_table.php
- [X] T006 Create resolution review items migration in backend/database/migrations/2026_06_20_000004_create_resolution_review_items_table.php
- [X] T007 [P] Create ManagedAlias model in backend/app/Models/ManagedAlias.php
- [X] T008 [P] Create ResolutionReviewItem model in backend/app/Models/ResolutionReviewItem.php
- [X] T009 [P] Create resolution DTO value objects in backend/app/Services/Chat/ResolutionData.php
- [X] T010 Implement shared phrase normalization helper in backend/app/Services/Chat/ResolutionNormalizer.php
- [X] T011 Implement shared candidate limiting and outcome formatting in backend/app/Services/Chat/ResolutionCandidateService.php
- [X] T012 Implement resolution state merge rules in backend/app/Services/Chat/ResolutionStateService.php
- [X] T013 Extend NLU result validation for resolution fields in backend/app/Services/Chat/NluResultValidator.php
- [X] T014 Extend slot extractor to preserve raw location, propertyType, and feature phrases in backend/app/Services/Chat/SlotExtractor.php
- [X] T015 Extend chat response serialization with resolution payload fields in backend/app/Http/Controllers/ChatController.php
- [X] T016 [P] Add TypeScript resolution contract types in frontend/src/app/chatbot/chat.types.ts
- [X] T017 Update Angular chat service parsing for resolution payloads in frontend/src/app/chatbot/chat.service.ts

**Checkpoint**: Foundation ready. Resolver stories can now proceed in priority order or in parallel where capacity allows.

---

## Phase 3: User Story 1 - Resolve Buyer Location Phrases (Priority: P1) MVP

**Goal**: Resolve clear location names, aliases, and multilingual phrases to one known location, or ask for up to 3 candidates when ambiguous.

**Independent Test**: Start a search with a clear colloquial or translated location phrase and verify location is resolved, buyer language is preserved, and the bot does not ask for location again.

### Tests for User Story 1

- [X] T018 [P] [US1] Add unit tests for exact, alias, Arabizi, Arabic, ambiguous, and unresolved location phrases in backend/tests/Unit/Chat/LocationResolutionServiceTest.php
- [X] T019 [P] [US1] Add feature tests for location resolution through authenticated /api/chat in backend/tests/Feature/Chat/LocationResolutionFlowTest.php
- [X] T020 [P] [US1] Add frontend candidate prompt tests for location ambiguity in frontend/src/app/chatbot/message-list/message-list.component.spec.ts

### Implementation for User Story 1

- [X] T021 [US1] Implement LocationResolutionService with exact, alias, normalized, and ambiguous matching in backend/app/Services/Chat/LocationResolutionService.php
- [X] T022 [US1] Integrate location resolution before search readiness decisions in backend/app/Services/Chat/IntentDetectionService.php
- [X] T023 [US1] Persist location resolution outcomes and pending clarification state in backend/app/Services/Chat/ChatLogService.php
- [X] T024 [US1] Add location resolution response fields and awaiting_slots behavior in backend/app/Http/Controllers/ChatController.php
- [X] T025 [US1] Render up to 3 safe location candidates in frontend/src/app/chatbot/message-list/message-list.component.ts
- [X] T026 [US1] Document MVP location resolver behavior in backend/docs/chat-phase-2-5-resolution.md

**Checkpoint**: User Story 1 is independently functional and testable as the MVP.

---

## Phase 4: User Story 2 - Resolve Property Type Synonyms (Priority: P2)

**Goal**: Resolve supported property type names, casing variations, Arabic terms, and synonyms before property type can contribute to search readiness.

**Independent Test**: Send a search message with a property type synonym and all other required preferences already known; verify the property type completes only when it maps to a supported category.

### Tests for User Story 2

- [X] T027 [P] [US2] Add unit tests for supported, synonym, casing, spacing, Arabic, unsupported, and correction property type phrases in backend/tests/Unit/Chat/PropertyTypeResolutionServiceTest.php
- [X] T028 [P] [US2] Add feature tests for property type resolution through authenticated /api/chat in backend/tests/Feature/Chat/PropertyTypeResolutionFlowTest.php

### Implementation for User Story 2

- [X] T029 [US2] Implement PropertyTypeResolutionService with supported category and managed alias matching in backend/app/Services/Chat/PropertyTypeResolutionService.php
- [X] T030 [US2] Integrate property type resolution and unsupported clarification in backend/app/Services/Chat/IntentDetectionService.php
- [X] T031 [US2] Update resolution state replacement for corrected property types in backend/app/Services/Chat/ResolutionStateService.php
- [X] T032 [US2] Return property type resolution outcomes in chat responses from backend/app/Http/Controllers/ChatController.php
- [X] T033 [US2] Update frontend chat service expectations for property type canonical values in frontend/src/app/chatbot/chat.service.spec.ts

**Checkpoint**: User Story 2 works independently while preserving User Story 1 behavior.

---

## Phase 5: User Story 3 - Resolve Optional Feature Preferences (Priority: P3)

**Goal**: Resolve clear optional feature phrases, retain multiple clear features from one message, and avoid blocking search readiness on unresolved optional features.

**Independent Test**: Complete required preferences, answer the grouped optional question with several feature phrases, and verify clear features are retained while unclear features trigger at most one concise clarification or are ignored if declined.

### Tests for User Story 3

- [X] T034 [P] [US3] Add unit tests for single, multiple, alias, ambiguous, unresolved, and declined feature phrases in backend/tests/Unit/Chat/FeatureResolutionServiceTest.php
- [X] T035 [P] [US3] Add feature tests for optional feature resolution and non-blocking readiness in backend/tests/Feature/Chat/FeatureResolutionFlowTest.php
- [X] T036 [P] [US3] Add frontend tests for optional feature clarification rendering in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts

### Implementation for User Story 3

- [X] T037 [US3] Implement FeatureResolutionService with multi-feature matching and optional ambiguity handling in backend/app/Services/Chat/FeatureResolutionService.php
- [X] T038 [US3] Integrate optional feature resolution after grouped optional slot collection in backend/app/Services/Chat/IntentDetectionService.php
- [X] T039 [US3] Enforce optional feature non-blocking search readiness in backend/app/Services/Chat/ResolutionStateService.php
- [X] T040 [US3] Persist resolved and unresolved feature outcomes in backend/app/Services/Chat/ChatLogService.php
- [X] T041 [US3] Render safe optional feature clarification candidates in frontend/src/app/chatbot/message-list/message-list.component.ts

**Checkpoint**: User Story 3 works independently and required search readiness is not blocked by unclear optional features.

---

## Phase 6: User Story 4 - Preserve Search State Through Resolution Outcomes (Priority: P4)

**Goal**: Update only the affected preference on resolution success, failure, or clarification while preserving slots, optional preferences, redirect state, fallback counters, and previous state.

**Independent Test**: Use a session with known property type, budget, and optional preferences, then provide an ambiguous location; verify all unrelated preferences remain intact and only location needs clarification.

### Tests for User Story 4

- [X] T042 [P] [US4] Add unit tests for resolution state merge, fallback preservation, correction replacement, and installment precedence in backend/tests/Unit/Chat/ResolutionStateServiceTest.php
- [X] T043 [P] [US4] Add feature tests for state preservation through ambiguous, unresolved, fallback, and installment turns in backend/tests/Feature/Chat/ResolutionStatePreservationFlowTest.php

### Implementation for User Story 4

- [X] T044 [US4] Harden ResolutionStateService so null, ambiguous, unresolved, and fallback outcomes preserve unrelated state in backend/app/Services/Chat/ResolutionStateService.php
- [X] T045 [US4] Ensure installment redirect bypasses resolver writes and preserves pending clarification in backend/app/Services/Chat/IntentDetectionService.php
- [X] T046 [US4] Update chat log snapshots to retain slot, resolution, redirect, complaint, and fallback counters in backend/app/Services/Chat/ChatLogService.php
- [X] T047 [US4] Add fallback response handling for resolver failures in backend/app/Services/Chat/OpenRouterService.php

**Checkpoint**: User Story 4 protects previous story behavior during failure, ambiguity, correction, and cash-only redirect paths.

---

## Phase 7: User Story 5 - Improve Resolution Coverage Over Time (Priority: P5)

**Goal**: Record unresolved and ambiguous phrases in a reviewable form and support future approved aliases through managed project data or configuration without adding an admin UI.

**Independent Test**: Process an initially unresolved phrase, confirm it is recorded, add an approved alias through managed data/configuration, then process the same phrase again and verify it resolves without clarification.

### Tests for User Story 5

- [X] T048 [P] [US5] Add unit tests for review item minimization, candidate snapshots, buyer choice capture, and alias activation in backend/tests/Unit/Chat/ResolutionReviewServiceTest.php
- [X] T049 [P] [US5] Add feature tests for unresolved-to-alias improvement through authenticated /api/chat in backend/tests/Feature/Chat/ResolutionReviewLoopTest.php

### Implementation for User Story 5

- [X] T050 [US5] Implement ResolutionReviewService for unresolved and ambiguous phrase records in backend/app/Services/Chat/ResolutionReviewService.php
- [X] T051 [US5] Record review items from location, property type, and feature resolver outcomes in backend/app/Services/Chat/ChatLogService.php
- [X] T052 [US5] Implement managed alias lookup and active-alias filtering in backend/app/Services/Chat/ResolutionCandidateService.php
- [X] T053 [US5] Add seedable managed alias examples for tests in backend/tests/Support/ChatTestFactory.php
- [X] T054 [US5] Document no-admin-UI alias update workflow in backend/docs/chat-phase-2-5-resolution.md

**Checkpoint**: User Story 5 enables review-driven improvement without exposing unrelated personal data or adding UI scope.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Validation, documentation, and hardening across all stories.

- [ ] T055 [P] Update frontend resolution payload documentation comments in frontend/src/app/chatbot/chat.types.ts
- [ ] T056 [P] Add quickstart scenario coverage notes to backend/docs/chat-phase-2-5-resolution.md
- [ ] T057 Run backend resolution validation with php artisan test --filter=Resolution from backend/
- [ ] T058 Run backend chat regression validation with php artisan test --filter=Chat from backend/
- [ ] T059 Run frontend chat validation with npm test -- --include chat from frontend/
- [ ] T060 Validate prompt-injection, privacy minimization, candidate limit, and cash-only redirect scenarios against specs/003-phase-2-5-resolution/quickstart.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories.
- **User Stories (Phase 3+)**: Depend on Foundational completion.
- **Polish (Phase 8)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **User Story 1 (P1)**: MVP; can start after Foundational and has no dependency on other stories.
- **User Story 2 (P2)**: Can start after Foundational; should preserve US1 behavior when both are present.
- **User Story 3 (P3)**: Can start after Foundational; depends only on shared resolution state, not US1 or US2 implementation details.
- **User Story 4 (P4)**: Best after US1-US3 because it hardens cross-resolver preservation behavior.
- **User Story 5 (P5)**: Can start after Foundational, but complete validation benefits from at least one resolver story producing reviewable outcomes.

### Within Each User Story

- Tests are listed first and should fail before implementation.
- Resolver services before controller/response integration.
- State persistence before frontend rendering validation.
- Story checkpoint validation before moving to the next priority.

---

## Parallel Opportunities

- Setup tasks T002, T003, and T004 can run in parallel after T001 is understood.
- Foundational model/type tasks T007, T008, T009, and T016 can run in parallel.
- Test tasks within each user story marked [P] can run in parallel.
- US1, US2, US3, and US5 can be staffed in parallel after Phase 2 if teams coordinate shared service contracts.
- Frontend rendering tasks can run in parallel with backend service implementation once the contract shapes in T015-T017 are complete.

---

## Parallel Example: User Story 1

```bash
Task: "T018 [P] [US1] Add unit tests for exact, alias, Arabizi, Arabic, ambiguous, and unresolved location phrases in backend/tests/Unit/Chat/LocationResolutionServiceTest.php"
Task: "T019 [P] [US1] Add feature tests for location resolution through authenticated /api/chat in backend/tests/Feature/Chat/LocationResolutionFlowTest.php"
Task: "T020 [P] [US1] Add frontend candidate prompt tests for location ambiguity in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 2

```bash
Task: "T027 [P] [US2] Add unit tests for supported, synonym, casing, spacing, Arabic, unsupported, and correction property type phrases in backend/tests/Unit/Chat/PropertyTypeResolutionServiceTest.php"
Task: "T028 [P] [US2] Add feature tests for property type resolution through authenticated /api/chat in backend/tests/Feature/Chat/PropertyTypeResolutionFlowTest.php"
```

## Parallel Example: User Story 3

```bash
Task: "T034 [P] [US3] Add unit tests for single, multiple, alias, ambiguous, unresolved, and declined feature phrases in backend/tests/Unit/Chat/FeatureResolutionServiceTest.php"
Task: "T035 [P] [US3] Add feature tests for optional feature resolution and non-blocking readiness in backend/tests/Feature/Chat/FeatureResolutionFlowTest.php"
Task: "T036 [P] [US3] Add frontend tests for optional feature clarification rendering in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts"
```

## Parallel Example: User Story 4

```bash
Task: "T042 [P] [US4] Add unit tests for resolution state merge, fallback preservation, correction replacement, and installment precedence in backend/tests/Unit/Chat/ResolutionStateServiceTest.php"
Task: "T043 [P] [US4] Add feature tests for state preservation through ambiguous, unresolved, fallback, and installment turns in backend/tests/Feature/Chat/ResolutionStatePreservationFlowTest.php"
```

## Parallel Example: User Story 5

```bash
Task: "T048 [P] [US5] Add unit tests for review item minimization, candidate snapshots, buyer choice capture, and alias activation in backend/tests/Unit/Chat/ResolutionReviewServiceTest.php"
Task: "T049 [P] [US5] Add feature tests for unresolved-to-alias improvement through authenticated /api/chat in backend/tests/Feature/Chat/ResolutionReviewLoopTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Stop and validate clear, ambiguous, and unresolved location phrases independently.
5. Demo MVP resolver behavior before adding property type or feature resolution.

### Incremental Delivery

1. Setup + Foundational establish schema, DTOs, state merge, and response contract.
2. Add US1 for location resolution MVP.
3. Add US2 for required property type resolution.
4. Add US3 for optional feature resolution without blocking readiness.
5. Add US4 to harden preservation, fallback, and cash-only precedence across all resolvers.
6. Add US5 to close the review and alias improvement loop.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. Backend developer A owns US1 location resolver.
3. Backend developer B owns US2 property type resolver.
4. Backend/frontend developer C owns US3 optional feature payload and rendering.
5. One developer owns US4/US5 cross-cutting hardening after resolver interfaces stabilize.

---

## Notes

- [P] tasks use different files or can proceed without depending on incomplete implementation tasks.
- [US1] through [US5] map directly to the five user stories in `spec.md`.
- Every implementation task names the exact file to create or modify.
- No task adds a buyer-facing or maintainer-facing alias management UI.
- Verify tests fail before implementing each story.
