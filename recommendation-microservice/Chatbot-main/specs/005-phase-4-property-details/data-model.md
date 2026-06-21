# Phase 4 Data Model: Property Details

## Property Reference

Represents the buyer's mention of a property that must resolve to one accessible property before detail, photo, or contact behavior runs.

**Fields**

- `session_id`: UUID for the authenticated chat session.
- `raw_text`: Buyer text or parsed reference phrase.
- `reference_type`: `position`, `title`, `context`, or `explicit_id`.
- `position`: 1-based page position when the buyer uses positional wording.
- `title_query`: Normalized title phrase when the buyer uses a title or partial title.
- `context_property_id`: Optional property ID supplied from a property detail page.
- `visible_reference_map`: Current visible page mapping from Phase 3 state.
- `status`: `resolved`, `ambiguous`, `missing`, `stale`, or `unresolved`.
- `candidate_property_ids`: Candidate listing IDs when ambiguous.

**Validation Rules**

- `session_id` must be owner-bound to the authenticated user.
- Positional references resolve only against the current visible result page.
- Title references resolve only when exactly one current visible property or valid page-context property matches.
- Stale references to older pages must not resolve.
- Missing or ambiguous references must trigger clarification before details, photos, or contact lookup.

## Resolved Property Detail

The safe set of available facts for one resolved active property.

**Fields**

- `property_id`: Listing ID.
- `title`: Seller-supplied title, treated as untrusted display text.
- `url`: Listing page URL.
- `status`: Listing status.
- `price`: Price in EGP when available.
- `area`: Area when available.
- `bedrooms`: Bedroom count when available.
- `bathrooms`: Bathroom count when available.
- `furnished_status`: Furnished status when available.
- `location`: Canonical location display name when available.
- `floor_details`: Floor/elevator information when available.
- `map_available`: Boolean.
- `features`: Listing feature names proven by stored data.
- `missing_fields`: Requested fields that are unavailable.
- `language`: Buyer reply language/register indicator from chat state.

**Validation Rules**

- Details must come from authenticated database records or validated retained result data.
- Missing, null, or unavailable fields must be acknowledged as unavailable and never estimated.
- Seller-supplied text must be escaped or delimited as untrusted for prompts and rendering.
- Inactive or inaccessible properties must not expose contact and should produce a clear unavailable-property response.

## Current Visible Property Set

The current group of shown properties used for Phase 4 positional references and clarification options.

**Fields**

- `search_id`: Stable identifier for the retained ranked result set.
- `page_number`: Current visible result page number.
- `items`: Up to 5 visible property summaries.
- `visible_reference_map`: Mapping from `position` 1 through 5 to listing IDs.
- `shown_at`: Time the current page was shown.

**State Transitions**

- `active` -> `replaced`: A new result page, new search, or valid property page context supersedes it.
- `active` -> `cleared`: Buyer starts a new search without current visible results.
- `active` -> `used_for_detail`: A reference resolves to one visible property for a detail/photo/contact turn.

## Property Gallery

Ordered photos for one resolved property.

**Fields**

- `property_id`: Listing ID.
- `images`: Ordered list of gallery items.
- `image_url`: Safe URL or path exposed for rendering.
- `display_order`: 1-based gallery order.
- `alt_text`: Optional seller-supplied or generated display text treated as inert.
- `has_images`: Boolean.

**Validation Rules**

- Gallery retrieval requires one resolved property.
- Returned images must belong only to the resolved property.
- Raw storage paths and unsafe metadata must not be placed in conversational reply text.
- If no images exist, the response must state that photos are not currently available.

## Seller Contact Response

A one-turn contact result for one explicitly requested resolved property.

**Fields**

- `property_id`: Listing ID.
- `requested_explicitly`: Boolean.
- `phone`: Seller phone number when available and allowed.
- `contact_available`: Boolean.
- `withheld_reason`: `not_explicit`, `ambiguous_property`, `inactive_property`, `missing_contact`, or `unauthorized`.
- `returned_at`: Time contact was returned.

**Validation Rules**

- Contact lookup requires authenticated buyer, owned session, explicit contact intent, and one resolved active property.
- Seller phone must not appear in normal detail, photo, ambiguous-reference, or bulk search replies.
- Seller phone must not be retained in reusable result context after the contact turn.
- Missing or unavailable contact must produce a clear unavailable-contact response.

## Property Page Context

A property the buyer was viewing before opening chat.

**Fields**

- `context_property_id`: Listing ID supplied by the client.
- `session_id`: Chat session UUID.
- `status`: `valid`, `invalid`, `inactive`, `inaccessible`, or `superseded`.
- `applies_to_turn`: Boolean indicating whether it can scope the current first detail turn.
- `validated_at`: Time the context was checked.

**State Transitions**

- `provided` -> `valid`: The property exists, is active, and is accessible to the authenticated buyer.
- `provided` -> `invalid`: The property cannot be found or is inaccessible.
- `valid` -> `superseded`: Buyer starts a new search or later visible search results define the active reference context.

## Detail Outcome Event

Reviewable record of property-detail behavior.

**Fields**

- `session_id`: Chat session UUID.
- `event_type`: `detail_answer`, `unresolved_reference`, `clarification`, `photo_gallery`, `no_photos`, `contact_returned`, `contact_unavailable`, or `reply_fallback`.
- `property_id`: Resolved property ID when available.
- `reference_type`: Reference strategy used when available.
- `requested_field`: Detail field requested when identifiable.
- `missing_fields`: Requested unavailable fields.
- `photo_count`: Number of gallery images returned.
- `contact_returned`: Boolean.
- `fallback`: Boolean.
- `latency_ms`: Resolution and reply latency when available.

**Validation Rules**

- Outcome events must not expose provider secrets.
- Seller phone should be recorded only as a boolean/contact outcome, not copied into broad review payloads.
- Reply failures after safe resolution must preserve the safe resolved property payload for the next turn.
