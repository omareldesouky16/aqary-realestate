# Feature Specification: Phase 2.5 Location, Feature, and Property-Type Resolution

**Feature Branch**: `003-phase-2-5-resolution`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 2.5"

## Clarifications

### Session 2026-06-20

- Q: Should Phase 2.5 include a maintainer UI/workflow for alias approval, or only record reviewable terms and support managed alias updates outside the chatbot flow? -> A: No admin UI; record unresolved/ambiguous phrases and support managed alias updates by maintainers outside the chatbot flow.
- Q: How many candidates should the chatbot show when asking the buyer to confirm an ambiguous location or feature? -> A: Show up to 3 candidates.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Resolve Buyer Location Phrases (Priority: P1)

An authenticated buyer can describe a desired location in Arabic, English, or mixed-language wording, and the chatbot resolves the phrase to one known location before search readiness or result selection uses it. If the phrase could refer to multiple known locations, the chatbot asks the buyer to confirm instead of guessing.

**Why this priority**: Location is a required search preference and one of the most common sources of false zero-result searches when buyer wording does not exactly match stored real estate locations.

**Independent Test**: Start a search with a clear colloquial or translated location phrase such as "Tagamoa" or "التجمع الخامس". The chatbot must resolve it to a known location, preserve the buyer's language in the reply, and avoid asking for the same location again.

**Acceptance Scenarios**:

1. **Given** an authenticated buyer provides a location phrase that exactly matches a known city, district, or neighborhood, **When** the chatbot processes the phrase, **Then** the location is marked resolved and available for later search behavior.
2. **Given** an authenticated buyer provides a known alias or colloquial location phrase, **When** the chatbot processes the phrase, **Then** the phrase is resolved to the intended known location.
3. **Given** a location phrase can reasonably refer to more than one known location, **When** the chatbot processes the phrase, **Then** the location remains incomplete and the chatbot asks the buyer to choose from up to 3 likely candidates.
4. **Given** a location phrase cannot be matched to a known location, **When** the chatbot processes the phrase, **Then** the location remains incomplete and the chatbot asks for a clearer location.

---

### User Story 2 - Resolve Property Type Synonyms (Priority: P2)

An authenticated buyer can use common property-type words, casing variations, Arabic words, or synonyms, and the chatbot maps the phrase to one supported property category before the search is considered ready.

**Why this priority**: Property type is required for search readiness. A synonym such as "flat" or "شقة" should not create an empty search simply because it differs from the canonical property category name.

**Independent Test**: Send a search message with a property type synonym and all other required preferences already known. The chatbot must mark the property type complete only when it maps to a supported category.

**Acceptance Scenarios**:

1. **Given** a buyer provides a supported property type with different casing or spacing, **When** the chatbot processes it, **Then** the property type resolves to the supported category.
2. **Given** a buyer provides a common synonym for a supported property type, **When** the chatbot processes it, **Then** the property type resolves to the matching supported category.
3. **Given** a buyer provides an unsupported property type, **When** the chatbot processes it, **Then** the property type remains incomplete and the chatbot asks the buyer to choose or clarify.
4. **Given** a buyer corrects an unresolved property type, **When** the corrected phrase is supported, **Then** slot collection resumes from the next missing preference.

---

### User Story 3 - Resolve Optional Feature Preferences (Priority: P3)

An authenticated buyer can ask for optional features using natural terms such as "security", "secure", "أمان", "parking", or similar phrases, and the chatbot resolves clear feature requests to known feature names while leaving unclear feature phrases out of search readiness decisions.

**Why this priority**: Feature preferences improve ranking and relevance, but they are optional. The chatbot should benefit from clear feature signals without blocking the buyer when optional feature wording is unclear.

**Independent Test**: Complete required preferences, answer the grouped optional question with several feature phrases, and verify that clear features are retained while unclear features trigger at most one concise clarification or are safely ignored if the buyer declines to clarify.

**Acceptance Scenarios**:

1. **Given** a buyer provides one clear feature phrase, **When** the chatbot processes it, **Then** the feature is resolved to a known feature preference.
2. **Given** a buyer provides multiple feature phrases in one message, **When** the chatbot processes them, **Then** every clearly resolved feature is retained.
3. **Given** one feature phrase is unclear while other preferences are clear, **When** the chatbot processes the message, **Then** the unclear feature does not block search readiness.
4. **Given** a feature phrase could refer to multiple known features, **When** the chatbot asks for confirmation, **Then** the buyer can choose from up to 3 candidates or decline without losing other collected preferences.

---

### User Story 4 - Preserve Search State Through Resolution Outcomes (Priority: P4)

