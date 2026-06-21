# Phase 2.5 Resolution Notes

Phase 2.5 resolves raw Phase 2 location, property-type, and feature phrases into canonical values before search readiness or later ranking logic uses them.

Guidance:

- Keep raw phrases and canonical outcomes together in chat state
- Show no more than 3 candidates when the buyer needs to choose
- Treat optional feature ambiguity as non-blocking
- Record unresolved and ambiguous phrases for maintainer review
- Keep alias updates outside the chat flow and do not add an admin UI
