# Phase 4 Research: Property Details

## Decision: Resolve references only against current visible results or valid page context

**Rationale**: The spec and constitution require deterministic grounding. Positional references such as "first" and "second" are meaningful only for the current visible page from Phase 3, while a property page context is valid only when it is owner-accessible, active, and explicitly supplied by the client for that turn/session.

**Alternatives considered**: Searching all prior result pages was rejected because it makes positional language stale and ambiguous. Asking the LLM to infer a property from free text was rejected because it can produce cross-property or fabricated matches.

## Decision: Use deterministic detail retrieval before LLM reply composition

**Rationale**: Property-detail replies must include only available facts for one resolved property. Laravel services should assemble a safe fact payload, mark missing fields explicitly, and pass delimited untrusted listing text to OpenRouter only for language/register composition.

**Alternatives considered**: Letting the LLM answer from raw chat history was rejected because it risks hallucinated amenities and stale property facts. Returning only structured facts without LLM wording was rejected because the product requires conversational Arabic/English replies.

## Decision: Treat galleries as structured response payloads, not conversational facts

**Rationale**: The chatbot text should invite viewing photos while the frontend renders an ordered gallery from a structured `property_gallery` payload. This avoids exposing raw storage paths in natural-language replies and keeps image metadata inert.

**Alternatives considered**: Embedding image paths in reply text was rejected because it creates unsafe rendering and poor UX. Reusing only cover images from bulk search was rejected because Phase 4 explicitly covers full-gallery viewing.

## Decision: Seller contact is a one-turn explicit response

**Rationale**: Seller phone numbers are privacy-sensitive. Contact lookup should run only after authenticated owner/session checks and single-property resolution, then return contact in a dedicated response field for that turn only. It must not be retained in reusable search result state or included in normal details/photos.

**Alternatives considered**: Including phone numbers in all detail responses was rejected due to privacy risk. Storing contact in visible result context was rejected because later ambiguous or bulk replies could leak it.

## Decision: Persist reviewable detail outcome events

**Rationale**: The constitution requires observability and graceful degradation. Persisted outcome records let implementation and demos verify reference resolution, missing-field handling, photo/contact gating, reply fallback preservation, and unsafe-input handling.

**Alternatives considered**: Relying only on transient logs was rejected because chat behavior needs session-level review and test assertions. Persisting raw provider prompts/responses was rejected because it risks exposing private data and credentials.

## Decision: Extend the existing `/api/chat` contract

**Rationale**: Phase 4 is a chat follow-up capability, not a separate public property-detail API. Extending the existing authenticated chat contract keeps session ownership, language handling, installment redirect precedence, result context, and response rendering consistent.

**Alternatives considered**: Adding standalone `/api/properties/{id}/chat-details` endpoints was rejected for this phase because it would duplicate session/context checks and weaken the single chat-state source of truth.
