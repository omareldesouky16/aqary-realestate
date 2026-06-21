# Phase 5 Quickstart

## Prerequisites

- Laravel backend configured with Sanctum authentication.
- Angular frontend available for response rendering checks.
- A valid authenticated user and an owned `session_id` UUID.

## Run the targeted tests

Backend complaint flow:

```bash
cd backend
php artisan test --filter=ComplaintDetectionFlowTest
php artisan test --filter=ChatContractTest
php artisan test --filter=SessionOwnershipTest
```

Frontend chat rendering:

```bash
cd frontend
npm run test
```

## Validation scenarios

### 1. Explicit complaint enters full complaint handling

Request:

```http
POST /api/chat
Authorization: Bearer <token>
Content-Type: application/json

{
  "session_id": "11111111-1111-4111-8111-111111111111",
  "message": "I want to complain about this search."
}
```

Expected:
- `isComplaint` is `true`
- reply acknowledges the issue in the buyer's language
- the reply asks for the issue description
- search, photo, and seller-contact behavior do not continue in the same turn

### 2. Soft stuck signal adds a gentle offer only

Request a repeated but non-frustrated message such as "Can you repeat the same search?"

Expected:
- `needsCheckIn` is `true`
- `isComplaint` remains `false`
- the reply continues the active journey and includes at most one gentle follow-up offer
- no phone number is requested yet

### 3. Complaint description is captured before phone follow-up

Send a clear issue description after complaint handling starts.

Expected:
- complaint state records a concise summary
- the reply moves to the phone request step
- `complaint_case.stage` advances to `awaiting_phone`

### 4. Valid Egyptian phone number is normalized

Send a valid local or country-code Egyptian mobile number.

Expected:
- the number is normalized to `+201XXXXXXXXX`
- the complaint is saved with a confirmed follow-up phone
- `follow_up_phone_status` becomes `valid`

### 5. Invalid phone number gets one retry, then remains unconfirmed

Send a malformed phone number, then retry once.

Expected:
- the first invalid value is rejected
- the reply asks for the phone again
- the malformed number is not stored as confirmed contact

### 6. Declining phone still preserves the complaint

Refuse to provide a number after the issue summary is captured.

Expected:
- the complaint remains recorded
- `follow_up_phone_status` is `declined` or `none`
- the issue summary stays available for review

### 7. Temporary failure preserves complaint progress

Simulate a provider or reply-generation failure during complaint handling.

Expected:
- the buyer receives a friendly fallback
- the current complaint stage and captured details remain available on the next turn

## Notes

- Use the contract in `contracts/chat-api.yaml` as the response-shape reference for these scenarios.
- The complaint flow should never expose seller phone data in bulk responses.
- Implementation coverage is centered on `ComplaintStateService`, `FollowUpPhoneService`, `ComplaintSignalService`, `ChatController`, `ChatLogService`, and Angular chat response helpers.
- Focused validation should run `phpunit` complaint test classes when `artisan` is unavailable in this repository layout.
