# Tasks: Phase 4 Property Details

**Input**: Design documents from `/specs/005-phase-4-property-details/`

**Prerequisites**: `plan.md` (required), `spec.md` (required for user stories), `research.md`, `data-model.md`, `contracts/`

**Tests**: Included because the spec defines explicit independent test criteria and quickstart validation scenarios for each story.

**Organization**: Tasks are grouped by user story so each story can be implemented and validated independently.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (`US1`, `US2`, `US3`, etc.)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure for Phase 4 property details

- [X] T001 Create the Phase 4 documentation scaffold and ensure `specs/005-phase-4-property-details/contracts/` exists for the chat contract
- [X] T002 Confirm the Phase 4 plan reference in `AGENTS.md` points to `specs/005-phase-4-property-details/plan.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that must be complete before any user story can be implemented

**Critical**: No user story work can begin until this phase is complete

- [X] T003 Add Phase 4 chat response fields and property-detail types in `frontend/src/app/chatbot/chat.types.ts`
- [X] T004 Update the chat API contract for property references, detail payloads, gallery payloads, and seller contact in `specs/005-phase-4-property-details/contracts/chat-api.yaml`
- [X] T005 Extend the chat controller response shape to carry property reference, detail, gallery, and contact fields in `backend/app/Http/Controllers/ChatController.php`
- [X] T006 Add reviewable detail outcome persistence fields to chat logging in `backend/app/Services/Chat/ChatLogService.php`
- [X] T007 Introduce property reference, detail, gallery, and contact service boundaries in `backend/app/Services/Chat/PropertyReferenceResolver.php`, `backend/app/Services/Chat/PropertyDetailService.php`, `backend/app/Services/Chat/PropertyGalleryService.php`, and `backend/app/Services/Chat/SellerContactService.php`
- [X] T008 Update the chat test factory and shared fixtures for current-visible-page, property-page-context, gallery, and contact scenarios in `backend/tests/Support/ChatTestFactory.php`

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Answer Details About a Shown Property (Priority: P1) MVP

**Goal**: Resolve a current visible property or valid page-context property and answer only from that property's available facts.

**Independent Test**: A buyer can ask about "the first one" or a partial title from the current visible page and get a grounded answer using only that property's data, with missing fields acknowledged and language preserved.

### Tests for User Story 1

- [X] T009 [P] [US1] Add contract coverage for property-detail responses and resolved property fields in `backend/tests/Feature/Chat/ChatContractTest.php`
- [X] T010 [P] [US1] Add unit coverage for current-page positional and title-based property reference resolution in `backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php`
- [X] T011 [P] [US1] Add feature coverage for detail answers against the current visible result page in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T012 [P] [US1] Add unit coverage for missing-field handling and no-inference detail payloads in `backend/tests/Unit/Chat/ChatMemoryMergeTest.php`

### Implementation for User Story 1

- [X] T013 [P] [US1] Implement deterministic property reference resolution against the current visible result page and valid page context in `backend/app/Services/Chat/PropertyReferenceResolver.php`
- [X] T014 [P] [US1] Implement grounded property detail assembly with missing-field reporting in `backend/app/Services/Chat/PropertyDetailService.php`
- [X] T015 [US1] Wire property-detail resolution into the chat controller flow in `backend/app/Http/Controllers/ChatController.php`
- [X] T016 [P] [US1] Render property-detail payloads, unavailable-field states, and safe property links in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [X] T017 [P] [US1] Update chat response handling for detail replies in `frontend/src/app/chatbot/chat.service.ts`
- [X] T018 [US1] Preserve buyer language/register and safe untrusted listing text in `backend/app/Services/Chat/IntentDetectionService.php` and `backend/app/Services/Chat/OpenRouterService.php`
- [X] T019 [US1] Persist detail-answer outcomes and unresolved-reference outcomes in `backend/app/Services/Chat/ChatLogService.php`

**Checkpoint**: User Story 1 should now be independently functional and demo-ready.

---

## Phase 4: User Story 2 - Clarify Ambiguous Property References (Priority: P2)

**Goal**: Ask for clarification when a property reference does not resolve to exactly one visible property.

**Independent Test**: An ambiguous request such as "tell me about that apartment" triggers a clarification prompt and current visible options instead of answering about the wrong listing.

### Tests for User Story 2

- [X] T020 [P] [US2] Add feature coverage for ambiguous and missing property references in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T021 [P] [US2] Add unit coverage for stale-page and ambiguous-reference handling in `backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php`
- [X] T022 [P] [US2] Add frontend coverage for clarification prompts and numbered options in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 2

- [X] T023 [P] [US2] Implement clarification candidate building and stale-page rejection in `backend/app/Services/Chat/PropertyReferenceResolver.php`
- [X] T024 [US2] Extend the controller to return clarification state and current visible options when a reference is unresolved in `backend/app/Http/Controllers/ChatController.php`
- [X] T025 [P] [US2] Render clarification prompts and numbered current-property options in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [X] T026 [US2] Persist unresolved-reference outcomes and clarification prompts in `backend/app/Services/Chat/ChatLogService.php`

**Checkpoint**: User Story 2 should now work independently of later photo/contact stories.

---

## Phase 5: User Story 3 - Show Photos for a Resolved Property (Priority: P3)

**Goal**: Return only the resolved property's ordered image gallery when the buyer asks to see photos.

**Independent Test**: Asking for photos on a resolved property shows that property's ordered gallery, while a property with no images receives a clear no-photos response.

### Tests for User Story 3

- [X] T027 [P] [US3] Add feature coverage for gallery payloads and no-photo responses in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T028 [P] [US3] Add unit coverage for property gallery ordering and image filtering in `backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php`
- [X] T029 [P] [US3] Add frontend coverage for gallery rendering without raw path leakage in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 3

- [X] T030 [P] [US3] Implement ordered property gallery retrieval for one resolved property in `backend/app/Services/Chat/PropertyGalleryService.php`
- [X] T031 [US3] Wire photo-request intent handling into the chat controller flow in `backend/app/Http/Controllers/ChatController.php`
- [X] T032 [P] [US3] Render gallery payloads and no-photo fallback states in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [X] T033 [US3] Persist photo-gallery and no-photo outcome events in `backend/app/Services/Chat/ChatLogService.php`

**Checkpoint**: User Story 3 should now be independently demoable without seller-contact support.

---

## Phase 6: User Story 4 - Provide Seller Contact on Explicit Single-Property Request (Priority: P4)

**Goal**: Return seller phone only when the buyer explicitly asks for contact for one resolved active property.

**Independent Test**: An explicit single-property contact request returns the correct phone or a clear unavailable-contact response, while normal detail replies never include seller phone numbers.

### Tests for User Story 4

- [X] T034 [P] [US4] Add feature coverage for explicit seller-contact requests and inactive-property denial in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T035 [P] [US4] Add unit coverage for contact gating, active-listing checks, and reusable-state exclusion in `backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php`
- [X] T036 [P] [US4] Add frontend coverage for explicit contact display and privacy-safe rendering in `frontend/src/app/chatbot/message-list/message-list.component.spec.ts`

### Implementation for User Story 4

- [X] T037 [P] [US4] Implement explicit seller-contact lookup for one resolved active property in `backend/app/Services/Chat/SellerContactService.php`
- [X] T038 [US4] Wire seller-contact intent handling into the chat controller flow in `backend/app/Http/Controllers/ChatController.php`
- [X] T039 [P] [US4] Render explicit contact responses without exposing seller phone in normal replies in `frontend/src/app/chatbot/message-list/message-list.component.ts`
- [X] T040 [US4] Persist contact-returned and contact-unavailable outcome events in `backend/app/Services/Chat/ChatLogService.php`

**Checkpoint**: User Story 4 should now be independently testable and must not leak contact data into earlier story flows.

---

## Phase 7: User Story 5 - Start Details From a Property Page Context (Priority: P5)

**Goal**: Allow the first chat turn to inherit a valid property page context before any search results have been shown.

**Independent Test**: Opening chat with a valid `context_property_id` lets the first detail question resolve against that property, then later visible search results supersede the page context.

### Tests for User Story 5

- [X] T041 [P] [US5] Add feature coverage for valid and invalid property-page context startup in `backend/tests/Feature/Chat/PropertyReferenceFlowTest.php`
- [X] T042 [P] [US5] Add unit coverage for property-page context validity and supersession in `backend/tests/Unit/Chat/NewSearchResetTest.php`
- [X] T043 [P] [US5] Add frontend coverage for first-turn context-scoped responses in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.spec.ts`

