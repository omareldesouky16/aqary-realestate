# Phase 0 Research: Phase 1 Intent Detection and Memory

## Decision: Use Laravel as the sole AI boundary

**Rationale**: The constitution and root plan require all LLM calls, prompts, raw provider responses,
API keys, session ownership checks, and private contact data to stay server-side. Laravel is already
the planned backend and can authenticate requests, validate session ownership, retry provider calls,
validate NLU JSON, and persist deterministic state before returning a client-safe response.

**Alternatives considered**: Calling the LLM directly from Angular was rejected because it exposes
keys and prompt details. Splitting NLU between frontend and backend was rejected because it creates
two sources of truth for session state.

## Decision: Keep the last 10 turns plus structured session state

**Rationale**: The clarification session selected the last 10 conversation turns plus structured
session state. This matches root `plan.md`, supports short multilingual property conversations,
and keeps prompt size predictable while preserving the facts needed for intent and reference handling.

**Alternatives considered**: Current-turn-only memory was rejected because it loses property references
and prior preferences. Full-history memory was rejected because it increases token cost and prompt
injection surface without clear Phase 1 value.

## Decision: Persist structured session state in chat log extracted data

**Rationale**: The root plan already uses `chat_logs.extracted_data` for slots, shown properties,
counters, complaint state, reset state, and fallback/error metadata. This avoids a large session-state
schema before later phases stabilize while still making each turn auditable.

**Alternatives considered**: A dedicated state table was deferred because Phase 1 needs flexibility.
Client-side state was rejected because it is not authoritative and can be tampered with.

## Decision: Add a dedicated chat session ownership record

**Rationale**: `session_id` is client-provided. Binding it to the authenticated user prevents replay
or accidental cross-user access to chat history, shown properties, and complaint indicators.

**Alternatives considered**: Inferring ownership from existing chat log rows was rejected because a
new session has no rows and ownership checks would be inconsistent.

## Decision: Treat NLU output as untrusted until schema-valid

**Rationale**: The LLM can return malformed JSON, unsupported intent values, unexpected fields, or
nulls that would otherwise erase known preferences. Schema validation and null-safe merge rules keep
session state deterministic.

**Alternatives considered**: Trusting model output directly was rejected because it violates the
constitution and risks state corruption.

## Decision: Separate hard complaint triggers from soft check-in signals

**Rationale**: Explicit complaints, clear frustration, and repeated failed searches represent a strong
need for follow-up. Repeated questions or preference corrections can also occur during normal search
exploration, so they should add only a gentle check-in until the user accepts help or a hard signal
appears.

**Alternatives considered**: Triggering complaints from all repeated behavior was rejected because it
creates false positives and interrupts normal property browsing.

## Decision: Reset search-specific state only for explicit reset or changed type/location after results

**Rationale**: A user changing property type or location after results have appeared is likely starting
a new search. Changing budget, bedrooms, area, bathrooms, or features is a normal refinement and must
not wipe the current search.

**Alternatives considered**: Resetting on any preference change was rejected because it loses useful
context. Never resetting was rejected because stale criteria contaminate unrelated searches.

## Decision: Use one retry and a language-aware fallback for NLU failure

**Rationale**: One retry handles transient provider failures without hiding systemic issues. If the
retry fails, the user still receives a friendly prompt to retry, and their message remains available
for the next turn.

**Alternatives considered**: Multiple retries were rejected because they increase latency and quota
pressure. Failing silently was rejected because it breaks user trust and conversation continuity.
