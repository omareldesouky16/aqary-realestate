# Implementation Plan: Phase 3 Search, Rank, and Reply

**Branch**: `004-phase-3-search-rank-reply` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/004-phase-3-search-rank-reply/spec.md`

## Summary

Implement the search execution layer that turns resolved Phase 2.5 criteria into ranked active cash listings, stable paginated result context, budget fallback replies, and grounded buyer-facing result summaries. Phase 3 extends the authenticated Laravel chat pipeline with deterministic search, scoring, result-state persistence, and reviewable outcomes while keeping OpenRouter reply composition server-side and rendering only sanitized listing facts in Angular.

## Technical Context

**Language/Version**: PHP 8.3+ with Laravel 12 for backend; TypeScript with Angular for frontend.

**Primary Dependencies**: Laravel Sanctum for authenticated chat requests; existing Phase 1, Phase 2, and Phase 2.5 chat/session/resolution services; OpenRouter using `qwen/qwen3-235b-a22b:free` for reply composition only; deterministic Laravel search and scoring services; MySQL listing/session/chat-log storage; Angular HTTP client and sanitized chat rendering.

**Storage**: MySQL `chat_sessions` for ownership; `chat_logs.extracted_data` for accumulated criteria, ranked result context, visible page references, pagination cursor, fallback outcome, and reviewable search events; existing listing tables for active cash listing data, pricing, location, property type, features, promotion signal, cover image, and seller contact fields.

**Testing**: Laravel feature/unit tests for search readiness, active cash filtering, 20% budget window, relevance-first ranking, minor promotion boost, first-page limit, show-more pagination, visible reference updates, budget fallback, no-listings fallback, state reset/refinement behavior, prompt-injection handling, private seller contact exclusion, reply-failure state preservation, and search event logging; Angular service/component tests for ranked result payloads, safe links, image preview indicators, missing-field rendering, show-more state, and sanitized title/markdown display; end-to-end validation scenarios from `quickstart.md`.

**Target Platform**: Authenticated web application with a Laravel API backend and Angular chat widget.

**Project Type**: Web application with backend API, frontend widget, persistent session state, deterministic database search, and third-party LLM integration for language-appropriate replies.

**Performance Goals**: First page of normal search results appears within 3 seconds after search readiness in validation scenarios; show-more requests reuse retained ranked result order without reranking; no more than 20 ranked candidates are retained for result browsing; each result page contains up to 5 listings.

**Constraints**: Login is required; `session_id` remains owner-bound; all provider calls and credentials stay server-side; property type and location must be resolved to canonical values before search; buyer budget must be present before search; only active cash listings are eligible; maximum budget allows listings up to 20% above the stated budget; promotion can only break close relevance ties; seller phone numbers stay out of bulk search replies; seller-supplied listing text is untrusted for prompts and rendering; installment requests keep cash-only redirect precedence.

**Scale/Scope**: Phase 3 covers active cash listing search, deterministic scoring, result-state persistence, show-more pagination, budget fallback, grounded bulk result replies, cover image preview indicators, and reviewable search outcomes. Property-detail galleries, seller contact lookup, complaint phone collection, alias management UI, and full property detail flows remain out of scope except where existing earlier-phase behavior must be preserved.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Authenticated AI boundary**: PASS. Search and result pagination are entered only through authenticated chat requests, owner-bound sessions, and server-side Laravel services; OpenRouter remains server-side for reply wording.
- **Deterministic data grounding**: PASS. Search readiness depends on resolved Laravel state, SQL uses canonical IDs and validated numeric criteria, and ranking is an explainable weighted score instead of model-generated ordering.
- **Privacy and rendering safety**: PASS. Seller fields are treated as untrusted display data, seller phone numbers are excluded from bulk result responses, and Angular must render safe links/images without raw unsafe markup.
- **Journey-level validation**: PASS. Independent journeys cover ranked results, low-budget fallback, show-more pagination, result-context reset/refinement, and grounded safe replies.
- **Reliability and observability**: PASS. Search outcomes, result count, no-result reason, minimum-price fallback, show-more exhaustion, ranking diagnostics, and reply failures are logged or persisted in reviewable chat state.
- **Cash-only constraint**: PASS. Installment/down-payment/monthly-payment requests keep the existing redirect path and never become search filters.

**Post-Design Re-check**: PASS. `research.md`, `data-model.md`, `contracts/chat-api.yaml`, and `quickstart.md` preserve the authenticated server-side boundary, deterministic ranking, privacy-safe result payloads, observable fallbacks, and cash-only scope.

## Project Structure

### Documentation (this feature)

```text
specs/004-phase-3-search-rank-reply/
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
|       |-- PropertySearchService.php
|       |-- PropertyScoringService.php
|       |-- SearchResultStateService.php
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
|   |-- chat.service.ts
|   |-- chat.types.ts
|   `-- safe-chat-markdown.pipe.ts
`-- src/app/shared/
```

**Structure Decision**: Use the existing Laravel API and Angular chat-widget layout from Phases 1 through 2.5. Backend Phase 3 work adds focused search, scoring, and result-state services around the current chat controller pipeline. Frontend work extends existing chat response types and message-list rendering to display safe ranked result summaries, image-preview indicators, and show-more state.

## Complexity Tracking

No constitution violations or complexity exceptions are required.
