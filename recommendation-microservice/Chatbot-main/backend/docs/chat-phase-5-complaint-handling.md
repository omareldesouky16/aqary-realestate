# Phase 5 Complaint Handling

Phase 5 adds authenticated complaint handling inside the existing chat flow. Hard complaint signals start a focused complaint flow, while soft stuck signals add one gentle follow-up offer without interrupting normal exploration.

## Validation Checklist

- Explicit complaints, frustration, and repeated failed searches enter full complaint handling.
- Soft repeats and preference corrections set `needsCheckIn` without requesting a phone number.
- Complaint issue summaries are captured before follow-up phone collection.
- Egyptian mobile numbers are normalized to `+201XXXXXXXXX` before confirmation.
- Malformed phone numbers are rejected as unconfirmed and retried.
- Declined phone contact preserves the complaint without a confirmed phone.
- Complaint turns block search, property details, galleries, and seller contact in the same turn.
- Complaint events are reviewable without exposing unrelated seller contact or provider secrets.

## Events

- `started`: full complaint handling began.
- `soft_check_in`: a gentle offer was shown without escalation.
- `issue_captured`: a concise complaint summary was stored.
- `phone_requested`: follow-up phone was requested.
- `phone_invalid`: malformed phone input was rejected.
- `phone_accepted`: valid phone was normalized and accepted.
- `phone_declined`: buyer declined follow-up phone.
- `saved`: complaint is ready for review.
- `fallback`: complaint state was preserved during a temporary fallback.
