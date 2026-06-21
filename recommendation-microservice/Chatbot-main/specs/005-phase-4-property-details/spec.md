# Feature Specification: Phase 4 Property Details

**Feature Branch**: `005-phase-4-property-details`

**Created**: 2026-06-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 4"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Answer Details About a Shown Property (Priority: P1)

An authenticated buyer who has already received search results can ask follow-up questions about one of the visible properties and receive a grounded answer about that specific property. The buyer can refer to the property by position, title, or obvious contextual wording, and the reply stays in the buyer's language and conversational register.

**Why this priority**: Search results become useful only when buyers can inspect and compare individual properties. This is the minimum viable Phase 4 journey after ranked search.

**Independent Test**: Show a page of search results, ask about "the first one" or a partial title, and verify the chatbot answers only from that property's available data while preserving the current conversation language.

**Acceptance Scenarios**:

1. **Given** an authenticated buyer has a current visible result page, **When** the buyer asks about "the first one", **Then** the chatbot answers about the first property in the current visible page.
2. **Given** an authenticated buyer refers to a shown property by a recognizable part of its title, **When** the reference resolves to one property, **Then** the chatbot answers about that property and records which property was used.
3. **Given** a requested detail is present for the resolved property, **When** the buyer asks for it, **Then** the chatbot includes that detail without adding unrelated facts.
4. **Given** a requested detail is missing for the resolved property, **When** the buyer asks for it, **Then** the chatbot says that information is not available and offers an appropriate next step instead of guessing.

---

### User Story 2 - Clarify Ambiguous Property References (Priority: P2)

When the buyer asks about a property but the reference cannot be resolved to exactly one shown property, the chatbot asks a concise clarification question and shows the current property options again.

**Why this priority**: Ambiguous follow-up answers can mislead buyers. Clarification protects trust and keeps the conversation anchored to the correct listing.

**Independent Test**: Show several results, ask an ambiguous question such as "tell me about that apartment", and verify the chatbot asks which shown property the buyer means without inventing a property or using an old page.

**Acceptance Scenarios**:

1. **Given** multiple properties are visible, **When** the buyer uses an unclear property reference, **Then** the chatbot asks the buyer to choose from the current visible properties.
2. **Given** no search results or property context exists, **When** the buyer asks about "the first one", **Then** the chatbot asks for search preferences or a specific property rather than fabricating a result.
3. **Given** a new results page was shown after "show more", **When** the buyer says "the second one", **Then** the reference applies to the current page, not an older page.
4. **Given** the buyer answers a clarification with a numbered choice or title, **When** that choice resolves to one visible property, **Then** the chatbot continues the property-detail answer for that property.

---

### User Story 3 - Show Photos for a Resolved Property (Priority: P3)

After a property is resolved, the buyer can ask to see photos and receive that property's image gallery when images are available. The chatbot's text invites the buyer to view the photos but does not present raw image paths as conversational facts.

**Why this priority**: Photos are a natural next step after buyers compare search results. This journey increases confidence without expanding into full property-detail page behavior.

**Independent Test**: Ask to see photos for a shown property that has images and verify the response displays only that property's photos in order; repeat with a property that has no gallery and verify the chatbot explains that no photos are currently available.

**Acceptance Scenarios**:

1. **Given** a buyer asks to see photos for a resolved shown property, **When** that property has gallery images, **Then** the buyer sees the ordered gallery for that property.
2. **Given** a buyer asks to see photos using a positional reference, **When** the reference resolves to a current visible property, **Then** the photos belong only to that property.
3. **Given** a resolved property has no gallery images, **When** the buyer asks for photos, **Then** the chatbot says photos are not currently available and offers another useful next step.
4. **Given** seller-supplied image metadata or titles contain unsafe text, **When** photos are shown, **Then** that text is treated as inert display data and does not change assistant behavior or unsafe rendering.

---

### User Story 4 - Provide Seller Contact on Explicit Single-Property Request (Priority: P4)

When a buyer explicitly asks for seller contact information for a resolved property, the chatbot can provide the seller phone for that one property only. Seller contact remains private during bulk search, general detail answers, and ambiguous property references.

**Why this priority**: Contacting the seller is high value, but it is privacy-sensitive. It must happen only after the buyer clearly asks for contact for one property.

**Independent Test**: Ask for the seller phone for the third shown property and verify only that property's contact is returned for that turn; ask a general detail question and verify seller phone is not included.

