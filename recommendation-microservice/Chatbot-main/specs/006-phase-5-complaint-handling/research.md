# Phase 5 Research

## Complaint routing and state persistence

Decision: Keep complaint handling inside the existing authenticated `/api/chat` flow and persist complaint state in `chat_logs.extracted_data` as a reviewable complaint subdocument.

Rationale: the repository already stores owner-bound session state on every turn, so extending the current chat log payload gives complaint state continuity, replayability, and reviewability without adding a separate complaint table or a second write path.

Alternatives considered: a dedicated `complaint_cases` table, or a separate complaint endpoint. Both add schema and routing surface area without improving the core Phase 5 journeys.

## Complaint stage model

Decision: Model complaint handling as a small state machine with `check_in`, `awaiting_issue`, `awaiting_phone`, `invalid_phone_retry`, `saved`, and `declined` stages, plus the existing `isComplaint` and `needsCheckIn` flags.

Rationale: the spec distinguishes hard complaint signals from soft stuck signals, and the implementation needs a deterministic way to preserve progress across turns, especially when phone validation or reply generation fails.

Alternatives considered: stateless prompt-only complaint wording, or collapsing the flow into one boolean flag. Both would lose retry state and make it difficult to preserve safe progress after a temporary failure.

## Phone validation and normalization

Decision: Accept Egyptian mobile numbers in local or country-code form, normalize them to a single canonical `+20` format, and reject malformed values as unconfirmed until one retry is exhausted.

Rationale: the feature explicitly requires Egyptian mobile support and normalized stored contact data. A single canonical output format keeps review, deduplication, and downstream follow-up simple.

Alternatives considered: storing only raw user input, or accepting arbitrary international numbers. Raw input is inconsistent for review; broader international acceptance is outside this phase scope.

## Service responsibilities

Decision: Keep `ComplaintSignalService` focused on hard/soft signal classification, add complaint-specific stage transition and phone helpers beside it, and keep `IntentDetectionService` responsible for reply wording and fallback phrasing only.

Rationale: the current codebase already separates ownership, logging, NLU validation, and signal calculation. Splitting complaint state transitions from reply composition keeps the flow testable and easier to reason about.

Alternatives considered: putting all complaint behavior into `ChatController`, or merging complaint logic into intent detection. Both would make the turn flow harder to test and would couple reply text to persistence rules.

## Frontend rendering

Decision: Extend the Angular chat response types and message rendering to show complaint state and follow-up prompts, while continuing to sanitize assistant text and treat all complaint content as untrusted.

Rationale: the UI needs to surface complaint status without exposing phone data or unsafe seller text, and the existing chat widget already centralizes response rendering.

Alternatives considered: rendering complaint state only in raw JSON responses, or exposing more private complaint detail directly in the client. Neither is acceptable for the privacy and rendering-safety constraints.
