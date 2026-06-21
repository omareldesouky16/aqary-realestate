# Chatbot Notes

Phase 2 adds slot collection state to the existing chat response contract.

Key response fields:

- `awaiting_slots`: ordered missing required slots or `optional_preferences` when the grouped optional question is due
- `slot_collection`: structured collection state with required slots, optional slots, clarification, and search readiness
- `fallback`: true when the assistant returned a friendly fallback after interpretation failure

Rendering rules:

- Treat all assistant and seller-supplied text as untrusted display data
- Render property titles with interpolation
- Keep slot prompts concise and deterministic
