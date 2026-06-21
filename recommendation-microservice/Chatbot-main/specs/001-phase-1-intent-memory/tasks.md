# Tasks: Phase 1 Intent Detection and Memory

**Input**: Design documents from `specs/001-phase-1-intent-memory/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/chat-api.yaml`, `quickstart.md`

**Tests**: Included. The specification defines independent tests for each user story and `quickstart.md` defines validation scenarios.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it touches different files and has no dependency on incomplete tasks.
- **[Story]**: Maps the task to a user story. Setup, foundational, and polish tasks do not use story labels.
- Every task includes an exact file or directory path.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Establish Laravel backend and Angular frontend structure required by the Phase 1 plan.

- [X] T001 Create Laravel backend project skeleton in `backend/`
- [X] T002 Create Angular frontend project skeleton in `frontend/`
- [X] T003 Configure backend environment example values for chat provider, history limit, and auth in `backend/.env.example`
- [X] T004 [P] Configure backend test bootstrap for chat tests in `backend/phpunit.xml`
- [X] T005 [P] Configure frontend test runner for chatbot tests in `frontend/package.json`
- [X] T006 [P] Add OpenAPI contract artifact reference to backend documentation in `backend/docs/chat-api.md`
- [X] T007 [P] Add frontend chatbot module directory scaffold in `frontend/src/app/chatbot/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before any user story can be implemented.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T008 Create chat sessions migration with `session_id`, `user_id`, and timestamps in `backend/database/migrations/2026_06_20_000001_create_chat_sessions_table.php`
- [X] T009 Create chat logs migration updates for `session_id`, `role`, `message`, `intent_detected`, and `extracted_data` support in `backend/database/migrations/2026_06_20_000002_update_chat_logs_for_chatbot.php`
- [X] T010 Create `ChatSession` model with owner relationship in `backend/app/Models/ChatSession.php`
- [X] T011 Create `ChatLog` model or update existing model for extracted data casting in `backend/app/Models/ChatLog.php`
- [X] T012 Implement session ownership verification and UUID validation in `backend/app/Services/Chat/SessionOwnershipService.php`
- [X] T013 Implement chat log read/write and last-10-turn retrieval in `backend/app/Services/Chat/ChatLogService.php`
- [X] T014 Implement OpenRouter request wrapper with one retry and typed failure result in `backend/app/Services/Chat/OpenRouterService.php`
- [X] T015 Implement shared NLU schema validation for intent, flags, references, slots, and language in `backend/app/Services/Chat/NluResultValidator.php`
- [X] T016 Implement deterministic state merge and reset helpers in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T017 Create chat route protected by Sanctum middleware in `backend/routes/api.php`
- [X] T018 Create base chat controller request pipeline in `backend/app/Http/Controllers/ChatController.php`
- [X] T019 Create frontend chat request/response interfaces from `contracts/chat-api.yaml` in `frontend/src/app/chatbot/chat.types.ts`
- [X] T020 Create authenticated chat service wrapper in `frontend/src/app/chatbot/chat.service.ts`
- [X] T021 Create safe markdown/link rendering utility for assistant replies in `frontend/src/app/chatbot/safe-chat-markdown.pipe.ts`
- [X] T022 Add shared chat fixture factory for backend tests in `backend/tests/Support/ChatTestFactory.php`
- [X] T023 Add shared chatbot test fixture factory for frontend tests in `frontend/src/app/chatbot/testing/chatbot-test.factory.ts`

**Checkpoint**: Foundation ready. User story implementation can now begin.

---

## Phase 3: User Story 1 - Continue a Property Search Conversation (Priority: P1) MVP

**Goal**: Authenticated users can send multilingual real estate messages, get correct intent classification, and keep known preferences across turns.

**Independent Test**: Start a new authenticated chat session, send property type/location, then send budget only. The chatbot preserves earlier preferences and asks only for missing information.

### Tests for User Story 1

- [X] T024 [P] [US1] Add contract test for authenticated `POST /api/chat` request and response shape in `backend/tests/Feature/Chat/ChatContractTest.php`
- [X] T025 [P] [US1] Add feature test for session ownership create, reuse, mismatch, and malformed UUID cases in `backend/tests/Feature/Chat/SessionOwnershipTest.php`
- [X] T026 [P] [US1] Add unit test for last-10-turn retrieval and null-safe preference preservation in `backend/tests/Unit/Chat/ChatMemoryMergeTest.php`
- [X] T027 [P] [US1] Add feature test for search, chitchat, and unclear intent routing in `backend/tests/Feature/Chat/IntentRoutingTest.php`
- [X] T028 [P] [US1] Add frontend service test for authenticated chat request serialization in `frontend/src/app/chatbot/chat.service.spec.ts`

### Implementation for User Story 1

- [X] T029 [US1] Implement authenticated session verification call at the start of `backend/app/Http/Controllers/ChatController.php`
- [X] T030 [US1] Implement last-10-turn history loading and prompt payload assembly in `backend/app/Services/Chat/ChatLogService.php`
- [X] T031 [US1] Implement NLU call orchestration and schema validation usage in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T032 [US1] Implement null-safe search preference merge for `propertyType`, `location`, `price`, `area`, `bedrooms`, `bathrooms`, and `features` in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T033 [US1] Persist user turns with intent and extracted session state in `backend/app/Http/Controllers/ChatController.php`
- [X] T034 [US1] Return `search_property`, `chitchat`, and `unclear` responses with `awaiting_slots` and `session_id` in `backend/app/Http/Controllers/ChatController.php`
- [X] T035 [US1] Render assistant reply and preserve current `session_id` in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts`
- [X] T036 [US1] Render message list with safe assistant text display in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [X] T037 [US1] Implement message input send and disabled/loading states in `frontend/src/app/chatbot/message-input/message-input.component.ts`

