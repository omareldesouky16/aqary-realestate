# Feature Specification: Phase 5 Complaint Handling

**Feature Branch**: `006-phase-5-complaint-handling`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 5"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Route Genuine Complaints to Follow-Up (Priority: P1)

An authenticated buyer who explicitly complains, clearly expresses frustration, or has repeated unsuccessful searches is moved into a focused complaint flow where the chatbot acknowledges the issue and asks what went wrong in the buyer's language.

**Why this priority**: This is the minimum viable complaint journey. A user who is stuck or upset needs a clear path to human follow-up without continuing through normal search or property-detail prompts.

**Independent Test**: Send an explicit complaint, a clear frustration message, and a session with repeated failed searches. Verify each one enters complaint handling, receives an empathetic acknowledgement, and asks for the issue description without exposing prior private data.

**Acceptance Scenarios**:

1. **Given** an authenticated buyer explicitly says they want to complain, **When** the chatbot processes the turn, **Then** complaint handling starts immediately and the buyer is asked to describe the issue.
2. **Given** an authenticated buyer uses clear frustration language, **When** the chatbot processes the turn, **Then** complaint handling starts immediately and the buyer receives an empathetic response.
3. **Given** an authenticated buyer has repeated unsuccessful searches in the same session, **When** the complaint threshold is reached, **Then** the chatbot starts complaint handling without waiting for the buyer to use the word "complaint".
4. **Given** complaint handling starts, **When** the chatbot replies, **Then** it does not continue property search, ranking, photo, or seller-contact behavior in the same turn.

---

### User Story 2 - Offer Help Without Interrupting Normal Exploration (Priority: P2)

When the buyer shows softer signs that the conversation may be stuck, such as repeating a request or repeatedly correcting the same preference, the chatbot continues the normal answer and adds a single gentle offer for follow-up help instead of starting the full complaint flow.

**Why this priority**: False-positive complaints can derail normal property exploration. Soft check-ins preserve trust while keeping the buyer in control.

**Independent Test**: Repeat the same request or correct the same preference several times without explicit frustration. Verify the chatbot continues the active journey and adds only one follow-up offer, with no phone request and no logged complaint.

**Acceptance Scenarios**:

1. **Given** the buyer repeats the same unresolved request without explicit complaint language, **When** the chatbot replies, **Then** it answers the active request normally and adds a gentle follow-up offer.
2. **Given** the buyer repeatedly corrects the same preference while still exploring, **When** no hard complaint signal is present, **Then** the chatbot does not start complaint handling or ask for a phone number.
3. **Given** the buyer accepts the gentle follow-up offer, **When** the buyer confirms they want help, **Then** the next turn enters the full complaint flow.
4. **Given** the buyer ignores the gentle offer and continues searching, **When** the next turn is handled, **Then** the chatbot continues the normal journey without repeating the same offer unless stuck signals increase again.

---

### User Story 3 - Capture Complaint Details (Priority: P3)

After complaint handling starts, the buyer can describe what went wrong and the chatbot records a concise complaint summary tied to the buyer's session.

**Why this priority**: A complaint without a clear issue description is hard for the follow-up team to act on. The system must collect the issue while preserving the buyer's language and privacy.

**Independent Test**: Start complaint handling, provide an issue description, and verify the complaint summary is recorded for review while the chatbot asks for a follow-up phone number.

**Acceptance Scenarios**:

1. **Given** complaint handling is active and no issue description has been captured, **When** the buyer describes the issue, **Then** the chatbot records a concise complaint description for review.
2. **Given** the complaint description is unclear or empty, **When** the buyer responds, **Then** the chatbot asks one concise clarification instead of inventing the issue.
3. **Given** the issue description has been captured, **When** the chatbot replies, **Then** it asks for a phone number so the team can follow up.
4. **Given** seller-supplied property text or previous chat text contains unsafe instructions, **When** the complaint summary is recorded, **Then** that text is treated only as complaint context and not as operating instructions.

---

### User Story 4 - Validate and Store Follow-Up Phone (Priority: P4)

The buyer can provide an Egyptian mobile number for follow-up. The system accepts common local and country-code formats, normalizes the number for review, rejects malformed numbers with one retry, and still preserves the complaint if the buyer declines to provide a valid number.

