# Tasks: Phase 2 Slot Collection

**Input**: Design documents from `specs/002-phase-2-slot-collection/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/chat-api.yaml`, `quickstart.md`

**Tests**: Included. The specification defines independent tests for each user story and `quickstart.md` defines validation scenarios.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it touches different files and has no dependency on incomplete tasks.
- **[Story]**: Maps the task to a user story. Setup, foundational, and polish tasks do not use story labels.
- Every task includes an exact file or directory path.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare the existing Phase 1 Laravel and Angular chat foundations for Phase 2 slot collection.

- [X] T001 Verify Phase 1 chat skeleton exists or create missing backend directories in `backend/app/Services/Chat/`
- [X] T002 Verify Phase 1 chat tests exist or create missing backend test directories in `backend/tests/Feature/Chat/` and `backend/tests/Unit/Chat/`
- [X] T003 Verify Angular chatbot structure exists or create missing frontend directories in `frontend/src/app/chatbot/`
- [X] T004 [P] Add Phase 2 OpenAPI contract reference to backend documentation in `backend/docs/chat-api.md`
- [X] T005 [P] Add Phase 2 validation scenario index in `backend/docs/chat-phase-2.md`
- [X] T006 [P] Add frontend Phase 2 type coverage placeholder in `frontend/src/app/chatbot/chat.types.ts`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared slot-state, contract, provider, persistence, and rendering foundations that MUST be complete before any user story can be implemented.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T007 Add `slot_collection`, `awaiting_slots`, and fallback response expectations from `contracts/chat-api.yaml` to `backend/tests/Feature/Chat/ChatContractTest.php`
- [X] T008 Extend chat response DTO or array builder with `slot_collection`, `awaiting_slots`, `fallback`, and `session_id` fields in `backend/app/Http/Controllers/ChatController.php`
- [X] T009 Create `SlotCollectionState` value object for required slots, optional slots, missing order, next question, optional status, search readiness, and clarification in `backend/app/Services/Chat/SlotCollectionState.php`
- [X] T010 Create `SlotValue` value object with value, raw text, currency, and status fields in `backend/app/Services/Chat/SlotValue.php`
- [X] T011 Create `ClarificationRequest` value object with slot name, reason, raw text, and candidate values in `backend/app/Services/Chat/ClarificationRequest.php`
- [X] T012 Update chat log extracted data casting for Phase 2 slot state in `backend/app/Models/ChatLog.php`
- [X] T013 Implement Phase 2 state serialization and hydration helpers in `backend/app/Services/Chat/SlotCollectionState.php`
- [X] T014 Implement ordered required-slot completeness helpers in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T015 Implement safe state merge that preserves existing values on NLU failure in `backend/app/Services/Chat/SlotExtractor.php`
- [X] T016 Update NLU schema validation for required slots, optional slots, clarification, language, and installment flags in `backend/app/Services/Chat/NluResultValidator.php`
- [X] T017 Update OpenRouter NLU prompt rules for slot collection, EGP budget defaulting, optional grouping, and untrusted prior messages in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T018 Update reply composer inputs for next slot prompt, grouped optional prompt, clarification prompt, and fallback state in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T019 Ensure authenticated session ownership is checked before slot state is read or written in `backend/app/Http/Controllers/ChatController.php`
- [X] T020 Add Phase 2 chat response interfaces for `SlotCollectionState`, `SlotValue`, and `ClarificationRequest` in `frontend/src/app/chatbot/chat.types.ts`
- [X] T021 Add frontend slot-state fixture factory for Phase 2 component tests in `frontend/src/app/chatbot/testing/slot-collection.factory.ts`
- [X] T022 Add backend slot-state fixture factory for Phase 2 tests in `backend/tests/Support/SlotCollectionStateFactory.php`

**Checkpoint**: Foundation ready. User story implementation can now begin.

---

## Phase 3: User Story 1 - Collect Required Search Preferences (Priority: P1) MVP

**Goal**: Authenticated buyers can provide property type, location, and maximum budget across turns, while the chatbot asks only for the next missing required preference in deterministic order.

**Independent Test**: Start a session, provide one required preference per turn, and verify stored preferences, missing slot order, next question, and optional readiness after budget capture.

### Tests for User Story 1