**Acceptance Scenarios**:

1. **Given** a buyer explicitly asks for seller contact for a resolved property, **When** the property is active and contact is available, **Then** the chatbot provides the contact for that single property.
2. **Given** a buyer asks a normal detail question without requesting contact, **When** the chatbot answers, **Then** seller phone is not included.
3. **Given** a buyer asks for contact but the property reference is ambiguous, **When** the message is handled, **Then** the chatbot asks which property the buyer means before sharing contact.
4. **Given** seller contact is unavailable for the resolved property, **When** the buyer asks for it, **Then** the chatbot says contact is not currently available and offers to continue helping with available listing details.

---

### User Story 5 - Start Details From a Property Page Context (Priority: P5)

When the chatbot is opened from a property detail page, the first buyer question can be scoped to that page's property even before search results have been shown in the chat. The chatbot still follows the same grounding, privacy, and photo/contact rules as normal property-detail follow-ups.

**Why this priority**: Buyers may enter the chat while already viewing a property. Supporting that context reduces friction while preserving the same safety boundaries.

**Independent Test**: Open the chatbot with an existing property context, ask "does it have parking?", and verify the chatbot answers about the page property without requiring a search first.

**Acceptance Scenarios**:

1. **Given** the chatbot starts with a valid property page context, **When** the buyer asks a detail question, **Then** the chatbot answers about that context property.
2. **Given** the chatbot starts with a property page context, **When** the buyer asks for photos, **Then** the chatbot shows that property's gallery when available.
3. **Given** the chatbot starts with a property page context, **When** the buyer explicitly asks for seller contact, **Then** the contact rule applies to that single property only.
4. **Given** the property page context is unavailable, inactive, or not accessible, **When** the buyer asks a detail question, **Then** the chatbot explains that it cannot access that property and offers to search or clarify.

### Edge Cases

- If the buyer is unauthenticated, property details, photos, and contact information must not be returned.
- If there is no current visible result page and no valid property page context, positional references must not resolve.
- If the buyer references a property from an older result page after a newer page is visible, the chatbot must use the current visible page or ask for clarification.
- If a property becomes inactive after it was shown, seller contact must not be returned and the buyer must be told the listing may no longer be available.
- If a requested field is missing, null, or not included for the resolved property, the chatbot must not infer or estimate it.
- If a buyer asks for seller contact during a bulk result reply or without resolving one property, the chatbot must ask which property the buyer means before sharing contact.
- If a buyer asks for photos for an ambiguous or missing property reference, the chatbot must clarify before showing any gallery.
- If seller-supplied text contains prompt-injection instructions, HTML, script-like content, or unsafe markdown, it must be treated as inert listing text.
- If reply generation temporarily fails after property details or photos are resolved, the buyer must receive a friendly fallback and any safe resolved property payload must remain available.
- If the buyer asks about installments, down payment, or monthly payment during a property-detail flow, the existing cash-only redirect must take precedence and no installment-specific property details should be invented.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST answer property-detail questions only for an authenticated buyer with either a resolved current visible property reference or a valid property page context.
- **FR-002**: The system MUST resolve positional references such as "first", "second", "third", and "last" against the current visible result page.
- **FR-003**: The system MUST resolve clear title-based references only when they identify exactly one property from the current visible page or valid page context.
- **FR-004**: The system MUST ask a clarification question when a property reference is missing, ambiguous, stale, or cannot be matched.
- **FR-005**: The system MUST repeat the current property choices in a concise numbered form when asking the buyer to clarify an unresolved property reference.
- **FR-006**: The system MUST answer only from details available for the resolved property.
- **FR-007**: The system MUST state when requested property information is unavailable rather than inventing, estimating, or using general market knowledge.
- **FR-008**: The system MUST preserve the buyer's language and conversational register in property-detail replies.
- **FR-009**: The system MUST support photo requests for a resolved property and return only that property's available photos.
- **FR-010**: The system MUST explain when a resolved property has no photos currently available.
- **FR-011**: The system MUST offer photo viewing as a next step after relevant property-detail replies when photos may help the buyer continue.
- **FR-012**: The system MUST provide seller contact only when the buyer explicitly asks for contact for one resolved property.
- **FR-013**: The system MUST NOT include seller phone numbers in normal property-detail replies, photo replies, ambiguous-reference replies, or bulk search results.
- **FR-014**: The system MUST NOT retain seller contact in reusable result context after the contact turn.
- **FR-015**: The system MUST avoid sharing seller contact for inactive, unavailable, or unresolved properties.
- **FR-016**: The system MUST support the first chat turn being scoped to a valid property page context.
- **FR-017**: The system MUST clear or supersede property page context when the buyer starts a new search or when later visible search results define the active reference context.
- **FR-018**: The system MUST keep current visible result references stable until a new result page, new search, or valid property page context replaces them.
- **FR-019**: The system MUST record property-detail outcomes, unresolved reference outcomes, photo outcomes, and explicit contact outcomes in a reviewable form.
- **FR-020**: The system MUST keep full-gallery viewing separate from bulk search result previews.
- **FR-SEC**: The system MUST keep private processing, session ownership checks, seller contact lookup, and contact data outside the user's device except for the one explicitly requested single-property contact response.
- **FR-DATA**: The system MUST use only resolved, validated property references before property details, photos, contact lookup, stored state, or user-visible readiness are affected.
- **FR-SAFE**: The system MUST treat seller-supplied listing content, image metadata, and user messages as untrusted input in assistant instructions and user-facing display.

