# Feature Specification: Phase 1 Intent Detection and Memory

**Feature Branch**: `001-phase-1-intent-memory`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create a specification for the phase 1"

## Clarifications

### Session 2026-06-20

- Q: How much conversation history must remain available for intent and reference handling? → A: Last 10 conversation turns plus structured session state.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Continue a Property Search Conversation (Priority: P1)

An authenticated buyer can send natural Arabic, English, or mixed-language real estate messages,
and the chatbot understands whether the user is searching, asking a general question, asking about
a shown property, complaining, or unclear. The chatbot remembers already provided preferences so
the user does not have to repeat them on every turn.

**Why this priority**: Intent detection and memory are the foundation for every later chatbot
phase. Without this journey, slot collection, search, property details, and complaints cannot work
reliably.

**Independent Test**: Start a new authenticated chat session, send a search message with a property
type and location, then send a budget in a later message. The chatbot must preserve the earlier
preferences, classify both turns correctly, and ask only for the next missing information.

**Acceptance Scenarios**:

1. **Given** an authenticated user starts a new chat, **When** they say they want an apartment in Cairo, **Then** the chatbot classifies the turn as a property search and stores the property type and location preferences for the session.
2. **Given** a session already has a stored property type and location, **When** the user provides only a budget, **Then** the chatbot keeps the previous preferences and adds the budget instead of clearing known values.
3. **Given** a user sends a greeting or unrelated small talk, **When** no real estate intent is present, **Then** the chatbot classifies the turn as chitchat and responds without changing search preferences.
4. **Given** a user message cannot be understood confidently, **When** no intent can be determined, **Then** the chatbot asks a clarifying question rather than guessing.

---

### User Story 2 - Refer to a Previously Shown Property (Priority: P2)

After results have been shown, a buyer can ask follow-up questions using references like "the first
one", "the second property", or Arabic equivalents, and the chatbot maps that phrase to the correct
property from the current shown list.

**Why this priority**: Real estate conversations naturally continue after results are shown. Users
expect to ask about "the first one" instead of repeating property titles or identifiers.

**Independent Test**: Seed a session with a numbered list of shown properties, then ask a question
about "the first one". The chatbot must identify the matching property and keep the answer scoped to
that property only.

**Acceptance Scenarios**:

1. **Given** three properties have been shown in order, **When** the user asks about "the first one", **Then** the chatbot resolves the reference to the first shown property.
2. **Given** a user refers to a property by a partial title that matches one shown result, **When** they ask a detail question, **Then** the chatbot resolves the reference to that shown property.
3. **Given** the chatbot cannot determine which property the user means, **When** the user asks a follow-up question, **Then** the chatbot asks the user to choose from the currently shown properties instead of guessing.

---

### User Story 3 - Redirect Installment Requests (Priority: P3)

A buyer who asks for installments, down payments, or monthly payment options is told that
installments are not currently supported and is invited to continue with cash listings instead.

**Why this priority**: Installment handling is a clear business rule. The chatbot must not collect
unsupported payment preferences or lead users into invalid searches.

**Independent Test**: Send a message asking for an apartment by installment. The chatbot must detect
the installment intent, avoid collecting installment details, and ask whether the user wants to
continue with cash payment.

**Acceptance Scenarios**:

1. **Given** a user asks for installment payment, **When** the chatbot evaluates the turn, **Then** it classifies the turn as an installment redirect and does not store installment-specific search preferences.
2. **Given** the chatbot has redirected an installment request, **When** the user agrees to continue with cash, **Then** the chatbot resumes the normal property search conversation.
3. **Given** the chatbot has redirected an installment request, **When** the user declines cash payment, **Then** the chatbot ends that search path gracefully.

---

### User Story 4 - Detect Complaints Without Interrupting Normal Exploration (Priority: P4)

A frustrated or explicitly complaining user is routed toward human follow-up, while normal repeated
questions or changing preferences trigger only a gentle check-in instead of an immediate complaint
flow.

**Why this priority**: Complaint handling protects user trust, but false positives can derail normal
property exploration. Phase 1 must distinguish hard complaint signals from softer signs that the
conversation may be getting stuck.

