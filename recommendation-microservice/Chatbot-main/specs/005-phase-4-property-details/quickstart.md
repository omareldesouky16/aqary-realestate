# Phase 4 Quickstart: Property Details Validation

## Prerequisites

- Backend dependencies are installed for the Laravel test harness.
- Frontend dependencies are installed for the Angular chat widget tests.
- Test data includes authenticated buyers, owned chat sessions, ranked visible result pages from Phase 3, active/inactive cash listings, missing optional listing fields, gallery images, listings without images, seller contact records, and unsafe seller-supplied text.
- Phase 3 result-state behavior is available so current visible result references are retained and updated after search/show-more replies.

## Setup

```powershell
cd backend
php artisan test
```

```powershell
cd frontend
npm test
```

Use focused test filters during implementation when available, then run the broader suites before completion.

## Scenario 1: Detail Answer For Current Visible Property

1. Authenticate as a buyer and create or reuse an owned chat session.
2. Complete a search that returns a visible result page.
3. Ask about "the first one" or a clear partial title from the current page.
4. Verify `intent: property_details`, `resolved_property_id` matches the current visible page item, and `resolved_by` is `position` or `title_match`.
5. Verify `property_detail` includes only available facts for that property.
6. Verify missing requested facts are acknowledged as unavailable.
7. Verify the reply preserves the buyer's Arabic or English language/register.

Expected outcome: the answer is grounded in one current-page property and appears within 3 seconds in normal validation.

## Scenario 2: Ambiguous Or Missing Reference Clarification

1. Show several visible properties.
2. Ask an ambiguous question such as "tell me about that apartment".
3. Verify no detail, gallery, or contact payload is returned.
4. Verify `property_reference.status` is `ambiguous`, `awaiting_slots` includes `property_reference_clarification`, and candidates repeat the current visible options in numbered form.
5. Reply with a numbered choice or title.
6. Verify the system continues the property-detail answer for the selected property.

Expected outcome: ambiguous references trigger clarification instead of answering about the wrong listing.

## Scenario 3: Stale Page Reference

1. Complete a search and show the first result page.
2. Ask for more results so a new current visible page replaces the old page.
3. Ask about "the second one".
4. Verify the reference resolves against the new page only.
5. Ask using a title that belongs only to the old page.

Expected outcome: current-page references remain stable until replaced, and stale references do not silently resolve to older pages.

## Scenario 4: Photo Gallery Request

1. Resolve one visible property with gallery images.
2. Ask to see photos.
3. Verify `intent: show_property_photos` and `property_gallery.images` contains only that property's ordered images.
4. Verify the reply invites photo viewing but does not include raw image paths as conversational facts.
5. Repeat with a resolved property that has no images.

Expected outcome: available photos render as a structured gallery; no-photo properties receive a clear no-photos-available response.

## Scenario 5: Explicit Seller Contact

1. Resolve one active property with seller contact.
2. Ask a normal detail question and verify no seller phone appears in reply text, `property_detail`, `properties`, or reusable search state.
3. Ask explicitly for the seller phone for that property.
4. Verify `intent: seller_contact`, `seller_contact.contact_available: true`, and `seller_contact.phone` is present only for that turn.
5. Ask for contact with an ambiguous property reference.
6. Verify the system asks for clarification before sharing contact.

Expected outcome: seller contact is returned only for explicit contact intent and one resolved active property.

## Scenario 6: Inactive Or Missing Contact

1. Resolve a property that becomes inactive after being shown.
2. Ask for seller contact.
3. Verify no phone is returned and the reply says the listing may no longer be available.
4. Repeat with an active property that has no seller contact.

Expected outcome: contact is withheld for inactive, unavailable, unresolved, or missing-contact cases.

## Scenario 7: Property Page Context First Turn

1. Open the chatbot with a valid `context_property_id` from a property detail page before any search results are shown in chat.
2. Ask "does it have parking?"
3. Verify the reference resolves by `page_context` and returns details for that property.
4. Ask for photos and then seller contact to verify the same gallery/contact rules apply.
5. Start a new search and verify the page context is superseded by visible search results.

Expected outcome: valid page context can scope the first property-detail turn, then later search context takes over.

## Scenario 8: Safe Rendering And Prompt Injection

1. Seed listing title, feature text, gallery metadata, and user text with HTML-like content, markdown, and instruction-like content.
2. Ask for details and photos for that property.
3. Verify seller-supplied text does not alter assistant behavior.
4. Verify Angular renders text and gallery metadata safely without unsafe markup.

Expected outcome: untrusted listing and image metadata remain inert in prompts and UI rendering.

## Scenario 9: Reply Failure Preserves Safe Property Context

1. Simulate successful deterministic property resolution followed by reply-generation failure.
2. Verify the response uses a language-appropriate friendly fallback.
3. Verify safe resolved property context remains available for a follow-up photo or detail request.

Expected outcome: temporary reply failures do not lose safe resolved property state.

## Scenario 10: Cash-Only Redirect Precedence

1. Ask about installments, down payment, or monthly payment during a property-detail flow.
2. Verify `intent: installment_redirect` and `installment_redirect: true`.
3. Verify no installment-specific property details are invented.

Expected outcome: the existing cash-only redirect takes precedence over Phase 4 property details.

## Contract Checks

- Backend responses must satisfy [contracts/chat-api.yaml](./contracts/chat-api.yaml).
- Angular `ChatResponse` types must include `property_reference`, `property_detail`, `property_gallery`, and `seller_contact`.
- Seller phone numbers must be absent from normal detail replies, photo replies, ambiguous-reference replies, bulk search results, and reusable result context.
- Detail outcome events must be reviewable through chat state, logs, or dedicated records without exposing provider secrets or raw broad contact payloads.

## Implementation Coverage Notes

- `PropertyReferenceResolver` resolves current-page position/title references and valid property-page context, and returns clarification candidates for unresolved references.
- `PropertyDetailService`, `PropertyGalleryService`, and `SellerContactService` keep details, galleries, and contact as separate one-property payloads.
- `ChatController` returns `property_reference`, `property_detail`, `property_gallery`, and `seller_contact` alongside existing search payloads.
- Frontend helpers render detail facts, clarification options, gallery images, and explicit seller phone without placing contact in reusable search result cards.
