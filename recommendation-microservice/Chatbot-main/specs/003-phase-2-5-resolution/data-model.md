# Data Model: Phase 2.5 Location, Feature, and Property-Type Resolution

## RawPreferencePhrase

Represents the buyer's original wording before deterministic resolution.

### Fields

- `preference_type`: `location`, `propertyType`, or `features`.
- `raw_text`: Original buyer phrase used for resolution.
- `language_hint`: Last known Arabic, English, Arabizi, or mixed-language signal when available.
- `source_turn`: User turn where the phrase was captured.
- `normalized_text`: Lowercased, whitespace-trimmed, punctuation-normalized representation used by resolver services.

### Validation Rules

- `raw_text` MUST be treated as untrusted user input.
- `preference_type` MUST be one of the supported resolver types.
- Private or unrelated message content MUST NOT be copied into review records beyond the preference phrase.

## ResolvedPreference

Represents a raw phrase confidently mapped to a known value.

### Fields

- `preference_type`: `location`, `propertyType`, or `features`.
- `canonical_id`: Internal known location, property category, or feature identifier when available.
- `canonical_name`: Internal canonical display name.
- `raw_text`: Phrase that produced the resolution.
- `resolved_by`: `exact`, `alias`, `synonym`, `normalization`, or `clarification`.
- `confidence`: Resolver confidence or deterministic match tier.
- `source_turn`: User turn where resolution completed.

### Relationships

- Belongs to the current `SessionState`.
- May be produced from one `RawPreferencePhrase`.
- May correspond to a `ManagedAlias`.

### Validation Rules

- Required `location` and `propertyType` values MUST be resolved before search readiness can be true.
- `canonical_id` and `canonical_name` MUST come from trusted product data or managed aliases.
- Latest explicit buyer correction replaces the earlier resolved value before results are shown.

## ResolutionOutcome

Represents the latest resolver result for one affected preference.

### Fields

- `preference_type`: `location`, `propertyType`, or `features`.
- `status`: `resolved`, `ambiguous`, or `unresolved`.
- `raw_text`: Phrase that was evaluated.
- `resolved_value`: `ResolvedPreference` when status is `resolved`.
- `candidates`: Up to 3 `ResolutionCandidate` records when status is `ambiguous`.
- `clarification_prompted`: Boolean indicating whether the buyer was asked to clarify.
- `optional_blocking`: Always false for optional features.
- `created_at_turn`: Turn where the outcome was created.

### State Rules

```text
raw phrase
  -> resolved      when one trusted value meets the confidence rule
  -> ambiguous     when multiple values are plausible and no single value should be chosen
  -> unresolved    when no candidate meets the minimum confidence rule
```

## ResolutionCandidate

Represents a likely known value shown to the buyer for confirmation.

### Fields

- `canonical_id`: Internal candidate identifier.
- `canonical_name`: Candidate display name.
- `preference_type`: `location`, `propertyType`, or `features`.
- `match_reason`: `exact`, `alias`, `synonym`, `similarity`, or `translation`.
- `confidence`: Candidate confidence or match tier.
- `display_order`: Candidate order in the clarification prompt.

### Validation Rules

- No clarification prompt may show more than 3 candidates.
- Required candidate choices MUST keep search readiness false until the buyer confirms.
- Candidate display text MUST be rendered safely by the frontend.

## ManagedAlias

Represents an approved phrase maintained outside the chatbot flow.

### Fields

- `alias_text`: Approved phrase or synonym.
- `normalized_alias_text`: Normalized phrase used by resolver services.
- `preference_type`: `location`, `propertyType`, or `features`.
- `canonical_id`: Known value targeted by the alias.
- `canonical_name`: Canonical value targeted by the alias.
- `language_hint`: Optional language or dialect context.
- `active`: Boolean indicating whether the alias can be used.

### Validation Rules

- Alias updates are managed outside the chatbot flow.
- Inactive aliases MUST NOT resolve future buyer phrases.
- Alias targets MUST reference supported product data.

## ResolutionReviewItem

Represents a reviewable unresolved or ambiguous phrase for maintainer tuning.

### Fields

- `id`: Unique review item identifier.
- `session_id`: Owning session UUID or anonymized/session-scoped reference.
- `user_id`: Authenticated user identifier when needed for ownership/audit, not exposed in maintainer exports unless required.
- `preference_type`: `location`, `propertyType`, or `features`.
- `raw_text`: Minimal phrase that failed or needed clarification.
- `status`: `ambiguous`, `unresolved`, `resolved_after_clarification`, or `alias_added`.
- `candidates_snapshot`: Candidate names/IDs shown at the time of ambiguity, limited to 3.
- `buyer_choice`: Canonical value selected by the buyer, when provided.
- `created_at`: Creation timestamp.
- `reviewed_at`: Timestamp when maintainers process the item outside the chatbot flow.

### Validation Rules

- Review items MUST NOT expose unrelated private message content.
- Ambiguous phrases MUST store candidates and eventual buyer choice when available.
- Unresolved phrases MUST remain reviewable for future alias tuning.

## SessionState Extension

Adds resolution-specific fields to the existing chat session state.

### Fields

- `resolution`: Object keyed by `location`, `propertyType`, and `features`.
- `pending_resolution_clarification`: Active clarification request for a required value or optional feature.
- `resolved_preferences`: Canonical values available to later search phases.
- `resolution_review_refs`: References to review items created from the current session.

### Validation Rules

- Updating one resolution outcome MUST NOT clear unrelated collected preferences, counters, redirect state, or fallback state.
- Temporary resolver failure MUST preserve previously collected and resolved values.
- Installment redirect state takes precedence over pending resolution prompts.

## State Transitions

```text
raw Phase 2 slot phrase
  -> deterministic resolver
  -> resolved preference stored
  -> search readiness may advance for required values
```

```text
raw required phrase
  -> ambiguous or unresolved
  -> targeted clarification prompt
  -> buyer confirms or corrects
  -> resolved preference stored
  -> slot collection resumes from next missing preference
```

```text
raw optional feature phrase
  -> resolved features retained
  -> ambiguous/unresolved feature logged
  -> at most one concise clarification
  -> search readiness remains governed by required preferences
```

```text
installment request during resolution
  -> cash-only redirect
  -> prior resolution and slot state preserved
  -> normal collection resumes only if buyer accepts cash listings
```