**Why this priority**: The follow-up team needs a reachable number, but invalid numbers should not silently become confirmed contact data. The complaint must not be lost if the buyer refuses to share contact details.

**Independent Test**: Submit valid local, valid country-code, invalid, retried, and declined phone responses. Verify valid numbers are normalized, invalid numbers are re-asked once, and declined contact still logs the complaint without a phone number.

**Acceptance Scenarios**:

1. **Given** complaint handling has captured the issue, **When** the buyer provides a valid Egyptian mobile number, **Then** the complaint is saved with a normalized follow-up phone number.
2. **Given** the buyer provides a malformed phone number, **When** it is checked, **Then** the chatbot says it does not look valid and asks the buyer to send it again.
3. **Given** the buyer provides a valid number after the retry prompt, **When** the number is accepted, **Then** the complaint is saved with the normalized number.
4. **Given** the buyer declines to provide a phone number or stops after an invalid number, **When** complaint handling completes or remains pending, **Then** the complaint description remains recorded with no confirmed phone number.

---

### User Story 5 - Preserve Complaint State Through Failures (Priority: P5)

If reply generation or understanding temporarily fails during complaint handling, the buyer receives a friendly fallback and the complaint state collected so far remains available in the next turn.

**Why this priority**: Complaint users are already frustrated. Losing their complaint text or phone progress after a temporary failure would make the experience worse and reduce trust.

**Independent Test**: Simulate temporary failures during complaint acknowledgement, issue capture, invalid-phone retry, and final confirmation. Verify the buyer receives a language-appropriate fallback and the next turn resumes from the correct complaint stage.

**Acceptance Scenarios**:

1. **Given** complaint handling has started, **When** a temporary reply failure occurs, **Then** the buyer receives a friendly fallback and the complaint stage is preserved.
2. **Given** the buyer has already described the issue, **When** a temporary failure happens before the phone request, **Then** the issue description remains available on the next turn.
3. **Given** the buyer submitted an invalid phone number, **When** a temporary failure happens during the retry response, **Then** the system does not store the malformed number as confirmed contact.
4. **Given** the complaint has been saved, **When** the chatbot responds, **Then** it does not automatically push the buyer back into search in the same turn.

### Edge Cases