**Independent Test**: Send one explicit complaint message and one normal preference-changing
conversation. The explicit complaint must trigger complaint handling, while normal exploration must
continue with at most a gentle offer for help.

**Acceptance Scenarios**:

1. **Given** a user explicitly says they want to complain, **When** the chatbot evaluates the turn, **Then** the session is marked for complaint handling immediately.
2. **Given** a user uses clear frustration language, **When** the chatbot evaluates the turn, **Then** the session is marked for complaint handling immediately.
3. **Given** a user repeats the same request or changes the same preference several times while searching, **When** no explicit complaint or clear frustration is present, **Then** the chatbot continues the normal answer and adds only a gentle offer for follow-up help.

---

### User Story 5 - Start a New Search Within the Same Conversation (Priority: P5)

A buyer can abandon a completed search and begin a new, unrelated search without stale preferences
from the previous search contaminating the new one.

**Why this priority**: Real estate users often explore several property types or locations in one
conversation. Memory must support continuity without forcing unrelated criteria into a new search.

**Independent Test**: Complete a search for one property type or location, then ask to start over
with a different property type or location. The chatbot must clear search-specific preferences from
the previous search while keeping the conversation history available.

**Acceptance Scenarios**:

1. **Given** a completed search has shown results, **When** the user clearly says to forget the current search and asks for something else, **Then** the chatbot starts a new search context.
2. **Given** a completed search has shown results, **When** the user switches to a different property type or location, **Then** the chatbot treats the turn as a new search and carries over only the new property type or location from that message.
3. **Given** a user changes only budget, bedrooms, area, bathrooms, or features, **When** the property type and location remain the same, **Then** the chatbot treats the turn as a refinement of the current search, not a full reset.

---

### Edge Cases