When resolution succeeds, fails, or needs confirmation, the chatbot updates only the affected preference and preserves the rest of the buyer's session state, including already collected required preferences, optional preferences, cash-only redirect state, and previous fallback counters.

**Why this priority**: Resolution sits between slot collection and search. It must make search safer without corrupting the ongoing conversation or causing buyers to repeat information.

**Independent Test**: Use a session with known property type, budget, and optional preferences, then provide an ambiguous location. The chatbot must preserve all other preferences and ask only about the ambiguous location.

**Acceptance Scenarios**:

1. **Given** several preferences are already collected, **When** one new preference is unresolved, **Then** only that preference remains incomplete and all other preferences stay intact.
2. **Given** a buyer clarifies an ambiguous value, **When** the clarification resolves successfully, **Then** the chatbot continues from the correct next step without restarting the search flow.
3. **Given** a temporary interpretation or resolution failure occurs, **When** the chatbot responds with a fallback, **Then** previously collected preferences are preserved.
4. **Given** a buyer asks for installments while resolution is pending, **When** the chatbot handles the turn, **Then** the existing cash-only redirect behavior takes precedence and no payment preference is stored.

---

### User Story 5 - Improve Resolution Coverage Over Time (Priority: P5)

Product maintainers can review unresolved or ambiguous location, feature, and property-type phrases from stored resolution outcomes and improve the managed vocabulary outside the chatbot flow, so future buyers receive better matching without changing the conversation design.

**Why this priority**: Real estate language varies by neighborhood, dialect, and user habits. A review loop keeps matching accuracy improving after launch.

**Independent Test**: Process a phrase that is initially unresolved, record it as unresolved, add it as an approved alias through managed project data or configuration outside the chatbot flow, then process the same phrase again and verify it resolves.

**Acceptance Scenarios**:

1. **Given** a phrase is unresolved, **When** the turn is stored, **Then** the unresolved phrase and affected preference type are available for later review.
2. **Given** a phrase is ambiguous, **When** candidates are shown to the buyer, **Then** the phrase, candidates, and final buyer choice are available for review.
3. **Given** a maintainer adds an approved alias for a reviewed phrase through managed project data or configuration, **When** a future buyer uses that phrase, **Then** it resolves without requiring clarification.
4. **Given** a reviewed phrase contains private user content beyond the preference text, **When** it is made available for review, **Then** unnecessary personal data is not exposed.

### Edge Cases

