# Tasks: Phase 5 Complaint Handling

**Input**: Design documents from `/specs/006-phase-5-complaint-handling/`

**Prerequisites**: `plan.md` (required), `spec.md` (required for user stories), `research.md`, `data-model.md`, `contracts/chat-api.yaml`, `quickstart.md`

**Tests**: Included because the feature specification defines mandatory independent tests, measurable success criteria, and quickstart validation scenarios for each complaint journey.

**Organization**: Tasks are grouped by user story so each complaint journey can be implemented and validated independently after shared complaint state foundations are complete.

## Format: `[ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story this task belongs to (`US1`, `US2`, `US3`, etc.)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare Phase 5 complaint documentation, contract references, and test fixtures used by all complaint journeys.

- [X] T001 Create Phase 5 backend documentation scaffold in `backend/docs/chat-phase-5-complaint-handling.md`
- [X] T002 [P] Confirm Phase 5 plan context in `AGENTS.md` points to `specs/006-phase-5-complaint-handling/plan.md`
- [X] T003 [P] Add Phase 5 contract notes from `specs/006-phase-5-complaint-handling/contracts/chat-api.yaml` to `backend/docs/chat-api.md`
- [X] T004 [P] Add complaint case, phone, and event fixture builders to `backend/tests/Support/ChatTestFactory.php`
- [X] T005 [P] Add frontend complaint response fixture helpers to `frontend/src/app/chatbot/testing/chatbot-test.factory.ts`
- [X] T006 [P] Add Phase 5 quickstart validation references to `backend/docs/chat-phase-5-complaint-handling.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core complaint state, phone validation, response contract, logging, and routing primitives required before any story can be implemented.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T007 Add `ComplaintCase`, `ComplaintStage`, `FollowUpPhone`, and `ComplaintEvent` TypeScript contract types in `frontend/src/app/chatbot/chat.types.ts`
- [X] T008 Extend Angular chat service defaults for `complaint_case`, `isComplaint`, and `needsCheckIn` in `frontend/src/app/chatbot/chat.service.ts`
- [X] T009 Extend Phase 5 response contract fields for complaint state and events in `specs/006-phase-5-complaint-handling/contracts/chat-api.yaml`
- [X] T010 Create complaint state transition service in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T011 Create Egyptian phone validation and normalization service in `backend/app/Services/Chat/FollowUpPhoneService.php`
- [X] T012 Extend NLU validation for complaint acceptance, issue text, phone text, and decline-contact signals in `backend/app/Services/Chat/NluResultValidator.php`
- [X] T013 Extend complaint signal calculation for hard complaint, soft check-in, repeated failed search, repeat, and slot contradiction state in `backend/app/Services/Chat/ComplaintSignalService.php`
- [X] T014 Extend chat log snapshots with `complaint_case`, `complaint_events`, phone attempt state, and review-safe event persistence in `backend/app/Services/Chat/ChatLogService.php`
- [X] T015 Extend chat controller response shape with `complaint_case` and complaint-state gating defaults in `backend/app/Http/Controllers/ChatController.php`
- [X] T016 [P] Add contract baseline tests for Phase 5 complaint response shape in `backend/tests/Feature/Chat/ChatContractTest.php`
- [X] T017 [P] Add frontend parsing tests for complaint response defaults in `frontend/src/app/chatbot/chat.service.spec.ts`

**Checkpoint**: Foundation ready. Complaint user stories can now proceed in priority order or in parallel where file ownership allows.

---

## Phase 3: User Story 1 - Route Genuine Complaints to Follow-Up (Priority: P1) MVP

**Goal**: Route explicit complaints, clear frustration, or repeated unsuccessful searches into full complaint handling and stop normal search/detail/contact behavior for that turn.

**Independent Test**: Send an explicit complaint, a clear frustration message, and a session with repeated failed searches; verify complaint handling starts, the reply is empathetic, the issue description is requested, and no property search/photo/contact work continues in the same turn.

### Tests for User Story 1

- [X] T018 [P] [US1] Add unit tests for hard complaint signals and repeated failed-search thresholds in `backend/tests/Unit/Chat/ComplaintSignalServiceTest.php`
- [X] T019 [P] [US1] Add unit tests for complaint stage initialization and event creation in `backend/tests/Unit/Chat/ComplaintStateServiceTest.php`
- [X] T020 [P] [US1] Add feature tests for explicit complaint, frustration, and repeated failed-search routing in `backend/tests/Feature/Chat/ComplaintDetectionFlowTest.php`
- [X] T021 [P] [US1] Add feature tests proving complaint turns block search, photo, property-detail, and seller-contact behavior in `backend/tests/Feature/Chat/ComplaintBlocksNormalFlowTest.php`
- [X] T022 [P] [US1] Add frontend tests for complaint acknowledgement rendering in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 1

- [X] T023 [US1] Implement hard complaint routing and repeated failed-search threshold handling in `backend/app/Services/Chat/ComplaintSignalService.php`
- [X] T024 [US1] Implement complaint start transition to `awaiting_issue` with `started` event in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T025 [US1] Wire complaint precedence before search, property detail, photo, and seller-contact execution in `backend/app/Http/Controllers/ChatController.php`
- [X] T026 [US1] Add empathetic complaint acknowledgement reply path in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T027 [US1] Persist complaint start outcomes and blocked-normal-flow decisions in `backend/app/Services/Chat/ChatLogService.php`
- [X] T028 [US1] Render complaint acknowledgement state and issue request hints in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 1 is independently functional and testable as the complaint MVP.

---

## Phase 4: User Story 2 - Offer Help Without Interrupting Normal Exploration (Priority: P2)

**Goal**: Treat softer stuck signals as normal exploration with a single gentle follow-up offer instead of starting full complaint handling.

**Independent Test**: Repeat the same request or repeatedly correct one preference without hard complaint language; verify the active journey continues, only one gentle offer appears, no phone is requested, and no complaint case is saved until the buyer accepts help.

### Tests for User Story 2

- [X] T029 [P] [US2] Add unit tests for soft repeat and slot-correction signals in `backend/tests/Unit/Chat/ComplaintSignalServiceTest.php`
- [X] T030 [P] [US2] Add feature tests for soft check-in without complaint escalation in `backend/tests/Feature/Chat/SoftComplaintCheckInFlowTest.php`
- [X] T031 [P] [US2] Add feature tests for buyer acceptance of gentle offer entering full complaint flow in `backend/tests/Feature/Chat/SoftComplaintAcceptanceFlowTest.php`
- [X] T032 [P] [US2] Add frontend tests for one-time gentle follow-up offer rendering in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 2

- [X] T033 [US2] Implement soft check-in state without phone request in `backend/app/Services/Chat/ComplaintSignalService.php`
- [X] T034 [US2] Implement `check_in` to `awaiting_issue` transition when buyer accepts help in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T035 [US2] Preserve normal search/detail response execution while appending a gentle offer in `backend/app/Http/Controllers/ChatController.php`
- [X] T036 [US2] Add non-repeating gentle offer reply text in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T037 [US2] Persist `soft_check_in` events without creating saved complaints in `backend/app/Services/Chat/ChatLogService.php`
- [X] T038 [US2] Render soft check-in offer state without phone prompts in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 2 works independently while preserving normal exploration.

---

## Phase 5: User Story 3 - Capture Complaint Details (Priority: P3)

**Goal**: Capture a concise issue summary after complaint handling starts and ask for a follow-up phone number only after useful complaint details are available.

**Independent Test**: Start complaint handling, provide an issue description, and verify a concise summary is recorded, the stage advances to `awaiting_phone`, and unclear descriptions trigger one clarification instead of invented complaint text.

### Tests for User Story 3

- [X] T039 [P] [US3] Add unit tests for issue summary extraction, empty description handling, and untrusted text treatment in `backend/tests/Unit/Chat/ComplaintStateServiceTest.php`
- [X] T040 [P] [US3] Add feature tests for issue capture and phone request progression in `backend/tests/Feature/Chat/ComplaintIssueCaptureFlowTest.php`
- [X] T041 [P] [US3] Add feature tests for unclear complaint description clarification in `backend/tests/Feature/Chat/ComplaintIssueClarificationFlowTest.php`
- [X] T042 [P] [US3] Add frontend tests for issue-captured and phone-request prompt display in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 3

- [X] T043 [US3] Implement issue summary capture and `awaiting_phone` transition in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T044 [US3] Implement unclear or empty issue clarification handling in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T045 [US3] Wire issue capture before normal chat processing while complaint is active in `backend/app/Http/Controllers/ChatController.php`
- [X] T046 [US3] Add phone-request and issue-clarification reply paths in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T047 [US3] Persist `issue_captured` and `phone_requested` complaint events in `backend/app/Services/Chat/ChatLogService.php`
- [X] T048 [US3] Render complaint issue summary status and phone-request state in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 3 captures useful complaint details and advances to phone collection.

---

## Phase 6: User Story 4 - Validate and Store Follow-Up Phone (Priority: P4)

**Goal**: Accept and normalize valid Egyptian mobile numbers, reject malformed input with one retry, and preserve complaints when contact is declined.

**Independent Test**: Submit valid local, valid country-code, invalid, retried, and declined phone responses; verify valid numbers normalize to `+201XXXXXXXXX`, malformed input stays unconfirmed, and declined contact preserves the complaint record.

### Tests for User Story 4

- [X] T049 [P] [US4] Add unit tests for Egyptian local, `+20`, `0020`, invalid, and declined phone cases in `backend/tests/Unit/Chat/FollowUpPhoneServiceTest.php`
- [X] T050 [P] [US4] Add unit tests for phone attempt counts, retry transition, saved transition, and declined transition in `backend/tests/Unit/Chat/ComplaintStateServiceTest.php`
- [X] T051 [P] [US4] Add feature tests for valid phone normalization and complaint saving in `backend/tests/Feature/Chat/ComplaintPhoneFlowTest.php`
- [X] T052 [P] [US4] Add feature tests for invalid phone retry and declined contact preservation in `backend/tests/Feature/Chat/ComplaintPhoneRetryFlowTest.php`
- [X] T053 [P] [US4] Add frontend tests for valid, invalid, and declined phone complaint states in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 4

- [X] T054 [US4] Implement Egyptian phone normalization and validation in `backend/app/Services/Chat/FollowUpPhoneService.php`
- [X] T055 [US4] Implement phone accepted, invalid retry, saved, and declined transitions in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T056 [US4] Wire phone validation during `awaiting_phone` and `invalid_phone_retry` complaint stages in `backend/app/Http/Controllers/ChatController.php`
- [X] T057 [US4] Add invalid-phone retry, saved confirmation, and declined-contact reply paths in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T058 [US4] Persist `phone_invalid`, `phone_accepted`, `phone_declined`, and `saved` complaint events without storing malformed phone as confirmed in `backend/app/Services/Chat/ChatLogService.php`
- [X] T059 [US4] Render normalized phone confirmation and privacy-safe declined/invalid phone states in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 4 completes the actionable complaint capture journey.

---

## Phase 7: User Story 5 - Preserve Complaint State Through Failures (Priority: P5)

**Goal**: Preserve complaint progress through NLU, reply-generation, and fallback failures without confirming invalid data or returning to search in the same turn.

**Independent Test**: Simulate failures during complaint acknowledgement, issue capture, invalid-phone retry, and final confirmation; verify fallback text is friendly, stage state is preserved, malformed phone remains unconfirmed, and the next turn resumes correctly.

### Tests for User Story 5

- [X] T060 [P] [US5] Add unit tests for fallback-pending preservation across complaint stages in `backend/tests/Unit/Chat/ComplaintStateServiceTest.php`
- [X] T061 [P] [US5] Add feature tests for complaint reply fallback preserving acknowledgement and issue state in `backend/tests/Feature/Chat/ComplaintFallbackFlowTest.php`
- [X] T062 [P] [US5] Add feature tests for invalid-phone fallback not storing malformed contact in `backend/tests/Feature/Chat/ComplaintPhoneRetryFlowTest.php`
- [X] T063 [P] [US5] Add frontend tests for complaint fallback display and resumed complaint state in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 5

- [X] T064 [US5] Implement fallback-pending complaint state preservation in `backend/app/Services/Chat/ComplaintStateService.php`
- [X] T065 [US5] Ensure complaint state is persisted before reply composition fallback handling in `backend/app/Http/Controllers/ChatController.php`
- [X] T066 [US5] Add complaint-specific friendly fallback replies in `backend/app/Services/Chat/IntentDetectionService.php`
- [X] T067 [US5] Persist `fallback` complaint events without overwriting issue summary or phone state in `backend/app/Services/Chat/ChatLogService.php`
- [X] T068 [US5] Render complaint fallback and resumed-stage indicators safely in `frontend/src/app/chatbot/message-list/message-list.component.ts`

**Checkpoint**: User Story 5 protects complaint continuity during provider or reply failures.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Validation, documentation, privacy hardening, and quickstart coverage across all complaint journeys.

- [X] T069 [P] Update Phase 5 complaint handling documentation and event glossary in `backend/docs/chat-phase-5-complaint-handling.md`
- [X] T070 [P] Add Phase 5 quickstart scenario coverage notes to `specs/006-phase-5-complaint-handling/quickstart.md`
- [X] T071 [P] Review and align complaint response fields between `backend/app/Http/Controllers/ChatController.php` and `frontend/src/app/chatbot/chat.types.ts`
- [X] T072 Harden untrusted complaint text handling in prompts and markdown rendering across `backend/app/Services/Chat/OpenRouterService.php` and `frontend/src/app/chatbot/safe-chat-markdown.pipe.ts`
- [X] T073 Validate complaint flow never leaks seller phone or complaint phone in unrelated responses in `backend/tests/Feature/Chat/ComplaintDetectionFlowTest.php`
- [X] T074 Run backend targeted complaint validation with `php artisan test --filter=Complaint` from `backend/`
- [X] T075 Run backend contract and ownership validation with `php artisan test --filter=ChatContract|SessionOwnership` from `backend/`
- [X] T076 Run frontend chat complaint validation with `npm test -- --include chat` from `frontend/`
- [X] T077 Verify all Phase 5 scenarios in `specs/006-phase-5-complaint-handling/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all complaint user stories.
- **User Stories (Phase 3+)**: Depend on Foundational completion.
- **Polish (Phase 8)**: Depends on all desired complaint stories being complete.