- [ ] T023 [P] [US1] Add feature test for property type then location then price slot order in `backend/tests/Feature/Chat/RequiredSlotOrderTest.php`
- [ ] T024 [P] [US1] Add unit test for required slot completeness and next-question calculation in `backend/tests/Unit/Chat/RequiredSlotProgressionTest.php`
- [ ] T025 [P] [US1] Add feature test for numeric budget without currency defaulting to EGP in `backend/tests/Feature/Chat/BudgetCurrencyDefaultTest.php`
- [ ] T026 [P] [US1] Add feature test proving captured required preferences are not re-asked in `backend/tests/Feature/Chat/NoRedundantRequiredPromptTest.php`
- [ ] T027 [P] [US1] Add frontend service test for `awaiting_slots` and `slot_collection` response parsing in `frontend/src/app/chatbot/chat.service.spec.ts`
- [ ] T028 [P] [US1] Add frontend widget test for required-slot awaiting indicators in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts`

### Implementation for User Story 1

- [ ] T029 [US1] Implement required slot merge for `propertyType`, `location`, and `price` in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T030 [US1] Implement numeric budget normalization and EGP default currency in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T031 [US1] Implement `missing_required_slots` and `next_question_slot` calculation in `backend/app/Services/Chat/SlotCollectionState.php`
- [ ] T032 [US1] Persist updated required slot state to `chat_logs.extracted_data` after each user turn in `backend/app/Services/Chat/ChatLogService.php`
- [ ] T033 [US1] Return one missing required slot in `awaiting_slots` from `backend/app/Http/Controllers/ChatController.php`
- [ ] T034 [US1] Compose language-appropriate required-slot prompts for property type, location, and price in `backend/app/Services/Chat/IntentDetectionService.php`
- [ ] T035 [US1] Render required-slot prompt state without unsafe markup in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [ ] T036 [US1] Display current awaiting-slot state in the chatbot widget in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts`

**Checkpoint**: User Story 1 is independently testable and demonstrates the MVP required-slot flow.

---

## Phase 4: User Story 2 - Capture Multiple Preferences From One Message (Priority: P2)

**Goal**: Buyers can provide several clear preferences in one natural message, including Arabic, English, or mixed-language input, and the chatbot stores all clear values before asking only for what remains missing.

**Independent Test**: Send one message containing property type, location, and budget, then verify all required slots are complete and the flow skips directly to optional preference collection.

### Tests for User Story 2

- [ ] T037 [P] [US2] Add feature test for one-message property type, location, and budget capture in `backend/tests/Feature/Chat/MultiSlotExtractionTest.php`
- [ ] T038 [P] [US2] Add feature test for two required preferences plus one optional preference in `backend/tests/Feature/Chat/PartialMultiSlotExtractionTest.php`
- [ ] T039 [P] [US2] Add feature test for Arabic, English, and Arabizi clear-value extraction in `backend/tests/Feature/Chat/MultilingualSlotExtractionTest.php`
- [ ] T040 [P] [US2] Add unit test for latest explicit value replacing an earlier pre-search value in `backend/tests/Unit/Chat/SlotReplacementTest.php`
- [ ] T041 [P] [US2] Add frontend message-list test for no redundant required prompt after full first message in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 2

