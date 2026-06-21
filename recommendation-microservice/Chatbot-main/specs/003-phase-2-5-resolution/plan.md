# Implementation Plan: Phase 2.5 Location, Feature, and Property-Type Resolution

**Branch**: `003-phase-2-5-resolution` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/003-phase-2-5-resolution/spec.md`

## Summary

Implement the resolver layer that turns raw Phase 2 slot phrases into canonical real estate values before search readiness, result selection, or ranking can use them. Phase 2.5 resolves buyer location phrases, property-type synonyms, and optional feature phrases from Arabic, English, Arabizi, and mixed-language input; asks targeted clarification when required values are ambiguous or unsupported; preserves unrelated session state; and records unresolved or ambiguous phrases for maintainer review without adding an admin UI.

## Technical Context

**Language/Version**: PHP 8.3+ with Laravel 12 for backend; TypeScript with Angular for frontend.

**Primary Dependencies**: Laravel Sanctum for authenticated chat requests; existing Phase 1 and Phase 2 chat/session services; OpenRouter using `qwen/qwen3-235b-a22b:free` for extraction and reply composition; Laravel resolver services for deterministic matching; MySQL-backed managed aliases; Angular HTTP client and sanitized chat rendering.

**Storage**: MySQL `chat_sessions` for ownership; `chat_logs.extracted_data` for accumulated slot and resolution state; managed alias data for location, property type, and feature phrase mappings; reviewable resolution outcomes for unresolved and ambiguous phrases.

**Testing**: Laravel feature/unit tests for exact-name resolution, alias resolution, ambiguity handling, unsupported values, clarification completion, state preservation, optional-feature behavior, review logging, cash-only precedence, and resolver failure fallback; Angular service/component tests for candidate prompts and resolution payload handling; end-to-end validation scenarios from `quickstart.md`.

**Target Platform**: Authenticated web application with a Laravel API backend and Angular chat widget.

**Project Type**: Web application with backend API, frontend widget, persistent session state, and third-party LLM integration.

**Performance Goals**: Resolve clear known values without extra chatbot turns; show no more than 3 candidates for any ambiguity prompt; preserve Phase 1/2 responsiveness while using deterministic resolver lookups; keep required-value ambiguity from triggering zero-result searches.

**Constraints**: Login is required; `session_id` remains owner-bound; all provider calls and credentials stay server-side; seller and user text is untrusted; canonical values must be resolved before search readiness or ranking uses them; optional features may not block readiness; installment requests keep the existing cash-only redirect precedence; Phase 2.5 does not include buyer-facing or maintainer-facing alias management UI.

**Scale/Scope**: Phase 2.5 covers resolution only for location, property type, and optional features, including candidates, clarification, state merge rules, review logging, and managed alias consumption. Search execution, ranking, result cards, pagination, property details, images, seller contact lookup, complaint phone collection, and alias-management UI remain out of scope.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Authenticated AI boundary**: PASS. Resolution is entered only through authenticated chat requests, owner-bound sessions, and server-side Laravel services.
- **Deterministic data grounding**: PASS. LLM-extracted phrases remain untrusted until Laravel resolver services map them to canonical location, property type, or feature records.
- **Privacy and rendering safety**: PASS. Resolution review records store only preference text and resolver metadata needed for tuning; seller/user text remains untrusted in prompts and display.
- **Journey-level validation**: PASS. Independent journeys cover clear location aliases, ambiguous required values, property-type synonyms, optional feature resolution, state preservation, and alias-review improvement.
- **Reliability and observability**: PASS. Resolver outcomes, candidates, unresolved phrases, buyer choices, and temporary failures are persisted or logged for review while preserving prior state.
- **Cash-only constraint**: PASS. Installment/down-payment/monthly-payment requests keep the existing redirect path and never become resolution or payment slots.

**Post-Design Re-check**: PASS. `research.md`, `data-model.md`, `contracts/chat-api.yaml`, and `quickstart.md` preserve the same boundaries and introduce no constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/003-phase-2-5-resolution/
|-- plan.md
|-- research.md
|-- data-model.md
|-- quickstart.md
|-- contracts/
|   `-- chat-api.yaml
`-- tasks.md
```

### Source Code (repository root)

```text
backend/
|-- app/
|   |-- Http/Controllers/ChatController.php
|   |-- Models/ChatSession.php
|   |-- Models/ResolutionReviewItem.php
|   `-- Services/Chat/
|       |-- ChatLogService.php
|       |-- FeatureResolutionService.php
|       |-- IntentDetectionService.php
|       |-- LocationResolutionService.php
|       |-- OpenRouterService.php
|       |-- PropertyTypeResolutionService.php
|       |-- ResolutionStateService.php
|       |-- SessionOwnershipService.php
|       `-- SlotExtractor.php
|-- database/migrations/
|-- routes/api.php
`-- tests/
    |-- Feature/Chat/
    `-- Unit/Chat/

frontend/
|-- src/app/chatbot/
|   |-- chatbot-widget/
|   |-- message-list/
|   |-- message-input/
|   `-- chat.service.ts
`-- src/app/shared/
```

**Structure Decision**: Use the existing web-application layout from Phase 1 and Phase 2. Backend Phase 2.5 work adds focused resolver and review-state services around the existing chat state pipeline. Frontend work is limited to consuming candidate/clarification data through the chat contract and rendering safe candidate prompts.

## Complexity Tracking

No constitution violations or complexity exceptions are required.