### User Story Dependencies

- **User Story 1 (P1)**: MVP; can start after Foundational and has no dependency on later stories.
- **User Story 2 (P2)**: Can start after Foundational; should preserve US1 hard complaint precedence while avoiding false positives.
- **User Story 3 (P3)**: Best after US1 because it continues the full complaint flow started by US1.
- **User Story 4 (P4)**: Depends on US3 issue capture because phone collection starts after an issue summary exists.
- **User Story 5 (P5)**: Can be implemented after shared complaint state exists, but validation benefits from US1-US4 stage transitions.

### Within Each User Story

- Tests are listed before implementation and should fail before code changes.
- Complaint state and phone value services before controller wiring.
- Controller gating before frontend rendering validation.
- Logging/event persistence before story checkpoint validation.
- Story complete before moving to the next priority unless work is explicitly parallelized by file ownership.

---

## Parallel Opportunities

- Setup tasks T002, T003, T004, T005, and T006 can run in parallel after T001 is understood.
- Foundational contract, frontend type, backend service, validation, logging, and controller tasks can be split by file after shared state names are agreed.
- Test tasks within each user story marked [P] can run in parallel.
- Frontend rendering tasks can run in parallel with backend service tasks once `complaint_case` response shape is stable.
- US2 soft check-in work can proceed in parallel with US3 issue capture after US1 complaint precedence is defined.

