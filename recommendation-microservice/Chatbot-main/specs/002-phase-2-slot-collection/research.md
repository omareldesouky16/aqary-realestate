# Research: Phase 2 Slot Collection

## Decision: Required slot order remains property type, location, then maximum budget

**Rationale**: This order matches the root plan and produces a natural narrowing path for buyers. Property type defines the broad category, location anchors relevance, and budget determines whether a later search is practical. Keeping a fixed order also makes the next question deterministic and testable.

**Alternatives considered**: Asking for budget first was rejected because buyers usually describe what and where before price. Letting the model choose the order was rejected because it would make tests less deterministic and could cause repeated or skipped prompts.

## Decision: Capture all clear values from every user turn before asking the next question

**Rationale**: Buyers commonly provide several preferences in one message. Extracting all clear values avoids unnecessary follow-up questions and improves completion speed while preserving the one-missing-required-slot-at-a-time prompt rule.

**Alternatives considered**: Capturing only the next expected slot was rejected because it would ignore useful information already provided by the buyer. Asking the buyer to repeat extra values was rejected as unnecessary friction.

## Decision: Ask exactly one grouped optional-preference question after required slots are complete

**Rationale**: Area, bedrooms, bathrooms, and features are useful for later ranking but are not required. A single grouped question balances relevance with conversational speed and satisfies the success criterion that buyers are not forced through several optional prompts.

**Alternatives considered**: Asking optional slots one by one was rejected because it lengthens the conversation and can block search readiness on nonessential data. Skipping optional preferences entirely was rejected because they materially improve later result ranking.

## Decision: Track optional preferences as answered, declined, or skipped

**Rationale**: Search readiness needs to distinguish between missing optional preferences and a buyer intentionally declining them. This prevents the assistant from asking the optional question repeatedly after the buyer says optional criteria are not important.

**Alternatives considered**: Treating null optional values as both missing and declined was rejected because it cannot tell whether the buyer has already answered the grouped prompt. Storing separate status per optional slot was rejected as more complex than needed for Phase 2.

## Decision: Payment method remains outside slot collection

**Rationale**: The product is cash-only for chatbot search. Installment, down-payment, and monthly-payment requests are handled by the existing redirect flow and must not produce searchable payment criteria.

**Alternatives considered**: Storing a payment preference for future use was rejected because it creates unsupported state and risks invalid search behavior. Asking a payment-method question was rejected because it directly violates the cash-only constraint.

## Decision: Numeric budgets without explicit currency default to EGP

**Rationale**: The chatbot is scoped to an Egyptian real estate marketplace, and local buyers commonly omit the currency when discussing budgets. Defaulting to EGP keeps the conversation concise, avoids unnecessary clarification questions, and makes budget storage deterministic.

**Alternatives considered**: Asking for currency on every omitted-currency budget was rejected as needless friction for the main market. Storing currency-less numeric budgets was rejected because later search and validation need a concrete monetary interpretation.

## Decision: Ambiguous required values remain incomplete until clarified

**Rationale**: Unclear property type, location, or budget values can lead to irrelevant or empty searches. The safest user experience is to ask a targeted clarification and preserve existing valid preferences without guessing.

**Alternatives considered**: Guessing based on model confidence was rejected because Phase 2 has no canonical resolution guarantee. Proceeding with partial required slots was rejected because later search readiness depends on all three required preferences.

## Decision: Phase 2 stores best-effort extracted values but defers canonical resolution

**Rationale**: The root plan separates slot collection from canonical property-type, location, and feature resolution. Phase 2 can collect and persist buyer intent while Phase 2.5 resolves raw values to canonical records before search.

**Alternatives considered**: Combining slot collection and resolution was rejected because it expands Phase 2 beyond its specified scope. Deferring all storage until resolution was rejected because it would lose conversational progress across turns.

## Decision: Extend the existing `/api/chat` contract instead of adding a new endpoint

**Rationale**: Slot collection is part of the same turn-by-turn chat workflow as Phase 1. Reusing the existing chat endpoint keeps session ownership, history, fallback behavior, and frontend integration consistent.

**Alternatives considered**: Adding a dedicated slot endpoint was rejected because it would split one conversation across multiple client contracts and duplicate ownership/fallback handling.
