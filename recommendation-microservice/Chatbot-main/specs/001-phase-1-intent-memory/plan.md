# Implementation Plan: Phase 1 Intent Detection and Memory

**Branch**: `` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/001-phase-1-intent-memory/spec.md`

## Summary

Implement the first chatbot foundation: authenticated session ownership, intent detection,
last-10-turn memory, structured session state, property reference resolution, installment
redirect, complaint signal classification, new-search reset behavior, and safe fallback handling.
The implementation follows the root `plan.md` architecture: Laravel owns authentication,
session state, provider calls, validation, and persistence; Angular consumes a single chat
contract and renders user-facing responses safely.

## Technical Context

**Language/Version**: PHP 8.3+ with Laravel 12 for backend; TypeScript with Angular for frontend.

**Primary Dependencies**: Laravel Sanctum for bearer-token authentication; OpenRouter using
`qwen/qwen3-235b-a22b:free` for NLU and reply composition; MySQL for chat/session state;
Angular HTTP client and sanitized markdown/link rendering for the widget.

**Storage**: MySQL tables for `chat_sessions` and `chat_logs`; existing property-related tables
are read for shown-property context only when Phase 1 needs a seeded or already-shown property list.

**Testing**: Laravel feature/unit tests for session ownership, intent routing, memory merge,
fallback behavior, and complaint/reset counters; Angular component/service tests for request/response
handling and safe rendering; end-to-end validation scenarios from `quickstart.md`.

**Target Platform**: Authenticated web application with a Laravel API backend and Angular chat widget.

**Project Type**: Web application with backend API, frontend widget, persistent session state,
and third-party LLM integration.

**Performance Goals**: Preserve user-visible chat responsiveness under normal provider latency;
load no more than 10 prior turns per request; return a friendly fallback after one failed retry
instead of leaving the user waiting indefinitely.

**Constraints**: Login is required; all LLM calls and API keys stay server-side; `session_id` must
be UUID-formatted and owner-bound; installments are unsupported and must redirect to cash-only;
seller/user content is untrusted; NLU output must be schema-validated before it updates state.

**Scale/Scope**: Phase 1 covers intent detection, memory, property-reference resolution,
installment redirect, complaint signal classification, reset behavior, and failure handling. Full
slot collection, canonical location/feature/property-type resolution, search ranking, image gallery,
seller phone lookup, and complaint phone collection are later-phase work.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Authenticated AI boundary**: PASS. The plan requires Sanctum auth, UUID validation,
  session ownership binding, and server-side OpenRouter calls only.
- **Deterministic data grounding**: PASS. Phase 1 validates NLU schema and stores structured
  state only after deterministic merge/reset rules; later canonical slot resolution remains out
  of scope but is explicitly preserved as a boundary.
- **Privacy and rendering safety**: PASS. Shown property titles and conversation history are
  treated as untrusted data; seller phone lookup is out of scope for Phase 1 and must not be
  exposed by this feature.
- **Journey-level validation**: PASS. Independent journeys cover search memory, property
  references, installment redirect, complaint signals, and new-search reset.
- **Reliability and observability**: PASS. One retry plus fallback is required for NLU failure;
  intent, state, reset, complaint, and provider-failure outcomes are persisted or logged.
- **Cash-only constraint**: PASS. Installment requests route to redirect and never create
  payment slots.

**Post-Design Re-check**: PASS. `research.md`, `data-model.md`, `contracts/chat-api.yaml`, and
`quickstart.md` preserve the same boundaries and introduce no constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/001-phase-1-intent-memory/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── chat-api.yaml
└── tasks.md
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Http/Controllers/ChatController.php
│   ├── Models/ChatSession.php
│   └── Services/Chat/
│       ├── ChatLogService.php
│       ├── IntentDetectionService.php
│       ├── OpenRouterService.php
│       ├── SessionOwnershipService.php
│       └── SlotExtractor.php
├── database/migrations/
├── routes/api.php
└── tests/
    ├── Feature/Chat/
    └── Unit/Chat/

frontend/
├── src/app/chatbot/
│   ├── chatbot-widget/
│   ├── message-list/
│   ├── message-input/
│   └── chat.service.ts
└── src/app/shared/
```

**Structure Decision**: Use the web-application layout from the root plan. Backend Phase 1 work is
centered on chat routing, ownership, state, LLM boundary, and persistence. Frontend Phase 1 work is
limited to the request/response contract and safe message display needed to validate the journeys.

## Complexity Tracking

No constitution violations or complexity exceptions are required.