---

## Parallel Example: User Story 1

```bash
Task: "T018 [P] [US1] Add unit tests for hard complaint signals and repeated failed-search thresholds in backend/tests/Unit/Chat/ComplaintSignalServiceTest.php"
Task: "T019 [P] [US1] Add unit tests for complaint stage initialization and event creation in backend/tests/Unit/Chat/ComplaintStateServiceTest.php"
Task: "T020 [P] [US1] Add feature tests for explicit complaint, frustration, and repeated failed-search routing in backend/tests/Feature/Chat/ComplaintDetectionFlowTest.php"
Task: "T022 [P] [US1] Add frontend tests for complaint acknowledgement rendering in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 2

```bash
Task: "T029 [P] [US2] Add unit tests for soft repeat and slot-correction signals in backend/tests/Unit/Chat/ComplaintSignalServiceTest.php"
Task: "T030 [P] [US2] Add feature tests for soft check-in without complaint escalation in backend/tests/Feature/Chat/SoftComplaintCheckInFlowTest.php"
Task: "T031 [P] [US2] Add feature tests for buyer acceptance of gentle offer entering full complaint flow in backend/tests/Feature/Chat/SoftComplaintAcceptanceFlowTest.php"
Task: "T032 [P] [US2] Add frontend tests for one-time gentle follow-up offer rendering in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 3