- [ ] T042 [US2] Update NLU schema parsing to accept multiple required and optional slot values from one turn in `backend/app/Services/Chat/NluResultValidator.php`
- [ ] T043 [US2] Merge all clear slot values before computing the next question in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T044 [US2] Implement latest explicit pre-search slot replacement logic in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T045 [US2] Preserve buyer language and mixed-language raw text in slot values in `backend/app/Services/Chat/SlotValue.php`
- [ ] T046 [US2] Skip required prompts when all required slots are complete in `backend/app/Http/Controllers/ChatController.php`
- [ ] T047 [US2] Surface optional readiness after multi-slot capture in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts`

**Checkpoint**: User Story 2 works independently with a single complete message or mixed partial message.

---

## Phase 5: User Story 3 - Ask One Grouped Optional Preference Question (Priority: P3)

**Goal**: After required slots are complete, the chatbot asks one grouped optional question for area, bedrooms, bathrooms, and features, then proceeds when the buyer provides optional values or declines them.

**Independent Test**: Complete required slots, answer the optional prompt with values or a decline, and verify optional status plus `search_ready`.

### Tests for User Story 3

- [ ] T048 [P] [US3] Add feature test for exactly one grouped optional prompt after required slots in `backend/tests/Feature/Chat/GroupedOptionalPromptTest.php`
- [ ] T049 [P] [US3] Add feature test for optional decline phrases in English and Arabic in `backend/tests/Feature/Chat/OptionalDeclineTest.php`
- [ ] T050 [P] [US3] Add feature test for optional area, bedrooms, bathrooms, and features capture in `backend/tests/Feature/Chat/OptionalSlotCaptureTest.php`
- [ ] T051 [P] [US3] Add unit test for optional status transitions `not_asked`, `asked`, `answered`, `declined`, and `skipped` in `backend/tests/Unit/Chat/OptionalCollectionStateTest.php`
- [ ] T052 [P] [US3] Add frontend component test for grouped optional prompt rendering in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 3

- [ ] T053 [US3] Implement optional slot state fields for `area`, `bedrooms`, `bathrooms`, and `features` in `backend/app/Services/Chat/SlotCollectionState.php`
- [ ] T054 [US3] Implement grouped optional prompt transition after required slots complete in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T055 [US3] Implement optional value merge without blocking on omitted optional categories in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T056 [US3] Implement optional decline detection and `optional_collection_status=declined` handling in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T057 [US3] Set `search_ready=true` after optional values, decline, or skip in `backend/app/Services/Chat/SlotCollectionState.php`
- [ ] T058 [US3] Return `optional_preferences` in `awaiting_slots` only when the grouped optional prompt is due in `backend/app/Http/Controllers/ChatController.php`
- [ ] T059 [US3] Compose the grouped optional preference prompt in `backend/app/Services/Chat/IntentDetectionService.php`
- [ ] T060 [US3] Render grouped optional prompt and optional answered state in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 3 works independently after required slots are complete.

---

## Phase 6: User Story 4 - Respect Cash-Only Search Boundaries (Priority: P4)

**Goal**: Payment method is never collected. Installment, down-payment, and monthly-payment requests trigger the existing cash-only redirect and preserve the next non-payment slot for resuming collection.

**Independent Test**: Ask for installments during slot collection, verify redirect and no payment slot, then accept cash and verify collection resumes from the next missing non-payment slot.

### Tests for User Story 4

- [ ] T061 [P] [US4] Add feature test for installment redirect during required slot collection in `backend/tests/Feature/Chat/Phase2InstallmentRedirectTest.php`
- [ ] T062 [P] [US4] Add feature test for down-payment and monthly-payment mentions in `backend/tests/Feature/Chat/PaymentTermRedirectTest.php`
- [ ] T063 [P] [US4] Add unit test proving payment method is excluded from slot state in `backend/tests/Unit/Chat/PaymentSlotExclusionTest.php`
- [ ] T064 [P] [US4] Add feature test for cash acceptance resuming from `resume_slot` in `backend/tests/Feature/Chat/CashRedirectResumeTest.php`
- [ ] T065 [P] [US4] Add frontend widget test for preserving session after cash-only redirect in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts`

### Implementation for User Story 4

- [ ] T066 [US4] Ensure installment intent branch runs before slot merge in `backend/app/Http/Controllers/ChatController.php`
- [ ] T067 [US4] Implement `CashOnlyRedirectState` storage with `installment_requested`, `redirect_prompted`, and `resume_slot` in `backend/app/Services/Chat/SlotCollectionState.php`
- [ ] T068 [US4] Discard payment method, installment, down-payment, and monthly-payment fields from NLU slots in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T069 [US4] Compose cash-only redirect reply and cash acceptance resume prompt in `backend/app/Services/Chat/IntentDetectionService.php`
- [ ] T070 [US4] Preserve existing non-payment slot state during redirect and resume in `backend/app/Services/Chat/ChatLogService.php`
- [ ] T071 [US4] Display cash-only redirect replies in the normal chat flow in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 4 works independently during any slot-collection step.

---

## Phase 7: User Story 5 - Handle Unclear or Ambiguous Slot Values (Priority: P5)

**Goal**: Unclear, unsupported, or ambiguous required values trigger targeted clarification and remain incomplete until the buyer provides a clear answer, while unrelated completed preferences remain intact.

**Independent Test**: Send ambiguous location, unclear budget, or unsupported property type and verify a targeted clarification with `search_ready=false`; then clarify and verify collection resumes.

### Tests for User Story 5

