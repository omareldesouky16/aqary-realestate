<!--
Sync Impact Report
Version change: template -> 1.0.0
Modified principles:
- Template principle 1 -> I. Authenticated, Server-Side AI Boundary
- Template principle 2 -> II. Deterministic Real Estate Data Grounding
- Template principle 3 -> III. Conversation Safety and Privacy
- Template principle 4 -> IV. Independent, Testable User Journeys
- Template principle 5 -> V. Observable Reliability and Graceful Degradation
Added sections:
- Product and Technology Constraints
- Development Workflow and Quality Gates
Removed sections:
- Template placeholder comments and undefined placeholder sections
Templates requiring updates:
- ✅ .specify/templates/plan-template.md
- ✅ .specify/templates/spec-template.md
- ✅ .specify/templates/tasks-template.md
- ✅ .specify/templates/commands/*.md (directory not present; no command files to update)
- ✅ AGENTS.md (already points agents to the current plan; no change required)
Follow-up TODOs:
- None
-->
# Aqary Real Estate Chatbot Constitution

## Core Principles

### I. Authenticated, Server-Side AI Boundary
All chatbot requests MUST require an authenticated Laravel Sanctum bearer token. All LLM
calls MUST originate from Laravel server-side services, never from Angular or browser code.
OpenRouter credentials, prompts, raw provider responses, and seller or user private data MUST
not be exposed to the client. Every `session_id` MUST be validated as a UUID and bound to
the authenticated user before chat history, slots, complaints, or property context are read.

Rationale: the chatbot handles personal chat history, complaint phone numbers, seller contact
data, and paid API credentials. A strict backend boundary prevents key leakage, session replay,
and cross-user data disclosure.

### II. Deterministic Real Estate Data Grounding
The assistant MUST answer only from authenticated database records, resolved session state, and
explicitly supplied property data. LLM output for intent, location, property type, features,
phone numbers, and property references MUST be treated as untrusted input and passed through
deterministic Laravel validation or resolver services before it affects SQL, stored state, or
returned data. Search ranking MUST remain transparent and explainable through weighted scoring,
not opaque model-generated ordering.

Rationale: real estate users make financial decisions from listing details. Deterministic
resolution, cash-only filtering, active-listing constraints, and explicit ranking rules reduce
false matches, hallucinated details, and silent zero-result failures.

### III. Conversation Safety and Privacy
The system MUST protect users and sellers from prompt injection, XSS, over-sharing, and
unnecessary personal data exposure. Seller-supplied listing fields and user messages MUST be
delimited as untrusted data in prompts. Angular MUST render property titles with interpolation
and sanitize assistant markdown to plain text plus safe links. Seller phone numbers MUST be
looked up only for a resolved property after explicit contact intent, and complaint phone
numbers MUST be normalized and validated before storage.

Rationale: chatbot inputs include seller content, user free text, and model-generated markdown.
Treating all such content as untrusted keeps the LLM, browser, and database from becoming
injection paths or privacy leaks.

### IV. Independent, Testable User Journeys
Every feature MUST be specified and implemented as independently testable user journeys. The
minimum viable path is authenticated property search with slot collection, deterministic slot
resolution, ranked active cash listings, and language-appropriate replies. Additional journeys
such as property details, show-more pagination, property-page context, installment redirect,
complaint handling, and image viewing MUST include clear acceptance scenarios and demo checks
before being considered complete.

Rationale: the plan spans backend services, database behavior, prompts, and Angular rendering.
Independent journeys keep implementation incremental while proving each user-visible behavior
works end to end.

### V. Observable Reliability and Graceful Degradation
The chatbot MUST log intent, extracted state, resolver outcomes, failed searches, LLM failures,
complaint signals, and latency-sensitive provider errors in a form that supports review and
tuning. LLM calls MUST retry once on timeout, non-200 responses, or invalid JSON, then return a
language-aware fallback without losing the user's message or already computed property data.
Ambiguous or unresolved slot values MUST trigger clarification instead of guessing.

Rationale: a free-tier LLM and multilingual real estate input will fail in predictable ways.
Observable deterministic fallbacks preserve trust and make alias, prompt, and reliability tuning
possible after launch.

## Product and Technology Constraints

The implementation MUST follow `plan.md` as the current source of product and architecture
truth unless a later accepted specification amends it. The approved stack is Laravel 12,
Angular, MySQL, Laravel Sanctum, and Qwen3 via OpenRouter. The database language for chatbot
fields is English, while user replies MUST match the user's Arabic or English language and
register.

The chatbot MUST support cash listings only. Installment, down-payment, and monthly-payment
requests MUST route to the installment redirect flow and MUST not create installment search
slots. Property search MUST return only active listings and MUST use server-resolved canonical
location, feature, and property-type values. Bulk search responses MUST not include seller phone
numbers.

Required backend services include session ownership, OpenRouter access, slot extraction,
location resolution, feature resolution, property type resolution, property search, property
scoring, phone validation, and chat log persistence. These services MAY be combined only when
the resulting code remains clear, testable, and aligned with the responsibilities in `plan.md`.

## Development Workflow and Quality Gates

Before implementation, every plan MUST pass a Constitution Check covering authentication,
server-side LLM boundaries, deterministic data grounding, privacy-safe rendering, journey-level
tests, observability, and fallback behavior. Any violation MUST be documented with a concrete
reason and a simpler rejected alternative.

Each feature specification MUST define prioritized user journeys, acceptance scenarios, edge
cases, functional requirements, key data entities, and measurable outcomes. Tasks MUST be
grouped by independently testable user story and include foundational work for migrations,
services, contracts, prompt/schema validation, Angular rendering safety, and monitoring where
relevant.

Validation MUST include the demo flows from `plan.md` that apply to the implemented journey,
including Arabic-to-English extraction, alias resolution, budget fallback, property reference
resolution, installment redirect, complaint flow, phone validation, prompt-injection handling,
LLM failure fallback, and show-more pagination when those capabilities are in scope.

## Governance

This constitution supersedes conflicting implementation habits, generated templates, and ad hoc
task lists. `plan.md` remains the technical execution guide, but any future plan change that
conflicts with these principles MUST amend this constitution first or explicitly document a
temporary exception in the Constitution Check.

Amendments require a written Sync Impact Report, a semantic version decision, and propagation to
affected templates or runtime guidance. MAJOR versions remove or redefine a core principle in a
backward-incompatible way. MINOR versions add principles, sections, or materially expanded
guidance. PATCH versions clarify wording without changing governance meaning.

Compliance MUST be reviewed during specification, planning, task generation, and implementation
review. A feature is not complete until its applicable constitution gates, acceptance scenarios,
and failure/privacy cases have been validated or explicitly deferred with owner-visible risk.

**Version**: 1.0.0 | **Ratified**: 2026-06-20 | **Last Amended**: 2026-06-20
