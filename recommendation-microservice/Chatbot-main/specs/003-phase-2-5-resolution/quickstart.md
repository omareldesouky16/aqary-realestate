# Quickstart: Phase 2.5 Location, Feature, and Property-Type Resolution

## Prerequisites

- Phase 1 authenticated chat and Phase 2 slot collection behavior are available.
- Backend environment is configured with authentication, database access, provider credentials, and resolver data.
- Test fixtures include known locations, supported property categories, known features, and approved aliases.
- Frontend or API client can send authenticated chat requests to `/api/chat`.
- A test user account and bearer token are available.

## Setup

1. Configure backend environment values for authentication, database access, and provider access.
2. Run backend migrations and seed a test user plus location, property-type, feature, and alias fixtures.
3. Start the backend application.
4. Start the frontend application or API client used for validation.
5. Authenticate as the test user and capture a bearer token.

## Validation Commands

Use project-standard test commands once the implementation exists:

```bash
cd backend
php artisan test --filter=Resolution
```

```bash
cd backend
php artisan test --filter=Chat
```

```bash
cd frontend
npm test -- --include chat
```

Manual contract checks can be run against `contracts/chat-api.yaml` using any OpenAPI-compatible client or validator.

## Scenario 1: Clear Location Alias Resolves

1. Send an authenticated chat request with a new `session_id` and message containing a location alias such as `Tagamoa`.
2. Confirm `resolution.outcomes.location.status = resolved`.
3. Confirm the response does not ask for location again.
4. Confirm the stored location has the canonical known location ID/name while preserving the raw phrase for audit or reply context.

Expected outcome: Clear known names and approved aliases resolve before search readiness uses location.

## Scenario 2: Ambiguous Required Location Asks for Confirmation

1. Seed two or more plausible known locations for a shared phrase.
2. Send a search message containing that ambiguous location phrase.
3. Confirm `search_ready = false`.
4. Confirm `resolution.pending_clarification.preference_type = location`.
5. Confirm the prompt shows no more than 3 candidates.
6. Send the buyer's candidate choice.
7. Confirm location becomes resolved and slot collection resumes from the next missing preference.

Expected outcome: The chatbot asks for confirmation instead of guessing required location.

## Scenario 3: Property Type Synonym Resolves

1. Send a search message with a supported synonym such as `flat` or an Arabic apartment term.
2. Confirm `resolution.outcomes.propertyType.status = resolved`.
3. Confirm the canonical supported property category is stored.
4. Send an unsupported property type.
5. Confirm the property type remains incomplete and the bot asks for clarification instead of running a search.

Expected outcome: Supported synonyms resolve; unsupported property types do not create zero-result searches.

## Scenario 4: Optional Features Retain Clear Values

1. Complete required preferences or seed a session where they are complete.
2. Answer the grouped optional question with multiple feature phrases.
3. Confirm every clear feature is represented as a resolved feature outcome.
4. Include one unclear feature phrase.
5. Confirm the unclear feature is logged and does not block search readiness.

Expected outcome: Clear optional features are retained while unclear feature wording does not stop the buyer from continuing.

## Scenario 5: State Preservation During Failed or Ambiguous Resolution

1. Seed a session with property type, budget, optional preferences, counters, and redirect/fallback state.
2. Send an ambiguous required location phrase.
3. Confirm only location is incomplete or pending clarification.
4. Confirm unrelated preferences and counters remain unchanged.
5. Simulate a temporary resolver failure and confirm the buyer receives a friendly fallback.

Expected outcome: Resolution failures and ambiguities do not corrupt existing session state.

## Scenario 6: Cash-Only Redirect Takes Precedence

1. Seed a session with a pending resolution clarification.
2. Send a message asking for installments or monthly payments.
3. Confirm `installment_redirect = true`.
4. Confirm no payment method is stored as a slot or resolver outcome.
5. Confirm the previous slot and resolution state is preserved for resumption if the buyer accepts cash listings.

Expected outcome: Existing cash-only behavior remains authoritative during resolution.

## Scenario 7: Review Loop Improves Future Resolution

1. Process a phrase that is initially unresolved.
2. Confirm a `ResolutionReviewItem` is available with the phrase, preference type, and status.
3. Add an approved alias through managed project data or configuration outside the chatbot flow.
4. Process the same phrase in a future session.
5. Confirm it resolves without requiring a clarification prompt.

Expected outcome: Maintainer-managed aliases improve repeat resolution without adding an admin UI or changing the conversation design.

## Scenario 8: Prompt Injection Text Is Treated as Data

1. Seed prior message or listing text that contains instruction-like content.
2. Send a message that triggers resolution using normal buyer wording.
3. Confirm resolver behavior is unaffected by instruction-like prior text.
4. Confirm the frontend renders candidates and canonical names safely.

Expected outcome: User and seller text cannot alter resolver instructions or unsafe display behavior.

## Implementation Coverage Notes

- Backend feature tests under `backend/tests/Feature/Chat/` should cover authenticated contract behavior and end-to-end chat state transitions.
- Backend unit tests under `backend/tests/Unit/Chat/` should cover resolver services, alias matching, candidate limiting, and review item creation.
- Frontend tests under `frontend/src/app/chatbot/` should cover candidate prompt rendering, `resolution` payload handling, and safe display of candidate text.