**Checkpoint**: User Story 1 is independently testable and demonstrates the MVP chat memory flow.

---

## Phase 4: User Story 2 - Refer to a Previously Shown Property (Priority: P2)

**Goal**: Users can ask about "the first one" or similar references, and the chatbot maps the reference to the correct currently shown property.

**Independent Test**: Seed a session with three shown properties and ask about the first one. The chatbot resolves the first property or asks for clarification when ambiguous.

### Tests for User Story 2

- [X] T038 [P] [US2] Add unit test for position, partial-title, explicit-id, and unresolved reference parsing in `backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php`
- [X] T039 [P] [US2] Add feature test for property-details intent using existing shown properties in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T040 [P] [US2] Add frontend test for rendering property reference cards safely in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 2

- [X] T041 [US2] Add shown property reference list accessors to session state handling in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T042 [US2] Inject current shown properties as untrusted reference data into NLU payload in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T043 [US2] Resolve `resolved_property_id`, `resolved_by`, and `user_reference` against `shown_properties` in `backend/app/Http/Controllers/ChatController.php`
- [X] T044 [US2] Return clarification response when property reference is unresolved in `backend/app/Http/Controllers/ChatController.php`
- [X] T045 [US2] Add property reference model type and sanitizer notes in `frontend/src/app/chatbot/chat.types.ts`
- [X] T046 [US2] Display numbered shown-property options for clarification in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Stories 1 and 2 work independently with seeded shown-property state.

---

## Phase 5: User Story 3 - Redirect Installment Requests (Priority: P3)

**Goal**: Users asking for installments are redirected to cash-only search without storing unsupported payment preferences.

**Independent Test**: Send an installment request, verify `installment_redirect = true`, then accept cash and verify normal search resumes.

### Tests for User Story 3

- [X] T047 [P] [US3] Add feature test for Arabic and English installment redirect detection in `backend/tests/Feature/Chat/InstallmentRedirectTest.php`
- [X] T048 [P] [US3] Add unit test proving installment payment values are not merged into search preferences in `backend/tests/Unit/Chat/InstallmentSlotExclusionTest.php`

### Implementation for User Story 3

