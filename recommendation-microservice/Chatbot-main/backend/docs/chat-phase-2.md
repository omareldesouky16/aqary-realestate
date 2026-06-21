# Phase 2 Slot Collection Notes

Phase 2 extends the authenticated chat API with deterministic slot collection.

Covered behavior:

- Required slot order: property type, location, then maximum budget
- Multi-value capture from one buyer message
- One grouped optional question for area, bedrooms, bathrooms, and features
- Cash-only redirect behavior for installment requests
- Clarification for unclear required slot values
- Preservation of prior slot values on temporary interpretation failure

Known boundary:

- Canonical resolution, search ranking, property cards, seller contact, and complaint phone collection remain later-phase work
