# Quickstart: Phase 2 Slot Collection

## Purpose

Validate that Phase 2 collects search preferences correctly without executing full search/ranking behavior. These scenarios prove required slot order, multi-value capture, EGP budget defaulting, grouped optional collection, cash-only redirects, ambiguity handling, and fallback preservation.

## Prerequisites

- Phase 1 chat session ownership, intent detection, memory, installment redirect, and fallback behavior are available.
- An authenticated buyer account exists.
- The chat endpoint follows `contracts/chat-api.yaml`.
- Test data or mocked provider responses can produce clear Arabic, English, and mixed-language slot extraction results.

## Setup

1. Start the backend application from the repository's backend project.
2. Start the frontend application from the repository's frontend project if validating through the UI.
3. Authenticate as a buyer and capture a bearer token.
4. Generate a new UUID for `session_id`.
5. Use `POST /api/chat` for each scenario and keep the same `session_id` within a scenario.

## Validation Scenarios

### Scenario 1: Required Slot Order

1. Send: `I want to buy an apartment`.
2. Expect `slot_collection.required_slots.propertyType.status` to be `complete`.
3. Expect `slot_collection.missing_required_slots` to contain `location`, then `price`.
4. Expect `slot_collection.next_question_slot` to be `location`.
5. Send: `in New Cairo`.
6. Expect `location` to be complete and `next_question_slot` to be `price`.
7. Send: `maximum 3000000`.
8. Expect required slots to be complete, the budget currency to be `EGP`, and `next_question_slot` to be `optional_preferences`.

### Scenario 2: Multi-Value First Message

1. Start a new session.
2. Send: `I need a villa in Sheikh Zayed under 8000000 with 4 bedrooms and a garden`.
3. Expect property type, location, price, bedrooms, and features to be captured.
4. Expect no redundant question for property type, location, or price.
5. Expect optional collection to be answered or ready to proceed based on captured optional values.

### Scenario 3: Grouped Optional Preferences

1. Start a new session.
2. Complete property type, location, and price with clear answers.
3. Expect the assistant to ask one grouped optional question covering size, bedrooms, bathrooms, and features.
4. Send: `not important`.
5. Expect `optional_collection_status` to be `declined`.
6. Expect `search_ready` to be true.
7. Expect no later separate prompts for area, bedrooms, bathrooms, or features in the same search context.

### Scenario 4: Optional Values Provided

1. Start a new session.
2. Complete required slots.
3. When the grouped optional question is asked, send: `At least 160 meters, 3 bedrooms, parking and security`.
4. Expect area, bedrooms, and features to be captured.
5. Expect omitted bathrooms to remain empty without blocking search readiness.

### Scenario 5: Omitted Budget Currency Defaults to EGP

1. Start a new session.
2. Send: `I need an apartment in New Cairo under 3000000`.
3. Expect property type, location, and price to be captured.
4. Expect the captured budget currency to be `EGP`.
5. Expect no currency clarification question.

### Scenario 6: Cash-Only Redirect During Slot Collection

1. Start a new session.
2. Send: `I want an apartment by installment`.
3. Expect `intent` to be `installment_redirect`.
4. Expect `installment_redirect` to be true.
5. Expect no payment-method slot in `slot_collection`.
6. Send: `okay cash is fine`.
7. Expect slot collection to resume from the next missing non-payment preference.

### Scenario 7: Ambiguous Required Value

1. Start a new session.
2. Send a message with an unclear location, such as a vague or unsupported place name.
3. Expect `slot_collection.clarification.slot_name` to be `location`.
4. Expect the location slot status to be `unclear` or `ambiguous`.
5. Expect `search_ready` to remain false.
6. Send a clear location clarification.
7. Expect the location to become complete and collection to resume from the first remaining required slot.

### Scenario 8: Temporary Interpretation Failure

1. Start a new session and complete property type and location.
2. Force or simulate a temporary interpretation failure on the next user message.
3. Expect a friendly fallback response.
4. Expect previously captured property type and location to remain intact.
5. Send a clear budget after the fallback.
6. Expect the budget to be captured and the flow to continue normally.

### Scenario 9: Prompt-Injection Text Is Inert

1. Start a session with prior text or listing-related content containing instruction-like phrases.
2. Send a normal slot-collection message.
3. Expect slot collection to follow the required order and ignore the instruction-like text.
4. Expect no unauthorized data exposure or behavior change.

## Expected Completion

- Required slot collection is complete only when property type, location, and maximum budget are complete.
- Optional collection is complete after the grouped optional question is answered, declined, or skipped.
- `search_ready` becomes true only after required slots are complete and optional handling is complete.
- Numeric budget amounts without explicit currency are treated as EGP.
- Installment-related requests never create payment slots.
- Unclear required values trigger targeted clarification.
- Existing valid preferences survive interpretation failures.