- [X] T049 [US3] Add `installment_redirect` handling branch before search preference merge in `backend/app/Http/Controllers/ChatController.php`
- [X] T050 [US3] Ensure installment-related slots are discarded in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T051 [US3] Add reply payload fields for cash-only redirect and acceptance flow in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T052 [US3] Display installment redirect reply and continue sending the same session on cash acceptance in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts`

**Checkpoint**: User Story 3 works independently after foundational chat routing exists.

---

## Phase 6: User Story 4 - Detect Complaints Without Interrupting Normal Exploration (Priority: P4)

**Goal**: Explicit complaints and clear frustration activate complaint handling, while repeated exploration only adds a gentle check-in.

**Independent Test**: Send an explicit complaint and a normal repeated refinement conversation. The explicit complaint triggers handling; normal exploration does not.

### Tests for User Story 4

- [X] T053 [P] [US4] Add unit test for hard complaint and soft check-in thresholds in `backend/tests/Unit/Chat/ComplaintSignalStateTest.php`
- [X] T054 [P] [US4] Add feature test for explicit complaint, frustration, repeat, and contradiction flows in `backend/tests/Feature/Chat/ComplaintDetectionFlowTest.php`

### Implementation for User Story 4

- [X] T055 [US4] Implement complaint signal calculation in `backend/app/Services/Chat/ComplaintSignalService.php`
- [X] T056 [US4] Track `failed_searches`, `repeat_count`, and `slot_contradiction_count` in session state in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T057 [US4] Route hard complaint results and soft check-in flags in `backend/app/Http/Controllers/ChatController.php`
- [X] T058 [US4] Return `isComplaint` and `needsCheckIn` consistently in chat responses in `backend/app/Http/Controllers/ChatController.php`
- [X] T059 [US4] Display complaint and soft check-in replies without special unsafe markup in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 4 works independently with simulated complaint and exploration conversations.

---

## Phase 7: User Story 5 - Start a New Search Within the Same Conversation (Priority: P5)

**Goal**: Users can abandon a completed search and begin a new unrelated search without stale preferences contaminating the new search.

**Independent Test**: Seed completed search state, send a different property type/location, and verify old search-specific state clears while new criteria seed the fresh search.

### Tests for User Story 5

- [X] T060 [P] [US5] Add unit test for explicit and implicit new-search reset triggers in `backend/tests/Unit/Chat/NewSearchResetTest.php`
- [X] T061 [P] [US5] Add feature test for stale preference clearing and counter preservation in `backend/tests/Feature/Chat/NewSearchFlowTest.php`

### Implementation for User Story 5

- [X] T062 [US5] Implement explicit `new_search_requested` reset handling in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T063 [US5] Implement property-type and location-change reset detection after shown results in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T064 [US5] Clear search-specific fields while preserving session-level counters in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T065 [US5] Re-seed fresh search criteria from the triggering message in `backend/app/Http/Controllers/ChatController.php`
- [X] T066 [US5] Add frontend regression test for continuing the same visible session after reset in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts`

**Checkpoint**: All Phase 1 user stories are independently functional and can be validated through quickstart scenarios.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, documentation, and hardening across all Phase 1 stories.