### Implementation for User Story 5

- [X] T044 [P] [US5] Extend chat startup to accept and validate property-page context in `backend/app/Http/Controllers/ChatController.php`
- [X] T045 [US5] Implement property-page context validation and supersession in `backend/app/Services/Chat/SearchResultStateService.php`
- [X] T046 [P] [US5] Render first-turn property-context responses and subsequent superseded state in `frontend/src/app/chatbot/chatbot-widget/chatbot-widget.component.ts`
- [X] T047 [US5] Persist page-context-valid, page-context-invalid, and page-context-superseded outcomes in `backend/app/Services/Chat/ChatLogService.php`

**Checkpoint**: User Story 5 should now be independently demoable and consistent with search-context precedence.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [X] T048 [P] Update quickstart validation steps for property details, galleries, seller contact, and property-page context in `specs/005-phase-4-property-details/quickstart.md`
- [X] T049 [P] Review all Phase 4 chat response types and contract fields for consistency between `backend/app/Http/Controllers/ChatController.php` and `frontend/src/app/chatbot/chat.types.ts`
- [X] T050 Harden privacy and prompt-injection handling for seller-supplied text and gallery metadata across `backend/app/Services/Chat/` and `frontend/src/app/chatbot/`
- [X] T051 Validate latency, fallback, and logging coverage for detail outcomes in `backend/tests/Feature/Chat/` and `backend/tests/Unit/Chat/`
- [X] T052 Verify the Phase 4 quickstart scenarios in `specs/005-phase-4-property-details/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - blocks all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel if staffed
  - Or sequentially in priority order (`P1` -> `P2` -> `P3` -> `P4` -> `P5`)
- **Polish (Final Phase)**: Depends on the desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational phase completion - no dependencies on later stories
- **User Story 2 (P2)**: Can start after Foundational phase completion - builds on the same reference resolver but remains independently testable
- **User Story 3 (P3)**: Can start after Foundational phase completion - uses the resolved property path from US1/US2 but stays independently testable
- **User Story 4 (P4)**: Can start after Foundational phase completion - reuses the same resolved property path and must preserve privacy boundaries
- **User Story 5 (P5)**: Can start after Foundational phase completion - depends on valid page-context validation but not on later stories

### Within Each User Story

- Tests are written before or alongside implementation for the story
- Backend services before controller wiring
- Controller wiring before frontend response rendering
- Detail, photo, and contact flows remain isolated so one story does not leak state into another
- Story complete before moving to the next priority

### Parallel Opportunities

- Setup tasks marked `[P]` can run in parallel when they touch different files
- Foundational tasks marked `[P]` can run in parallel
- Once Foundational phase completes, all user stories can start in parallel if team capacity allows
- Tests for a user story marked `[P]` can run in parallel
- Service and frontend tasks for a user story marked `[P]` can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all User Story 1 tests together:
Task: "Add contract coverage for property-detail responses and resolved property fields in backend/tests/Feature/Chat/ChatContractTest.php"
Task: "Add unit coverage for current-page positional and title-based property reference resolution in backend/tests/Unit/Chat/PropertyReferenceResolutionTest.php"
Task: "Add feature coverage for detail answers against the current visible result page in backend/tests/Feature/Chat/PropertyReferenceFlowTest.php"

# Launch the backend and frontend implementation work together:
Task: "Implement deterministic property reference resolution against the current visible result page and valid page context in backend/app/Services/Chat/PropertyReferenceResolver.php"
Task: "Render property-detail payloads, unavailable-field states, and safe property links in frontend/src/app/chatbot/message-list/message-list.component.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. Stop and validate User Story 1 independently
5. Demo only after grounded detail answers, missing-field handling, and language preservation work

### Incremental Delivery

1. Complete Setup + Foundational - foundation ready
2. Add User Story 1 - test independently - demo the MVP
3. Add User Story 2 - test independently - demo clarification behavior
4. Add User Story 3 - test independently - demo photo gallery flow
5. Add User Story 4 - test independently - demo explicit seller contact
6. Add User Story 5 - test independently - demo first-turn page context

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
   - Developer D: User Story 4
   - Developer E: User Story 5
3. Stories complete and integrate independently

---

## Notes

- `[P]` tasks = different files, no dependencies
- `[Story]` label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing when writing tests first
- Keep seller contact isolated to explicit single-property contact responses
- Keep positional references tied only to the current visible page
- Keep property-page context valid only until superseded by later search state