- [ ] T072 [P] [US5] Add feature test for unclear property type clarification in `backend/tests/Feature/Chat/PropertyTypeClarificationTest.php`
- [ ] T073 [P] [US5] Add feature test for ambiguous location clarification in `backend/tests/Feature/Chat/LocationClarificationTest.php`
- [ ] T074 [P] [US5] Add feature test for unclear budget clarification in `backend/tests/Feature/Chat/BudgetClarificationTest.php`
- [ ] T075 [P] [US5] Add unit test for required slots remaining incomplete while clarification is unresolved in `backend/tests/Unit/Chat/ClarificationStateTest.php`
- [ ] T076 [P] [US5] Add feature test for temporary interpretation failure preserving previous preferences in `backend/tests/Feature/Chat/SlotFallbackPreservationTest.php`
- [ ] T077 [P] [US5] Add frontend message-list test for targeted clarification prompt rendering in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 5

- [ ] T078 [US5] Parse unclear, ambiguous, unsupported, and invalid-format statuses from NLU output in `backend/app/Services/Chat/NluResultValidator.php`
- [ ] T079 [US5] Create targeted clarification state for required slots in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T080 [US5] Prevent required slots with unresolved clarification from being marked complete in `backend/app/Services/Chat/SlotCollectionState.php`
- [ ] T081 [US5] Resume from the first missing required slot after clarification is resolved in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T082 [US5] Handle unclear optional values without blocking search readiness after one concise clarification or decline in `backend/app/Services/Chat/SlotExtractor.php`
- [ ] T083 [US5] Return language-appropriate targeted clarification replies in `backend/app/Services/Chat/IntentDetectionService.php`
- [ ] T084 [US5] Preserve captured slot state when OpenRouter interpretation fails in `backend/app/Services/Chat/OpenRouterService.php`
- [ ] T085 [US5] Render clarification prompts and fallback messages safely in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 5 works independently with ambiguous required values and simulated provider failure.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, documentation, and hardening across all Phase 2 stories.

- [ ] T086 [P] Update Phase 2 implementation notes and deferred later-phase boundaries in `backend/docs/chat-phase-2.md`
- [ ] T087 [P] Update frontend chatbot notes for `awaiting_slots` and grouped optional display in `frontend/src/app/chatbot/README.md`
- [ ] T088 Validate the implemented response against `specs/002-phase-2-slot-collection/contracts/chat-api.yaml`
- [ ] T089 Review prompt-injection handling for prior messages and seller-supplied text in `backend/app/Services/Chat/IntentDetectionService.php`
- [ ] T090 Review fallback logging and slot-state preservation metadata in `backend/app/Services/Chat/OpenRouterService.php`
- [ ] T091 Run backend Phase 2 chat tests and record command outcome in `specs/002-phase-2-slot-collection/validation-results.md`
- [ ] T092 Run frontend chatbot tests and record command outcome in `specs/002-phase-2-slot-collection/validation-results.md`
- [ ] T093 Run manual quickstart scenarios 1 through 9 and record outcomes in `specs/002-phase-2-slot-collection/validation-results.md`
- [ ] T094 Confirm no payment-method slot appears in persisted state or frontend types in `backend/app/Services/Chat/SlotCollectionState.php` and `frontend/src/app/chatbot/chat.types.ts`
- [ ] T095 Confirm Phase 2 does not execute search, ranking, canonical resolution, image display, seller contact, or complaint phone collection in `backend/app/Http/Controllers/ChatController.php`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational; provides MVP required-slot progression.
- **User Story 2 (Phase 4)**: Depends on Foundational and naturally builds on the same merge flow as US1.
- **User Story 3 (Phase 5)**: Depends on Foundational and can use seeded complete required slots.
- **User Story 4 (Phase 6)**: Depends on Foundational; can be tested at any slot-collection step with fixtures.
- **User Story 5 (Phase 7)**: Depends on Foundational; can be tested with fixtures for ambiguous values and fallback failures.
- **Polish (Phase 8)**: Depends on selected user stories being complete.

### User Story Dependencies

- **US1 (P1)**: MVP and first implementation target.
- **US2 (P2)**: Can start after Foundational, but full end-to-end value follows US1.
- **US3 (P3)**: Can start after Foundational using fixture-complete required slots; full flow follows US1 or US2.
- **US4 (P4)**: Can start after Foundational and run in parallel with US2/US3.
- **US5 (P5)**: Can start after Foundational and run in parallel with US2/US3/US4 using explicit unclear-value fixtures.

### Within Each User Story

