import { MessageListComponent } from './message-list.component';
import { chatResponse, complaintCase, searchState } from '../testing/chatbot-test.factory';

describe('MessageListComponent search helpers', () => {
  it('returns ranked properties and safe facts', () => {
    const component = new MessageListComponent();
    const response = chatResponse({
      properties: [
        {
          id: 42,
          position: 1,
          rank_position: 1,
          title: 'Luxury Apartment in Maadi',
          url: 'https://example.test/listings/42',
          price: 3200000,
          area: 180,
          bedrooms: 3,
          bathrooms: 2,
          furnished_status: 'Furnished',
          location: 'Maadi',
          has_cover_image: true,
          matched_features: ['Security'],
        },
      ],
      search: searchState(),
    });

    const message = { role: 'assistant' as const, text: 'Here you go', response };

    expect(component.searchResults(message).length).toBe(1);
    expect(component.resultFacts(component.searchResults(message)[0]).join(' ')).toContain('EGP 3200000');
    expect(component.hasCoverImage(component.searchResults(message)[0])).toBeTrue();
  });

  it('renders budget fallback without stale result cards', () => {
    const component = new MessageListComponent();
    const response = chatResponse({
      properties: [],
      min_price_fallback: 1800000,
      search: searchState({ status: 'budget_fallback', min_price_fallback: 1800000, has_more: false }),
    });

    const message = { role: 'assistant' as const, text: 'Increase budget', response };

    expect(component.searchResults(message)).toEqual([]);
    expect(component.canShowMore(message)).toBeFalse();
  });

  it('omits missing facts and uses safe result titles', () => {
    const component = new MessageListComponent();
    const property = {
      id: 7,
      position: 1,
      rank_position: 1,
      title: '<b>Unsafe</b> title',
      url: '/properties/7',
      has_cover_image: false,
      matched_features: [],
    };

    expect(component.safeResultTitle(property)).toBe('<b>Unsafe</b> title');
    expect(component.resultFacts(property)).toEqual([]);
    expect(component.hasCoverImage(property)).toBeFalse();
  });

  it('returns property detail facts, reference options, gallery images, and explicit contact', () => {
    const component = new MessageListComponent();
    const response = chatResponse({
      property_reference: {
        status: 'ambiguous',
        candidates: [{ position: 1, property_id: 42, title: 'Luxury Apartment' }],
        clarification_prompt: 'Which property?',
      },
      property_detail: {
        id: 42,
        title: 'Luxury Apartment',
        url: '/listings/42',
        price: 3200000,
        area: 180,
        bedrooms: 3,
        bathrooms: 2,
        furnished_status: 'Furnished',
        location: 'Maadi',
        missing_fields: [],
      },
      property_gallery: {
        property_id: 42,
        has_images: true,
        images: [{ image_url: 'https://example.test/42.jpg', display_order: 1, alt_text: '<b>unsafe</b>' }],
      },
      seller_contact: {
        property_id: 42,
        contact_available: true,
        phone: '01000000000',
      },
    });
    const message = { role: 'assistant' as const, text: 'Details', response };

    expect(component.propertyReferenceOptions(message)[0].property_id).toBe(42);
    expect(component.propertyDetailFacts(component.propertyDetail(message)).join(' ')).toContain('EGP 3200000');
    expect(component.galleryImages(message)[0].image_url).toContain('42.jpg');
    expect(component.sellerPhone(message)).toBe('01000000000');
  });

  it('returns complaint stage, issue, phone, and fallback state', () => {
    const component = new MessageListComponent();
    const response = chatResponse({
      isComplaint: true,
      complaint_case: complaintCase({
        status: 'fallback_pending',
        stage: 'saved',
        issue_summary: 'Wrong search results',
        phone_status: 'valid',
        follow_up_phone_normalized: '+201001234567',
      }),
    });
    const message = { role: 'assistant' as const, text: 'Saved', response };

    expect(component.complaintStage(message)).toBe('saved');
    expect(component.complaintIssue(message)).toBe('Wrong search results');
    expect(component.complaintPhone(message)).toBe('+201001234567');
    expect(component.isComplaintFallback(message)).toBeTrue();
  });
});
