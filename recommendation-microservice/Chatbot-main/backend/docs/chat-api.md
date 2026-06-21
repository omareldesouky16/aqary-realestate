# Chat API

The chat API is `POST /api/chat` and is protected by Sanctum bearer authentication.

The request body follows `specs/004-phase-3-search-rank-reply/contracts/chat-api.yaml` and keeps the existing
`session_id`, `message`, and optional `context_property_id` inputs.

The response now includes the Phase 2.5 state indicators plus the Phase 3 search payload:

- `search`: search status, result count, shown count, page size, visible references, and budget fallback data.
- `properties`: the current visible ranked result page, limited to 5 items.
- `has_more`: whether retained results remain for show-more requests.
- `min_price_fallback`: the minimum same-scope active cash price when no listing fits the budget window.
- `show_image_offer`: whether the assistant should invite photo viewing for the returned listings.
- `fallback`: whether a friendly fallback was used instead of a normal result path.
# Phase 5 Complaint Handling

`/api/chat` responses include `complaint_case` when a complaint or soft check-in is active. The complaint payload includes the stage, issue summary when captured, phone status, normalized phone only after validation, and reviewable complaint events. Hard complaint turns must not continue search, photo, property-detail, or seller-contact behavior in the same turn.