- Tests are written before implementation tasks.
- Value objects and state helpers precede service orchestration.
- NLU validation precedes slot merge behavior.
- Slot merge behavior precedes controller response integration.
- Backend contract behavior precedes frontend rendering work.
- Story checkpoint must pass before considering that story complete.

---

## Parallel Opportunities

- Setup tasks T004 through T006 can run in parallel after directory verification tasks T001 through T003.
- Foundational value-object tasks T009 through T011 can run in parallel with frontend type task T020 and fixture tasks T021 through T022.
- Test tasks within each user story are marked `[P]` and can run in parallel.
- US2, US3, US4, and US5 can be developed in parallel after Foundational if fixtures are used.
- Polish documentation tasks T086 and T087 can run in parallel.

---

## Parallel Example: User Story 1

```bash
Task: "T023 Add required slot order feature test in backend/tests/Feature/Chat/RequiredSlotOrderTest.php"
Task: "T024 Add required slot progression unit test in backend/tests/Unit/Chat/RequiredSlotProgressionTest.php"
Task: "T025 Add EGP budget default feature test in backend/tests/Feature/Chat/BudgetCurrencyDefaultTest.php"
Task: "T027 Add frontend chat service parsing test in frontend/src/app/chatbot/chat.service.spec.ts"
```

## Parallel Example: User Story 2

```bash
Task: "T037 Add multi-slot extraction feature test in backend/tests/Feature/Chat/MultiSlotExtractionTest.php"
Task: "T039 Add multilingual slot extraction feature test in backend/tests/Feature/Chat/MultilingualSlotExtractionTest.php"
Task: "T040 Add slot replacement unit test in backend/tests/Unit/Chat/SlotReplacementTest.php"
```

## Parallel Example: User Story 3

```bash
Task: "T048 Add grouped optional prompt feature test in backend/tests/Feature/Chat/GroupedOptionalPromptTest.php"
Task: "T049 Add optional decline feature test in backend/tests/Feature/Chat/OptionalDeclineTest.php"
Task: "T051 Add optional collection state unit test in backend/tests/Unit/Chat/OptionalCollectionStateTest.php"
```

## Parallel Example: User Story 4

```bash
Task: "T061 Add installment redirect feature test in backend/tests/Feature/Chat/Phase2InstallmentRedirectTest.php"
Task: "T063 Add payment slot exclusion unit test in backend/tests/Unit/Chat/PaymentSlotExclusionTest.php"
Task: "T065 Add frontend cash redirect session test in frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts"
```

## Parallel Example: User Story 5

```bash
Task: "T072 Add property type clarification feature test in backend/tests/Feature/Chat/PropertyTypeClarificationTest.php"
Task: "T073 Add location clarification feature test in backend/tests/Feature/Chat/LocationClarificationTest.php"
Task: "T076 Add fallback preservation feature test in backend/tests/Feature/Chat/SlotFallbackPreservationTest.php"
Task: "T077 Add frontend clarification rendering test in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundational infrastructure.
3. Complete Phase 3 User Story 1.
4. Stop and validate required slot order, no redundant prompts, EGP budget defaulting, and authenticated persistence.
5. Use the MVP to support later optional, cash-only, and clarification demos.

### Incremental Delivery

1. Deliver US1 for deterministic required-slot progression.
2. Add US2 for multi-value and multilingual capture.
3. Add US3 for grouped optional preferences and search-readiness state.
4. Add US4 for cash-only redirect boundaries.
5. Add US5 for ambiguity handling and fallback preservation.
6. Run Phase 8 quickstart validation across all delivered stories.

### Parallel Team Strategy

1. Backend developer completes value objects, slot merge, controller integration, and provider prompt updates.
2. Frontend developer completes response types, awaiting-slot indicators, and grouped prompt rendering.
3. Test-focused developer writes story tests first and maintains quickstart validation notes.
4. After Foundational, split US2/US3/US4/US5 across developers using slot-state fixtures.

---

## Notes

- Every story has an independent validation path using a new session or seeded slot-state fixtures.
- Payment method is intentionally absent from every slot model, response type, and persistence task.
- Phase 2 stores best-effort extracted values only; canonical property-type, location, and feature resolution remains Phase 2.5.
- Phase 2 does not execute search, ranking, result cards, image display, seller contact lookup, or complaint phone collection.
- Seller-supplied text and prior messages remain untrusted input in prompts and frontend rendering.
