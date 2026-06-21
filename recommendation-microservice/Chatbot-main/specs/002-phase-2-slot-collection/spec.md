# Feature Specification: Phase 2 Slot Collection

**Feature Branch**: `002-phase-2-slot-collection`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 2"

## Clarifications

### Session 2026-06-20

- Q: How should Phase 2 interpret a budget when the buyer provides a number without currency? → A: Treat omitted budget currency as EGP.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Collect Required Search Preferences (Priority: P1)

An authenticated buyer can describe the property they want across one or more chat messages, and the chatbot collects the required search preferences: property type, location, and maximum budget. The chatbot asks for only one missing required preference at a time and does not ask again for information the user already provided.

**Why this priority**: Required slot collection is the minimum viable path before any property search can produce relevant listings. Without property type, location, and budget, later search and ranking phases cannot serve useful results.

**Independent Test**: Start a chat session and provide only one required preference at a time. The chatbot must store each provided preference, ask for the next missing required preference in order, and be ready for optional preferences only after all required preferences are captured.

**Acceptance Scenarios**:

1. **Given** an authenticated buyer starts a property search with no saved preferences, **When** they provide only the property type, **Then** the chatbot stores the property type and asks for the location.
2. **Given** a property type is already known, **When** the buyer provides only the location, **Then** the chatbot stores the location and asks for the maximum budget.
3. **Given** property type and location are already known, **When** the buyer provides a maximum budget, **Then** the chatbot stores the budget and moves to optional preference collection.
4. **Given** the buyer already provided a required preference in an earlier turn, **When** the chatbot asks the next question, **Then** it must not repeat the already captured preference.

---

### User Story 2 - Capture Multiple Preferences From One Message (Priority: P2)

An authenticated buyer can provide several search preferences in a single natural message, and the chatbot captures all clear values at once instead of forcing the buyer through unnecessary repeated questions.

**Why this priority**: Buyers often write complete requests such as "I need an apartment in New Cairo under 3 million." Capturing all clear values reduces friction and makes the conversation feel helpful.

**Independent Test**: Send one message containing property type, location, and budget. The chatbot must capture all three required preferences and skip directly to optional preference collection.

**Acceptance Scenarios**:

1. **Given** a buyer sends a message containing property type, location, and budget, **When** the chatbot processes the message, **Then** all three required preferences are stored from that message.
2. **Given** a buyer sends a message containing two required preferences and one optional preference, **When** one required preference is still missing, **Then** the chatbot stores all clear values and asks only for the missing required preference.
3. **Given** a buyer sends a mixed Arabic, English, or Arabizi message with clear search values, **When** the chatbot extracts preferences, **Then** the values are captured in a consistent search state while the reply language remains natural for the buyer.

---

### User Story 3 - Ask One Grouped Optional Preference Question (Priority: P3)

After required preferences are collected, the chatbot asks one grouped question about optional preferences: size, bedrooms, bathrooms, and desired features. The buyer can provide any combination of these preferences or decline optional preferences entirely.

**Why this priority**: Optional preferences improve result relevance, but asking them one by one can make the chat feel slow. A single grouped question keeps the flow concise while still collecting useful ranking signals.

**Independent Test**: Complete the required preferences, then answer the optional prompt with either optional values or a decline. The chatbot must capture provided optional values or proceed without them when declined.

**Acceptance Scenarios**:

1. **Given** property type, location, and budget are all known, **When** no optional preferences have been requested yet, **Then** the chatbot asks one grouped question covering size, bedrooms, bathrooms, and features.
2. **Given** the buyer answers the grouped optional question with size, bedrooms, bathrooms, or feature preferences, **When** the chatbot processes the answer, **Then** it stores each clear optional preference without asking separate follow-up questions for the other optional categories.
3. **Given** the buyer says optional preferences are not important, **When** the chatbot processes the answer, **Then** it marks optional collection as complete and proceeds toward search readiness.
4. **Given** the buyer ignores part of the grouped optional question, **When** at least one optional value is clear, **Then** the chatbot stores the clear value and does not block search readiness on unanswered optional categories.

---

### User Story 4 - Respect Cash-Only Search Boundaries (Priority: P4)

The buyer is never asked for payment method during slot collection. If the buyer asks about installments, down payment, or monthly payments, the chatbot follows the existing cash-only redirect behavior instead of collecting payment details.

