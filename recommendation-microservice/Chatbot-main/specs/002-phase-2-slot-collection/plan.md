# Implementation Plan: Phase 2 Slot Collection

**Branch**: `` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/002-phase-2-slot-collection/spec.md`

## Summary

Implement the next chatbot foundation after Phase 1: deterministic slot-collection progression for authenticated property searches. Phase 2 collects required preferences in order (property type, location, maximum budget), treats numeric budgets without explicit currency as EGP, captures multiple clear values from one message, asks one grouped optional question for area, bedrooms, bathrooms, and features, excludes payment method entirely, preserves already captured values, and asks targeted clarification for unclear required values. The implementation extends the existing chat contract and session state while keeping canonical value resolution and search execution as later-phase dependencies.

## Technical Context

**Language/Version**: PHP 8.3+ with Laravel 12 for backend; TypeScript with Angular for frontend.

**Primary Dependencies**: Laravel Sanctum for authenticated chat requests; existing Phase 1 chat/session services; OpenRouter using `qwen/qwen3-235b-a22b:free` for NLU and reply composition; Angular HTTP client and sanitized chat rendering.

**Storage**: MySQL `chat_sessions` for session ownership and `chat_logs.extracted_data` for accumulated slot-collection state, budget currency default, optional-decline status, last asked slot, and search-readiness markers.

**Testing**: Laravel feature/unit tests for required slot order, multi-slot extraction, EGP default budget handling, grouped optional handling, cash-only redirects, unclear slot clarification, and fallback preservation; Angular component/service tests for awaiting-slot indicators and slot prompt rendering; end-to-end validation scenarios from `quickstart.md`.

**Target Platform**: Authenticated web application with a Laravel API backend and Angular chat widget.

**Project Type**: Web application with backend API, frontend widget, persistent session state, and third-party LLM integration.

**Performance Goals**: Preserve Phase 1 chat responsiveness while adding slot-state decisions; ask no redundant slot questions in clear flows; complete required slot collection in 3 or fewer chatbot questions when the buyer provides clear answers.

**Constraints**: Login is required; `session_id` remains owner-bound; all LLM calls and API keys stay server-side; numeric budgets without explicit currency default to EGP; payment method is never collected; installment requests route to the existing redirect; Phase 2 does not execute search or final canonical resolution; ambiguous required values trigger clarification instead of guessing.

**Scale/Scope**: Phase 2 covers slot collection only: required property type, location, maximum budget; optional area, bedrooms, bathrooms, features; collection progress, optional decline, cash-only boundary, and search-readiness state. Canonical resolution, search/ranking, result cards, show-more pagination, property details, images, seller contact, and complaint phone collection remain later-phase work.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Authenticated AI boundary**: PASS. Phase 2 continues using authenticated chat requests, owner-bound sessions, and server-side provider calls only.
- **Deterministic data grounding**: PASS. Slot values from NLU remain untrusted and are stored only as collection state until deterministic resolution validates them before later search behavior.
- **Privacy and rendering safety**: PASS. Phase 2 introduces no seller phone exposure and preserves untrusted-content handling for prior messages and listing text.
- **Journey-level validation**: PASS. Independent journeys cover required slot ordering, multi-value capture, omitted-currency budget handling, grouped optional preferences, cash-only redirects, and ambiguous-slot clarification.
- **Reliability and observability**: PASS. Existing retry/fallback behavior remains required, and slot-state outcomes are stored for review through session state and chat logs.
- **Cash-only constraint**: PASS. Payment method is excluded from slot collection, and installment/down-payment/monthly-payment mentions route to redirect rather than creating slots.

**Post-Design Re-check**: PASS. `research.md`, `data-model.md`, `contracts/chat-api.yaml`, and `quickstart.md` preserve the same scope boundaries and introduce no constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/002-phase-2-slot-collection/
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

**Structure Decision**: Use the existing web-application layout from Phase 1 and the root plan. Backend Phase 2 work is centered on slot-state progression, NLU schema handling, reply inputs, and persistence. Frontend Phase 2 work is limited to consuming the existing chat response plus slot-readiness indicators needed to render prompts and validation states safely.

## Complexity Tracking

No constitution violations or complexity exceptions are required.
