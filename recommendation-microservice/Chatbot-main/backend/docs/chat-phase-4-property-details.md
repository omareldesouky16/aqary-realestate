# Phase 4 Property Details

Phase 4 adds single-property follow-up behavior on top of the current visible Phase 3 result page or a valid first-turn property page context.

## Validation Checklist

- Property references resolve only against the current visible page or a valid page context.
- Ambiguous, missing, or stale references return clarification options instead of details.
- Detail payloads include only available property facts and list missing fields.
- Photo requests return a structured gallery payload for one resolved property only.
- Seller contact is returned only for an explicit contact request for one resolved active property.
- Seller phone is not retained in reusable search result or detail context.
- Detail, photo, contact, and unresolved-reference outcomes are recorded in `detail_events`.

## Quickstart Scenarios

Use `specs/005-phase-4-property-details/quickstart.md` to validate current-page detail answers, ambiguous references, stale-page references, photo gallery requests, explicit seller contact, property page context, safe rendering, reply fallback preservation, and installment redirect precedence.
