# Phase 5 Data Model

## ChatSession

Existing entity used for ownership and session scoping.

Fields:
- `session_id` UUID primary key
- `user_id` authenticated owner
- timestamps

Relationships:
- `hasMany` `ChatLog`

Validation rules:
- Complaint processing may only read or mutate state for the authenticated owner.

## ChatLog

Existing entity that stores each turn and the extracted state snapshot.

Fields:
- `session_id`
- `role`
- `message`
- `intent_detected`
- `extracted_data`
- timestamps

Relationships:
- `belongsTo` `ChatSession`

Phase 5 usage:
- `extracted_data.complaint_state` stores the current complaint stage, issue summary, and phone status.
- `extracted_data.complaint_events` stores reviewable complaint outcomes over time.

## ComplaintSignalState

Computed routing state used to decide whether a turn should enter full complaint handling or only add a soft check-in.

Fields:
- `explicit_complaint` boolean
- `frustration_detected` boolean
- `failed_searches` integer
- `repeat_count` integer
- `slot_contradiction_count` integer
- `needs_check_in` boolean
- `is_complaint` boolean

Rules:
- Any explicit complaint, clear frustration, or repeated unsuccessful search threshold starts full complaint handling.
- Repeat or contradiction signals alone only set the gentle check-in path.

## ComplaintCase

Logical reviewable complaint record tied to one owned chat session.

Fields:
- `session_id`
- `status` one of `active`, `saved`, `declined`, `fallback_pending`
- `stage` one of `check_in`, `awaiting_issue`, `awaiting_phone`, `invalid_phone_retry`, `saved`, `declined`
- `issue_summary` nullable string
- `issue_language` nullable string
- `follow_up_phone_raw` nullable string
- `follow_up_phone_normalized` nullable string
- `follow_up_phone_status` one of `pending`, `valid`, `invalid`, `declined`, `none`
- `follow_up_phone_attempts` integer
- `last_event_type` nullable string
- `updated_at`

Relationships:
- `belongsTo` `ChatSession`
- `hasMany` `ComplaintOutcomeEvent`

Validation rules:
- `issue_summary` must be concise and derived from the buyer's message, not from seller instructions.
- `follow_up_phone_normalized` may only be set after validation succeeds.
- Invalid phone input stays unconfirmed and does not overwrite a valid phone.
- Declining phone contact preserves the complaint record.

## FollowUpPhone

Value object for Egyptian mobile follow-up numbers.

Accepted inputs:
- Local format such as `01XXXXXXXXX`
- Country-code format such as `+201XXXXXXXXX`
- Country-code with dial prefix such as `00201XXXXXXXXX`

Canonical output:
- `+201XXXXXXXXX`

Validation rules:
- Only Egyptian mobile patterns are accepted.
- Malformed values are rejected once with a retry prompt.
- Stored numbers must always be normalized.

## ComplaintOutcomeEvent

Reviewable event representing one step in the complaint journey.

Fields:
- `type` such as `started`, `soft_check_in`, `issue_captured`, `phone_requested`, `phone_invalid`, `phone_accepted`, `phone_declined`, `saved`, `fallback`
- `stage`
- `message`
- `created_at`
- `metadata`

Rules:
- The event log must preserve complaint progress across turns even when reply generation fails.
- Events are append-only and must not expose private data beyond what the complaint flow already captured.

## State transitions

- `check_in` -> `awaiting_issue` when a hard complaint signal starts the flow.
- `awaiting_issue` -> `awaiting_phone` when the buyer provides a usable issue summary.
- `awaiting_phone` -> `invalid_phone_retry` when a malformed phone number is entered.
- `invalid_phone_retry` -> `saved` when a normalized phone is accepted.
- `awaiting_phone` -> `declined` when the buyer refuses to share contact details.
- Any active stage -> `fallback_pending` only for transient reply failures, while the already captured complaint state remains intact.