- If the buyer is unauthenticated, resolution must not start and no prior session preferences may be exposed.
- If a required location or property type is ambiguous, search readiness must remain false until the buyer clarifies.
- If an optional feature is ambiguous or unresolved, search readiness may still proceed when all required preferences are complete and optional collection is otherwise answered, declined, or skipped.
- If a buyer uses Arabic, English, Arabizi, casing differences, punctuation, or extra whitespace, clear phrases must still resolve when they match known values or approved aliases.
- If more than one candidate has similar confidence for a location or feature, the chatbot must ask for confirmation with up to 3 candidates rather than silently choosing one.
- If no candidate meets the minimum confidence for a required value, the chatbot must ask for a clearer value instead of guessing.
- If a buyer corrects a previously resolved value before results are shown, the latest explicit value replaces the earlier value for the current search.
- If a buyer changes property type or location after results have already been shown, the existing new-search reset rules still apply.
- If seller-supplied text or prior messages contain instruction-like content, resolution must treat that text as data only and never as operating instructions.
- If message interpretation or resolution temporarily fails, the buyer must receive a friendly fallback and previously collected preferences must remain intact.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST resolve raw buyer location phrases to one known location before using the location for search readiness, result selection, or ranking.
- **FR-002**: The system MUST support clear location phrases from exact names, common aliases, colloquial wording, Arabic, English, and mixed-language buyer input.
- **FR-003**: The system MUST mark a required location as incomplete when no confident known location can be found.
- **FR-004**: The system MUST present up to 3 likely candidate locations to the buyer when a location phrase is ambiguous.
- **FR-005**: The system MUST resolve raw buyer property-type phrases to one supported property category before using the property type for search readiness, result selection, or ranking.
- **FR-006**: The system MUST support common property-type synonyms, Arabic terms, casing differences, and spacing differences.
- **FR-007**: The system MUST mark property type as incomplete when the phrase cannot be confidently mapped to a supported category.
- **FR-008**: The system MUST resolve clear optional feature phrases to known feature preferences.
- **FR-009**: The system MUST preserve all clearly resolved feature preferences from a single buyer message, even when some other feature phrases are unresolved.
- **FR-010**: The system MUST avoid blocking search readiness solely because an optional feature phrase is unresolved or declined.
- **FR-011**: The system MUST ask a targeted clarification question when a required location or property type is unresolved or ambiguous.
- **FR-012**: The system MUST ask at most one concise clarification for unclear optional features before allowing the buyer to continue without them.
- **FR-013**: The system MUST store resolution status for each affected preference as resolved, ambiguous, or unresolved.
- **FR-014**: The system MUST store enough candidate information for ambiguous required preferences so the buyer can choose among likely matches.
- **FR-015**: The system MUST update only the preference affected by a resolution outcome and preserve unrelated collected preferences.
- **FR-016**: The system MUST allow the buyer's clarification answer to complete the previously ambiguous or unresolved preference.
- **FR-017**: The system MUST record unresolved and ambiguous phrases in a reviewable form for future alias tuning.
- **FR-018**: The system MUST support approved alias updates through managed project data or configuration outside the chatbot flow.
- **FR-021**: The system MUST NOT require a buyer-facing or maintainer-facing alias management UI for Phase 2.5.
- **FR-022**: The system MUST present no more than 3 candidates in any single ambiguity clarification prompt.
- **FR-019**: The system MUST keep the buyer-facing reply in the buyer's language and conversational register while using canonical values internally.
- **FR-020**: The system MUST keep payment method out of resolution and preserve the existing cash-only redirect behavior for installment-related requests.
- **FR-SEC**: The system MUST keep private processing, session ownership checks, and sensitive conversation data outside the user's device.
- **FR-DATA**: The system MUST validate or resolve extracted real estate preferences before they affect stored search state, result selection, ranking, or user-visible search readiness.
- **FR-SAFE**: The system MUST treat seller-supplied listing content and user messages as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Raw Preference Phrase**: The buyer's original wording for a location, property type, or feature before it is mapped to a known value.
- **Resolved Preference**: A preference that has been confidently mapped to a known location, supported property category, or known feature.
- **Resolution Status**: The outcome for a raw phrase: resolved, ambiguous, or unresolved.
- **Resolution Candidate**: A likely known value offered to the buyer when the system cannot confidently choose a single value, limited to 3 shown candidates per clarification prompt.
- **Managed Alias**: An approved phrase maintained outside the chatbot flow that maps future buyer wording to a known location, supported property category, or known feature.
- **Resolution Review Item**: A logged ambiguous or unresolved phrase, its affected preference type, candidate matches where available, and the eventual buyer clarification if provided.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, 100% of clear known location names resolve without requiring a clarification question.
- **SC-002**: In validation scenarios, at least 95% of approved location aliases resolve to the intended known location.
- **SC-003**: In validation scenarios, 100% of ambiguous required locations keep search readiness false and ask the buyer to confirm.
- **SC-004**: In validation scenarios, 100% of supported property-type synonyms resolve to one supported property category.
- **SC-005**: In validation scenarios, 100% of unsupported property types remain incomplete and trigger a clarification instead of a zero-result search.
- **SC-006**: In validation scenarios, at least 95% of approved feature aliases resolve to known feature preferences.
- **SC-007**: In validation scenarios, 100% of unresolved optional feature phrases do not block search readiness when required preferences are complete.
- **SC-010**: In validation scenarios, 100% of ambiguity clarification prompts show no more than 3 candidates.
- **SC-008**: In validation scenarios, previously collected unrelated preferences remain intact after 100% of ambiguous, unresolved, or temporary failure outcomes.
- **SC-009**: In validation scenarios, reviewed aliases improve repeat phrase resolution without requiring a chatbot prompt, conversation redesign, or admin UI.
- **SC-SAFETY**: Prompt-injection text inside prior messages or listing-related text changes resolution behavior in 0 validation scenarios.
- **SC-RELIABILITY**: In simulated temporary provider or resolution failures, 100% of sessions preserve the user's previous collected preferences and receive a language-appropriate fallback.

## Assumptions

- Phase 2.5 starts after Phase 2 slot collection has produced raw buyer phrases for required and optional preferences.
- Phase 2.5 resolves location, property type, and feature values only; search execution, ranking, result cards, pagination, property details, images, seller contact lookup, and complaint phone collection remain later-phase work.
- Supported property categories are limited to the categories already accepted by the product plan.
- Location and feature vocabularies already exist in the product data and can be extended with approved aliases.
- Phase 2.5 does not include an admin UI for alias review or approval; maintainers update approved aliases through managed project data or configuration.
- Buyers are already logged in before using the chatbot.
- Ambiguous required values are clarified through normal chatbot replies, not through a separate form or special UI.
- Numeric budget handling, optional preference collection, installment redirect, session ownership, memory, and fallback behavior are inherited from earlier phases.