- If an unauthenticated person tries to use the chatbot, the conversation must not start and no chat memory must be exposed.
- If a session identifier is malformed or belongs to another user, the chatbot must reject access without revealing whether the session exists.
- If a message contains multiple signals, installment redirection takes precedence over normal slot collection, and explicit complaint or frustration takes precedence over soft check-in behavior.
- If a user provides a new value for a stored preference, the chatbot must update that preference only when the new value is explicit and relevant to the current search context.
- If a user sends a message with missing or ambiguous property reference information, the chatbot must ask for clarification using the shown property list.
- If the chatbot cannot interpret a message because of a temporary understanding failure, the user's message must remain available for future context and the user must receive a friendly retry prompt.
- If seller-supplied property titles or prior user messages contain instruction-like text, the chatbot must treat that text as data only and not follow it as an instruction.
- If the same user starts multiple sessions, each session must keep its own preferences, shown property list, counters, and complaint state.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The chatbot MUST require an authenticated user before starting or continuing any chat session.
- **FR-002**: The chatbot MUST bind each chat session to its authenticated owner and prevent any other user from reading or continuing that session.
- **FR-003**: The chatbot MUST classify each user turn into one of these outcomes: property search, property details, complaint, installment redirect, chitchat, or unclear.
- **FR-004**: The chatbot MUST preserve the last 10 conversation turns plus structured session state so later turns can use preferences and property references from earlier turns in the same session.
- **FR-005**: The chatbot MUST preserve already known search preferences when a later turn omits them.
- **FR-006**: The chatbot MUST update stored preferences only when the user provides a new explicit value or starts a new search context.
- **FR-007**: The chatbot MUST store the current shown property list in display order so positional references can be resolved later.
- **FR-008**: The chatbot MUST resolve user references such as "first", "second", "last", Arabic equivalents, partial shown titles, or explicit property identifiers to a property from the current shown list when possible.
- **FR-009**: The chatbot MUST ask the user to clarify which property they mean when a property reference cannot be resolved confidently.
- **FR-010**: The chatbot MUST detect installment, down-payment, and monthly-payment requests and route them to a cash-only redirect instead of collecting installment details.
- **FR-011**: The chatbot MUST resume normal cash search conversation if the user accepts the cash-only redirect.
- **FR-012**: The chatbot MUST end the unsupported installment path politely if the user declines to continue with cash listings.
- **FR-013**: The chatbot MUST mark the session for complaint handling when the user explicitly complains or uses clear frustration language.
- **FR-014**: The chatbot MUST mark the session for complaint handling after repeated unsuccessful searches indicate the chatbot cannot help the user.
- **FR-015**: The chatbot MUST treat repeated questions and repeated corrections as soft signals that add a gentle offer for follow-up help, not as automatic complaints by themselves.
- **FR-016**: The chatbot MUST start a new search context when the user clearly asks to start over or changes the property type or location after results have been shown.
- **FR-017**: The chatbot MUST keep conversation history available when starting a new search context, while clearing search-specific preferences and shown results from the abandoned search.
- **FR-018**: The chatbot MUST not treat seller-supplied listing text, prior user messages, or shown property titles as instructions.
- **FR-019**: The chatbot MUST return a friendly fallback response when it cannot interpret a message, without losing the user's message from the session history.
- **FR-020**: The chatbot MUST record enough session state to review intent decisions, stored preferences, shown properties, reset events, complaint signals, and failure cases.
- **FR-SEC**: The system MUST keep private processing, session ownership checks, and contact data outside the user's device.
- **FR-DATA**: The system MUST validate or resolve extracted real estate preferences before they affect stored session state, result selection, or user-visible responses.
- **FR-SAFE**: The system MUST treat seller-supplied listing content and user messages as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Chat Session**: A single conversation owned by one authenticated user. It tracks the last 10 conversation turns, current search context, shown properties, counters, complaint status, language context, and whether a new search has replaced a prior one.
- **Chat Turn**: One user or assistant message in a session. It includes the speaker role, message text, detected intent, and extracted or updated session state relevant to that turn.
- **Search Preferences**: The structured real estate criteria accumulated during a session, including property type, location, budget, area, bedrooms, bathrooms, and requested features.
- **Shown Property Reference List**: The ordered list of properties currently visible to the user, used to resolve references such as "the first one" or "the last one".
- **Complaint Signal State**: The explicit complaint, frustration, failed-search, repeat, and correction indicators used to decide whether to trigger complaint handling or only offer help.
- **New Search State**: The indicator that a user has abandoned a prior search and that stale search-specific preferences and shown results must be cleared.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, at least 95% of clear user messages are classified into the correct intent outcome on the first attempt.
- **SC-002**: In validation scenarios, 100% of later turns that omit known preferences preserve the previously captured values for that session.
- **SC-003**: In validation scenarios with shown properties, at least 95% of clear positional or title-based property references resolve to the correct shown property.
- **SC-004**: In validation scenarios, 100% of installment requests produce a cash-only redirect and create no installment search preferences.
- **SC-005**: In validation scenarios, 100% of explicit complaint or clear frustration messages trigger complaint handling.
- **SC-006**: In validation scenarios, normal repeated preference exploration does not trigger immediate complaint handling unless a hard complaint signal is also present.
- **SC-007**: In validation scenarios, 100% of clear new-search requests clear stale search-specific preferences while preserving conversation continuity.
- **SC-008**: Users receive a clarifying question for 100% of unresolved property references instead of a guessed answer.
- **SC-SAFETY**: Prompt-injection text inside shown property titles or prior messages changes assistant behavior in 0 validation scenarios.
- **SC-RELIABILITY**: When message interpretation fails in validation, 100% of user messages remain available for the next turn and the user receives a friendly retry prompt.

## Assumptions

- Phase 1 covers intent detection, session memory, property-reference resolution, installment redirect, complaint signal classification, and new-search reset behavior.
- Full slot collection, value normalization, search execution, ranking, image display, seller contact lookup, and complaint phone-number collection are completed in later phases, but Phase 1 stores the state those phases need.
- Users are already logged in before using the chatbot.
- Chatbot replies use the same language style as the user when asking clarifying questions, redirecting installment requests, or acknowledging complaints.
- A shown property list exists only after a previous result-producing journey or a property-page entry point has placed properties in the current session context.