### Key Entities *(include if feature involves data)*

- **Property Reference**: The buyer's mention of a property by position, title, current context, or explicit identifier that must resolve to one property before details are shown.
- **Resolved Property Detail**: The safe set of available facts for one property, such as title, price, area, bedrooms, bathrooms, furnished status, location, floor details, map availability, and listed features.
- **Current Visible Property Set**: The current group of shown properties used for positional references and clarification options.
- **Property Gallery**: The ordered set of available photos for one resolved property.
- **Seller Contact Response**: A one-turn contact result for one explicitly requested resolved property.
- **Property Page Context**: A property the buyer was viewing before opening chat, used as the initial detail context when valid.
- **Detail Outcome Event**: A reviewable record of detail answer, unresolved reference, photo request, contact request, or fallback outcome.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In validation scenarios, 100% of clear positional references after a visible result page resolve to the correct current-page property.
- **SC-002**: In validation scenarios, 100% of ambiguous or missing property references trigger clarification instead of answering about the wrong property.
- **SC-003**: In validation scenarios, 100% of property-detail replies include only facts available for the resolved property.
- **SC-004**: In validation scenarios, 100% of missing requested fields are acknowledged as unavailable rather than invented or estimated.
- **SC-005**: In validation scenarios, 100% of photo requests for properties with photos show only the resolved property's gallery.
- **SC-006**: In validation scenarios, 100% of photo requests for properties without photos receive a clear no-photos-available response.
- **SC-007**: In validation scenarios, seller phone numbers appear in 0 replies unless the buyer explicitly asks for contact for one resolved property.
- **SC-008**: In validation scenarios, 100% of explicit single-property contact requests return either the correct contact or a clear unavailable-contact response.
- **SC-009**: In validation scenarios, 100% of valid property page context conversations can answer a first-turn property-detail question without requiring a prior search in chat.
- **SC-010**: In validation scenarios, 100% of property-detail replies preserve the buyer's language and conversational register.
- **SC-011**: In validation scenarios, property-detail replies appear within 3 seconds for normal follow-up questions after a property is resolved.
- **SC-SAFETY**: Prompt-injection or unsafe markup inside listing text, feature text, or image metadata changes assistant behavior or unsafe rendering in 0 validation scenarios.
- **SC-RELIABILITY**: In simulated temporary reply failures after a property is resolved, 100% of sessions preserve safe resolved property context and return a language-appropriate fallback.

## Assumptions

- Phase 4 starts after authenticated session ownership, prior search result context, current visible result references, and safe bulk result presentation are available.
- Property details can use information already retained for shown results unless a photo gallery or explicit seller contact request requires fresh single-property retrieval.
- Full image galleries are shown only when the buyer asks for photos or chooses a photo-viewing prompt.
- Seller contact is out of scope for bulk search and normal property-detail summaries; it belongs only to explicit single-property contact requests.
- Property page context is optional and applies only to the first chat turn unless later search results replace the active context.
- The chatbot continues to support cash listings only; installment-related questions follow the existing redirect behavior.
- The buyer can continue searching, paging, or refining after a property-detail turn without losing the search context unless they clearly start a new search.