- [X] T067 [P] Update Phase 1 implementation notes and known limits in `backend/docs/chat-phase-1.md`
- [X] T068 [P] Add quickstart scenario coverage notes in `specs/001-phase-1-intent-memory/quickstart.md`
- [X] T069 Run backend chat test suite and record command outcome in `specs/001-phase-1-intent-memory/validation-results.md`
- [X] T070 Run frontend chatbot test suite and record command outcome in `specs/001-phase-1-intent-memory/validation-results.md`
- [X] T071 Validate OpenAPI contract against actual backend response fields in `specs/001-phase-1-intent-memory/contracts/chat-api.yaml`
- [X] T072 Review prompt-injection handling for shown property titles and conversation history in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T073 Review fallback logging and provider error metadata in `backend/app/Services/Chat/OpenRouterService.php`
- [X] T074 Run manual quickstart scenarios 1 through 8 and record outcomes in `specs/001-phase-1-intent-memory/validation-results.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational; provides MVP chat memory.
- **User Story 2 (Phase 4)**: Depends on Foundational and can use seeded shown-property state; naturally follows US1 for end-to-end flow.
- **User Story 3 (Phase 5)**: Depends on Foundational; independent of US2.
- **User Story 4 (Phase 6)**: Depends on Foundational; benefits from US1 state handling but can be tested with fixtures.
- **User Story 5 (Phase 7)**: Depends on Foundational and seeded completed-search state; naturally follows US1/US2 for end-to-end flow.
- **Polish (Phase 8)**: Depends on selected user stories being complete.

### User Story Dependencies

- **US1 (P1)**: MVP and first implementation target.
- **US2 (P2)**: Can be implemented after Foundational using fixtures, but full conversation demo is easier after US1.
- **US3 (P3)**: Can be implemented after Foundational and in parallel with US2.
- **US4 (P4)**: Can be implemented after Foundational and in parallel with US2/US3.
- **US5 (P5)**: Can be implemented after Foundational using fixtures, but full demo is easiest after US1/US2.

### Within Each User Story

- Tests are written before implementation tasks.
- State/model work precedes service orchestration.
- Services precede controller integration.
- Backend contract behavior precedes frontend rendering work.
- Story checkpoint must pass before considering that story complete.

---

## Parallel Opportunities

- Setup tasks T004 through T007 can run in parallel after project skeleton tasks T001 and T002.
- Foundational model, environment, and frontend type tasks can be split across backend and frontend once migrations are defined.
- Test tasks within each user story are marked `[P]` and can run in parallel.
- US2, US3, US4, and US5 can be developed in parallel after Foundational if fixtures are used.
- Polish documentation tasks T067 and T068 can run in parallel.

---

## Parallel Example: User Story 1

```bash
# Backend tests in parallel work streams
Task: "T024 Add contract test in backend/tests/Feature/Chat/ChatContractTest.php"
Task: "T025 Add session ownership test in backend/tests/Feature/Chat/SessionOwnershipTest.php"
Task: "T026 Add memory merge test in backend/tests/Unit/Chat/ChatMemoryMergeTest.php"
Task: "T027 Add intent routing test in backend/tests/Feature/Chat/IntentRoutingTest.php"

# Frontend test in separate work stream
Task: "T028 Add chat service test in frontend/src/app/chatbot/chat.service.spec.ts"
```

## Parallel Example: User Story 2

```bash
Task: "T038 Add property reference unit test in backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php"
Task: "T039 Add property reference feature test in backend/tests/Feature/Chat/PropertyReferenceFlowTest.php"
Task: "T040 Add frontend safe rendering test in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 3

```bash
Task: "T047 Add installment redirect feature test in backend/tests/Feature/Chat/InstallmentRedirectTest.php"
Task: "T048 Add installment slot exclusion unit test in backend/tests/Unit/Chat/InstallmentSlotExclusionTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundational infrastructure.
3. Complete Phase 3 User Story 1.
4. Stop and validate authenticated chat, memory merge, chitchat, unclear intent, and fallback behavior.
5. Use the MVP to support later story demos.

### Incremental Delivery

1. Deliver US1 for authenticated intent detection and memory.
2. Add US2 for shown-property references.
3. Add US3 for installment redirect.
4. Add US4 for complaint hard/soft signals.
5. Add US5 for new-search reset.
6. Run Phase 8 quickstart validation across all delivered stories.

### Parallel Team Strategy

1. Backend developer completes migrations, services, and controller tasks.
2. Frontend developer completes chat service and rendering tasks.
3. Test-focused developer writes story tests before implementation and maintains quickstart validation notes.
4. After Foundational, split US2/US3/US4/US5 across developers using fixtures.

---

## Notes

- Every story has an independent validation path using fixtures or a completed prior flow.
- Seller-supplied titles and prior messages are untrusted in every backend prompt and frontend display path.
- Phase 1 stores extracted real estate preferences but does not perform full canonical location, feature, or property-type resolution; later phases own that resolution.
- Seller contact lookup, image galleries, search ranking, and complaint phone collection are out of scope for this task list.