```bash
Task: "T039 [P] [US3] Add unit tests for issue summary extraction, empty description handling, and untrusted text treatment in backend/tests/Unit/Chat/ComplaintStateServiceTest.php"
Task: "T040 [P] [US3] Add feature tests for issue capture and phone request progression in backend/tests/Feature/Chat/ComplaintIssueCaptureFlowTest.php"
Task: "T041 [P] [US3] Add feature tests for unclear complaint description clarification in backend/tests/Feature/Chat/ComplaintIssueClarificationFlowTest.php"
Task: "T042 [P] [US3] Add frontend tests for issue-captured and phone-request prompt display in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 4

```bash
Task: "T049 [P] [US4] Add unit tests for Egyptian local, +20, 0020, invalid, and declined phone cases in backend/tests/Unit/Chat/FollowUpPhoneServiceTest.php"
Task: "T050 [P] [US4] Add unit tests for phone attempt counts, retry transition, saved transition, and declined transition in backend/tests/Unit/Chat/ComplaintStateServiceTest.php"
Task: "T051 [P] [US4] Add feature tests for valid phone normalization and complaint saving in backend/tests/Feature/Chat/ComplaintPhoneFlowTest.php"
Task: "T053 [P] [US4] Add frontend tests for valid, invalid, and declined phone complaint states in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

