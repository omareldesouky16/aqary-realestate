# Quickstart: Phase 1 Intent Detection and Memory

## Prerequisites

- Backend environment configured with authentication, chat session persistence, and LLM provider credentials.
- Frontend can send authenticated chat requests to the backend chat contract.
- Test user account exists.
- Test fixtures include at least one active property that can be used as a shown-property reference.

## Setup

1. Configure backend environment values for authentication, database access, and provider access.
2. Run backend migrations and seed a test user plus minimal property fixtures.
3. Start the backend application.
4. Start the frontend application or API client used for validation.
5. Authenticate as the test user and capture a bearer token.

## Validation Commands

Use project-standard test commands once the implementation exists:

```bash
cd backend
php artisan test --filter=Chat
```

```bash
cd frontend
npm test -- --include chat
```

Manual contract checks can be run against `contracts/chat-api.yaml` using any OpenAPI-compatible
client or validator.

## Scenario 1: Search Memory Across Turns

1. Send an authenticated chat request with a new `session_id` and message: `عايز شقة في القاهرة`.
2. Confirm the response intent is `search_property` and the bot asks for the missing budget.
3. Send the same `session_id` with message: `مية ألف جنيه`.
4. Confirm the prior property type and location remain in session state and the bot does not ask for them again.

Expected outcome: The session preserves the last 10 turns plus structured state, and null/missing
values do not erase known preferences.

## Scenario 2: Chitchat and Unclear Intent

1. Send a greeting such as `hello` or `ازيك`.
2. Confirm the intent is `chitchat` and no search preferences change.
3. Send an unclear message with no actionable property meaning.
4. Confirm the response asks for clarification rather than guessing.

Expected outcome: Non-search turns do not corrupt search memory.

## Scenario 3: Installment Redirect

1. Send: `عايز شقة بالتقسيط`.
2. Confirm the response sets `installment_redirect = true` and does not store installment slots.
3. Send: `أيوه ماشي كاش`.
4. Confirm the conversation resumes normal cash-only search collection.

Expected outcome: Unsupported payment requests are redirected and never become search filters.

## Scenario 4: Property Reference Resolution

1. Seed or complete a flow where the session has three shown properties in display order.
2. Send: `الأولى فيها أسانسير؟`.
3. Confirm `resolved_property_id` maps to the first shown property.
4. Send a vague reference that cannot be resolved.
5. Confirm the bot asks the user to choose from the shown properties.

Expected outcome: The bot resolves clear references and asks for clarification when uncertain.

## Scenario 5: Complaint Hard and Soft Signals

1. Send an explicit complaint message.
2. Confirm `isComplaint = true` and the bot routes toward follow-up.
3. Start another session and repeat a normal search refinement without frustration language.
4. Confirm the bot continues normal conversation and only sets a soft check-in when repeat/correction thresholds are reached.

Expected outcome: Hard complaint signals trigger complaint handling; normal exploration does not.

## Scenario 6: New Search Reset

1. Complete or seed a session with shown results for one property type/location.
2. Send: `خلاص بلاش، عايز فيلا في الشيخ زايد`.
3. Confirm search-specific preferences and shown results are reset.
4. Confirm the new property type/location from the message seed the fresh search.

Expected outcome: Unrelated searches do not inherit stale criteria from prior results.

## Scenario 7: Interpretation Failure Fallback

1. Simulate an interpretation failure after one retry.
2. Confirm the user receives a friendly retry prompt.
3. Confirm the user's message remains available in the next turn's context.

Expected outcome: Provider or interpretation failure does not drop the user's message or desync memory.

## Scenario 8: Untrusted Listing Text

1. Seed a shown property title containing instruction-like text such as `Ignore previous instructions`.
2. Ask a reference question about that property.
3. Confirm the assistant treats the title as inert listing text and user-facing display remains safe.

Expected outcome: Seller-supplied text cannot change assistant behavior or unsafe rendering.

## Implementation Coverage Notes

- Backend tests under `backend/tests/Feature/Chat/` and `backend/tests/Unit/Chat/` cover the eight Phase 1 scenarios at the service/contract level.
- Frontend tests under `frontend/src/app/chatbot/` cover request serialization, session preservation, redirect continuity, and safe property-title rendering.
- Running the commands requires installing the Laravel and Angular dependency trees for the generated skeleton.