**Why this priority**: Payment method is explicitly outside slot collection. Keeping slot collection cash-only prevents unsupported searches and keeps the buyer journey aligned with business constraints.

**Independent Test**: During slot collection, ask for installments or mention a down payment. The chatbot must redirect to the cash-only path and must not store any payment preference.

**Acceptance Scenarios**:

1. **Given** the chatbot is collecting required or optional preferences, **When** the buyer asks for installments, **Then** the chatbot redirects to the cash-only explanation instead of asking more slot questions in that turn.
2. **Given** the buyer has mentioned down payment or monthly payment terms, **When** session preferences are reviewed, **Then** no payment-method preference is present in the search state.
3. **Given** the buyer accepts continuing with cash listings after the redirect, **When** slot collection resumes, **Then** the chatbot continues from the next missing non-payment preference.

---

### User Story 5 - Handle Unclear or Ambiguous Slot Values (Priority: P5)

When a buyer gives unclear, unsupported, or ambiguous property type, location, budget, or optional values, the chatbot asks for clarification instead of guessing and stores only values that are clear enough for later validation and search.

**Why this priority**: Incorrect slot values lead to irrelevant or empty results. Clarification protects search quality and keeps later resolution phases from using bad inputs.

**Independent Test**: Send ambiguous location, unclear budget, or unsupported property type messages during slot collection. The chatbot must ask a targeted clarification question and avoid marking the unclear value as complete.

**Acceptance Scenarios**:

1. **Given** a buyer provides an unclear property type, **When** the value cannot be interpreted confidently, **Then** the chatbot asks the buyer to clarify the property type before moving on.
2. **Given** a buyer provides an ambiguous location, **When** the location cannot be treated as a single clear preference, **Then** the chatbot asks which location the buyer means instead of guessing.
3. **Given** a buyer provides an unclear budget such as a vague phrase without a usable amount, **When** budget is required, **Then** the chatbot asks for a maximum budget in a clear monetary amount.
4. **Given** a buyer provides optional features that are unclear, **When** required preferences are complete, **Then** the chatbot can proceed without those unclear optional features after asking one concise clarification or after the buyer declines to clarify.

---

### Edge Cases