## Parallel Example: User Story 5

```bash
Task: "T060 [P] [US5] Add unit tests for fallback-pending preservation across complaint stages in backend/tests/Unit/Chat/ComplaintStateServiceTest.php"
Task: "T061 [P] [US5] Add feature tests for complaint reply fallback preserving acknowledgement and issue state in backend/tests/Feature/Chat/ComplaintFallbackFlowTest.php"
Task: "T062 [P] [US5] Add feature tests for invalid-phone fallback not storing malformed contact in backend/tests/Feature/Chat/ComplaintPhoneRetryFlowTest.php"
Task: "T063 [P] [US5] Add frontend tests for complaint fallback display and resumed complaint state in frontend/src/app/chatbot/message-list/message-list.component.spec.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational complaint state, phone helper, contract, and logging primitives.
3. Complete Phase 3: User Story 1.
4. Stop and validate explicit complaint, frustration, repeated failed search, and blocked normal-flow behavior independently.
5. Demo the complaint acknowledgement and issue-request flow before adding soft check-ins or phone collection.

### Incremental Delivery

1. Setup + Foundational establish response shape, state machine, phone helper, logging, and controller gates.
2. Add US1 for full complaint entry MVP.
3. Add US2 for soft check-ins without false-positive complaint escalation.
4. Add US3 for issue summary capture and phone request.
5. Add US4 for phone normalization, retry, saved, and declined-contact outcomes.
6. Add US5 for failure preservation across complaint stages.

### Parallel Team Strategy

1. Team completes Setup + Foundational together.
2. Backend developer A owns US1 complaint routing and controller gating.
3. Backend developer B owns US3-US4 complaint state and phone transitions.
4. Frontend developer owns complaint response typing and message-list rendering.
5. Reliability-focused developer owns US5 fallback preservation and quickstart validation.

---

## Notes

- [P] tasks use different files or can proceed without depending on incomplete implementation tasks.
- [US1] through [US5] map directly to the five user stories in `spec.md`.
- Every implementation task names the exact file to create or modify.
- Tests should fail before implementing each story.
- Complaint turns with hard signals must take precedence over search, property-detail, photo, and seller-contact behavior.
- Malformed phone numbers must never be stored as confirmed contact.
- Complaint text, prior messages, and listing content are untrusted and must not become operating instructions.