- If the buyer is unauthenticated, complaint details and phone numbers must not be accepted or exposed.
- If the session does not belong to the authenticated buyer, complaint state must not be read or updated.
- If a message contains installment, property-detail, and complaint signals together, the complaint flow takes precedence when the buyer is explicitly complaining or clearly frustrated.
- If soft stuck signals occur during normal exploration, the system must not request a phone number until the buyer accepts help or a hard complaint signal appears.
- If the buyer gives a malformed phone number multiple times, the system must avoid storing it as confirmed contact and keep the complaint available for review.
- If the buyer gives personal data inside the complaint description, the system must store only what is needed for follow-up and must not expose it back in unrelated replies.
- If the buyer changes their mind and returns to search after a complaint is recorded, the complaint must remain reviewable and the search flow must start only from a clear new request.
- If user messages contain prompt-injection instructions, HTML, script-like content, or unsafe markdown, they must be treated as inert complaint text.
- If reply generation temporarily fails after complaint text or phone state has been resolved, the buyer must receive a friendly fallback and the safe complaint state must remain available.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST allow complaint handling only for authenticated buyers in sessions they own.
- **FR-002**: The system MUST start full complaint handling when a buyer explicitly complains, uses clear frustration language, or reaches the repeated-unsuccessful-search threshold.
- **FR-003**: The system MUST treat repeated questions and repeated preference corrections as soft stuck signals that add a gentle follow-up offer, not as automatic full complaints.
- **FR-004**: The system MUST enter full complaint handling on the next turn when the buyer accepts a gentle follow-up offer.
- **FR-005**: The system MUST acknowledge complaints empathetically in the buyer's language before asking for more details.
- **FR-006**: The system MUST ask the buyer to describe what went wrong when complaint handling starts and no complaint description is available.
- **FR-007**: The system MUST record a concise complaint description when the buyer provides enough information.
- **FR-008**: The system MUST ask one concise clarification when the buyer's complaint description is empty or unclear.
- **FR-009**: The system MUST ask for a follow-up phone number after the complaint description is captured.
- **FR-010**: The system MUST accept Egyptian mobile numbers in common local or country-code formats.
- **FR-011**: The system MUST normalize accepted phone numbers into one canonical follow-up format before recording them.
- **FR-012**: The system MUST reject malformed phone numbers as unconfirmed and ask the buyer to retry once.
- **FR-013**: The system MUST keep the complaint recorded even when the buyer declines to provide a phone number.
- **FR-014**: The system MUST NOT store malformed phone input as a confirmed phone number.
- **FR-015**: The system MUST record complaint outcomes in a reviewable form, including whether a phone number was provided, invalid, declined, or still pending.
- **FR-016**: The system MUST preserve complaint stage, complaint text, invalid-phone state, and confirmed-contact state across turns in the same session.
- **FR-017**: The system MUST return a friendly fallback if complaint handling temporarily fails, without losing the buyer's message or already captured complaint state.
- **FR-018**: The system MUST NOT continue property search, photo, property-detail, or seller-contact behavior in the same turn after a complaint has been recorded.
- **FR-019**: The system MUST allow the buyer to return to normal search only after a clear new request following complaint capture.
- **FR-SEC**: The system MUST keep private complaint processing, session ownership checks, complaint text, and complaint phone data outside the user's device except for the buyer's own current-turn confirmation.
- **FR-DATA**: The system MUST validate and normalize phone input before it affects confirmed complaint contact state.
- **FR-SAFE**: The system MUST treat user complaint text, prior messages, and seller-supplied listing content as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Complaint Signal State**: The hard and soft indicators used to decide whether to start full complaint handling or only offer follow-up help.
- **Complaint Case**: A reviewable complaint record tied to one authenticated chat session, including issue description, capture status, and follow-up readiness.
- **Complaint Stage**: The current point in the complaint journey, such as acknowledgement, awaiting issue description, awaiting phone, invalid-phone retry, saved, or declined contact.
- **Follow-Up Phone**: A buyer-provided Egyptian mobile number that is validated, normalized, and recorded only when accepted.
- **Complaint Outcome Event**: A reviewable event showing complaint start, soft check-in, issue captured, invalid phone, phone accepted, contact declined, saved complaint, or fallback.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, 100% of explicit complaint messages start full complaint handling.
- **SC-002**: In validation scenarios, 100% of clear frustration messages start full complaint handling.
- **SC-003**: In validation scenarios, repeated normal exploration triggers 0 full complaint flows unless a hard complaint signal or buyer acceptance occurs.
- **SC-004**: In validation scenarios, 100% of soft stuck-signal replies continue the active journey and include at most one gentle follow-up offer.
- **SC-005**: In validation scenarios, 100% of complaint flows capture or request an issue description before asking for phone follow-up.
- **SC-006**: In validation scenarios, 100% of valid Egyptian mobile numbers are accepted and normalized consistently.
- **SC-007**: In validation scenarios, 100% of malformed phone numbers are rejected as unconfirmed and receive one retry prompt.
- **SC-008**: In validation scenarios, 100% of declined-phone complaints remain recorded without a confirmed phone number.
- **SC-009**: In validation scenarios, 100% of complaint replies preserve the buyer's Arabic or English language and conversational register.
- **SC-010**: In validation scenarios, complaint acknowledgement and next-step replies appear within 3 seconds under normal operating conditions.
- **SC-SAFETY**: Prompt-injection or unsafe markup inside complaint text, prior messages, or listing text changes assistant behavior or unsafe rendering in 0 validation scenarios.
- **SC-RELIABILITY**: In simulated temporary failures during complaint handling, 100% of sessions preserve safe complaint state and return a language-appropriate fallback.

## Assumptions

- Phase 5 starts after authenticated session ownership, intent detection, hard/soft complaint signal calculation, chat memory, and language-preserving replies are available from earlier phases.
- Full complaint handling is limited to buyer-facing chat follow-up capture; staff dashboards, ticket assignment, and outbound calling workflows are outside this phase.
- Complaint records are retained in the existing reviewable chat/session history unless a later governance decision introduces a dedicated complaint management surface.
- Egyptian mobile numbers are the supported follow-up phone format for this phase.
- The chatbot does not ask for phone numbers during soft check-ins unless the buyer accepts help or a hard complaint signal appears.
- Complaint handling can coexist with search history, but once a complaint is recorded the same turn does not continue search, property details, photos, or seller-contact behavior.