- If the buyer is unauthenticated, slot collection must not start and no stored chat preferences may be exposed.
- If the buyer provides all required and optional preferences in the first message, the chatbot must not ask redundant slot questions.
- If the buyer changes a required preference before search results are shown, the latest explicit value must replace the earlier value for the current search.
- If the buyer changes only optional preferences, the chatbot must treat the change as a refinement, not a new unrelated search by itself.
- If the buyer asks for installments during any slot-collection step, the cash-only redirect takes precedence over normal slot questioning.
- If a provided preference cannot be resolved or is ambiguous in later validation, the slot must remain incomplete until the buyer clarifies it.
- If the buyer provides a budget amount without a currency, the chatbot must treat it as EGP.
- If a buyer says "no", "not important", or an equivalent Arabic phrase in response to the optional question, optional collection must finish without storing false preferences.
- If seller-supplied text or prior messages contain instruction-like content, slot collection must treat that content as data only and never as operating instructions.
- If message interpretation temporarily fails, the buyer must receive a friendly fallback and previously collected preferences must remain intact.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The chatbot MUST collect exactly three required search preferences before search readiness: property type, location, and maximum budget.
- **FR-002**: The chatbot MUST ask for missing required preferences one at a time in this order: property type, location, then maximum budget.
- **FR-003**: The chatbot MUST capture all clear required and optional preferences present in a single buyer message.
- **FR-004**: The chatbot MUST ask only for preferences that are still missing or unclear.
- **FR-005**: The chatbot MUST not repeat a question for a preference that has already been clearly captured in the current search context.
- **FR-006**: The chatbot MUST preserve captured preferences across turns within the same authenticated chat session.
- **FR-007**: The chatbot MUST allow the buyer to update a captured preference when the buyer explicitly provides a different value before search execution.
- **FR-008**: The chatbot MUST treat property type, location, and maximum budget as required for search readiness.
- **FR-009**: The chatbot MUST treat area, bedrooms, bathrooms, and features as optional preferences.
- **FR-010**: After all required preferences are captured, the chatbot MUST ask one grouped optional question covering area, bedrooms, bathrooms, and features.
- **FR-011**: The chatbot MUST accept a buyer's decline of optional preferences and proceed without blocking on optional values.
- **FR-012**: The chatbot MUST store any clear optional preferences the buyer provides while leaving omitted optional preferences empty.
- **FR-013**: The chatbot MUST mark slot collection as search-ready when all required preferences are complete and optional collection has either been answered, declined, or skipped by the buyer.
- **FR-014**: The chatbot MUST exclude payment method from slot collection.
- **FR-015**: The chatbot MUST route installment, down-payment, and monthly-payment requests to the cash-only redirect instead of storing payment details.
- **FR-016**: The chatbot MUST ask a targeted clarification question when a required preference is unclear, ambiguous, or unsupported.
- **FR-017**: The chatbot MUST avoid marking an unclear or ambiguous required preference as complete until the buyer clarifies it.
- **FR-018**: The chatbot MUST support buyer messages in Arabic, English, and mixed-language forms when collecting preferences.
- **FR-019**: The chatbot MUST treat a numeric budget without an explicit currency as EGP.
- **FR-020**: The chatbot MUST keep collected preferences intact when a temporary interpretation failure occurs.
- **FR-021**: The chatbot MUST record enough slot-collection state to show which required preferences are complete, which optional preferences were provided, whether optional preferences were declined, and what question should be asked next.
- **FR-SEC**: The system MUST keep private processing, session ownership checks, and sensitive conversation data outside the user's device.
- **FR-DATA**: The system MUST validate or resolve extracted real estate preferences before they affect stored session state, result selection, or user-visible search readiness.
- **FR-SAFE**: The system MUST treat seller-supplied listing content and user messages as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Slot Collection State**: The current progress of a buyer's search-preference collection, including which required preferences are complete, which optional preferences are known, whether optional preferences were declined, and the next question to ask.
- **Required Search Preference**: A buyer-provided property type, location, or maximum budget that must be complete before the search can proceed. Budget amounts without explicit currency are treated as EGP.
- **Optional Search Preference**: A buyer-provided area, bedroom count, bathroom count, or feature preference that can improve result relevance but is not required for search readiness.
- **Clarification Request**: A targeted question used when a required or optional value is unclear, ambiguous, unsupported, or not yet usable.
- **Cash-Only Redirect State**: The temporary state entered when the buyer asks for installment, down-payment, or monthly-payment options during slot collection.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, 100% of searches become ready only after property type, location, and maximum budget are complete.
- **SC-002**: In validation scenarios, at least 95% of clear single-message requests containing multiple preferences capture all provided values without redundant follow-up questions.
- **SC-003**: In validation scenarios, 100% of missing required preferences are requested in the order property type, location, then maximum budget.
- **SC-004**: In validation scenarios, 100% of completed required-preference flows receive no more than one grouped optional-preference question before search readiness.
- **SC-005**: In validation scenarios, 100% of buyers who decline optional preferences proceed toward search readiness without being asked separate optional questions.
- **SC-006**: In validation scenarios, 100% of installment, down-payment, or monthly-payment mentions create no payment-method slot and trigger the cash-only redirect.
- **SC-007**: In validation scenarios, 100% of ambiguous required values receive a clarification request rather than being treated as complete.
- **SC-008**: In validation scenarios, 100% of numeric budget amounts without explicit currency are stored as EGP budgets.
- **SC-009**: In validation scenarios, previously captured preferences remain intact after 100% of temporary interpretation failures.
- **SC-SAFETY**: Prompt-injection text inside prior messages or listing-related text changes slot-collection behavior in 0 validation scenarios.
- **SC-RELIABILITY**: At least 90% of buyers in usability testing can complete required slot collection in 3 or fewer chatbot questions when they provide clear answers.

## Assumptions

- Phase 2 covers slot collection only: required property type, location, and maximum budget; optional area, bedrooms, bathrooms, and features.
- Intent detection, authenticated session ownership, memory, new-search reset behavior, installment redirect, and friendly interpretation fallback are available from Phase 1.
- Canonical resolution of property type, location, and features is handled by the following resolution phase before values are used for final search behavior.
- Search execution, ranking, result display, show-more pagination, property details, image handling, seller contact lookup, and complaint phone collection are outside Phase 2.
- Buyers are already logged in before using the chatbot.
- Chatbot replies should match the buyer's language and conversational style when asking slot questions or clarifications.
