# Implementation Plan: Phase 4 Property Details

**Branch**: `005-phase-4-property-details` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/005-phase-4-property-details/spec.md`

## Summary

Implement property-detail follow-ups on top of the Phase 3 ranked result context. Phase 4 resolves buyer references to one current visible property or valid property-page context, answers only from available listing facts, supports explicit photo gallery requests, and returns seller contact only for explicit single-property contact requests. The work extends the authenticated Laravel chat pipeline with deterministic property reference/detail services, single-property gallery/contact retrieval, reviewable detail outcomes, and Angular rendering for detail, gallery, and contact-safe responses.

## Technical Context

**Language/Version**: PHP 8.3+ with Laravel 12 for backend; TypeScript with Angular for frontend.

**Primary Dependencies**: Laravel Sanctum for authenticated chat requests; existing Phase 1 through Phase 3 chat/session/resolution/search/result-state services; OpenRouter using `qwen/qwen3-235b-a22b:free` for reply composition only; deterministic Laravel property reference/detail/gallery/contact services; MySQL listing/session/chat-log storage; Angular HTTP client and sanitized chat rendering.

**Storage**: MySQL `chat_sessions` for ownership; `chat_logs.extracted_data` for accumulated criteria, current visible result references, optional property-page context, resolved property detail context, detail/photo/contact outcomes, and fallback events; existing listing tables for active listing facts, ordered image/gallery records, and seller contact fields.

**Testing**: Laravel feature/unit tests for owner-bound property reference resolution, current-page positional references, title references, stale/ambiguous reference clarification, property-page context, missing detail handling, photo gallery payloads, no-photo fallback, explicit contact gating, inactive listing contact denial, prompt-injection handling, reply failure preservation, and detail outcome logging; Angular service/component tests for detail response payloads, gallery rendering, no raw image paths in conversational text, contact-safe display, sanitized seller text, and property-page context request support; end-to-end validation scenarios from `quickstart.md`.

**Target Platform**: Authenticated web application with a Laravel API backend and Angular chat widget.

**Project Type**: Web application with backend API, frontend widget, persistent session state, deterministic database grounding, and third-party LLM integration for language-appropriate replies.

**Performance Goals**: Property-detail replies appear within 3 seconds for normal follow-up questions after a property is resolved; reference resolution uses the retained current visible page or valid page context without re-running ranked search; photo/contact retrieval is single-property only.

**Constraints**: Login is required; `session_id` remains owner-bound; all provider calls and credentials stay server-side; property references must resolve to exactly one current visible property or valid property-page context before details, photos, contact lookup, state mutation, or user-visible readiness are affected; seller phone numbers appear only in an explicit single-property contact response and are not retained in reusable result context; seller-supplied listing text and image metadata are untrusted; installment requests keep cash-only redirect precedence.

**Scale/Scope**: Phase 4 covers property-detail answers for current visible results and valid property-page context, unresolved-reference clarification, full-gallery viewing on request, explicit seller contact for one resolved active property, state supersession between page context and search context, and reviewable detail outcomes. New search/ranking behavior, complaint phone collection, seller management, public property detail pages, and payment/installment support remain out of scope except where existing earlier-phase behavior must be preserved.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Authenticated AI boundary**: PASS. Property details, photos, and contact flows remain behind authenticated chat requests with owner-bound `session_id`; OpenRouter remains server-side for reply wording only.
- **Deterministic data grounding**: PASS. Property references are resolved by Laravel services against current visible result state or validated page context before any property facts, galleries, contact lookup, or persisted outcomes are used.
- **Privacy and rendering safety**: PASS. Seller/user content and image metadata are untrusted; seller phone lookup is explicit, single-property only, and excluded from reusable result context; Angular rendering must sanitize text, links, and gallery metadata.
- **Journey-level validation**: PASS. Independent journeys cover shown-property detail answers, ambiguous reference clarification, photo gallery requests, explicit seller contact, and first-turn property-page context.
- **Reliability and observability**: PASS. Detail outcomes, unresolved references, photo/contact outcomes, reply failures, and preserved safe property context are reviewable through chat state or logs.
- **Cash-only constraint**: PASS. Installment/down-payment/monthly-payment questions keep the existing redirect path and never create installment-specific property facts or filters.

**Post-Design Re-check**: PASS. `research.md`, `data-model.md`, `contracts/chat-api.yaml`, and `quickstart.md` preserve authenticated server-side processing, deterministic single-property grounding, contact privacy, safe gallery rendering, observable fallbacks, and cash-only redirect precedence.

## Project Structure

### Documentation (this feature)

```text
specs/005-phase-4-property-details/
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
|   |-- Models/ChatLog.php
|   `-- Services/Chat/
|       |-- ChatLogService.php
|       |-- IntentDetectionService.php
|       |-- OpenRouterService.php
|       |-- PropertyDetailService.php
|       |-- PropertyGalleryService.php
|       |-- PropertyReferenceResolver.php
|       |-- SellerContactService.php
|       |-- SearchResultStateService.php
|       `-- SessionOwnershipService.php
|-- database/migrations/
|-- routes/api.php
`-- tests/
    |-- Feature/Chat/
    |-- Support/
    `-- Unit/Chat/

frontend/
|-- src/app/chatbot/
|   |-- chatbot-widget/
|   |-- message-list/
|   |-- message-input/
|   |-- chat.service.ts
|   |-- chat.types.ts
|   `-- safe-chat-markdown.pipe.ts
`-- src/app/shared/
```

**Structure Decision**: Use the existing Laravel API and Angular chat-widget layout from earlier phases. Backend Phase 4 adds focused property reference, detail, gallery, and seller contact services around the current chat controller pipeline. Frontend work extends existing chat response types and message-list rendering to display grounded property details, gallery payloads, and explicit contact responses without exposing contact in bulk or reusable result state.

## Complexity Tracking

No constitution violations or complexity exceptions are required.
