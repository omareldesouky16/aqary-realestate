import { ChatResponse } from '../chat.types';

export function chatResponse(overrides: Partial<ChatResponse> = {}): ChatResponse {
  return {
    reply: 'Got it.',
    intent: 'search_property',
    isComplaint: false,
    needsCheckIn: false,
    complaint_case: null,
    installment_redirect: false,
    awaiting_slots: [],
    slot_collection: {
      required_slots: {
        propertyType: { value: null, status: 'missing' },
        location: { value: null, status: 'missing' },
        price: { value: null, status: 'missing' },
      },
      optional_slots: {
        area: { value: null, status: 'missing' },
        bedrooms: { value: null, status: 'missing' },
        bathrooms: { value: null, status: 'missing' },
        features: { value: null, status: 'missing' },
      },
      missing_required_slots: ['propertyType', 'location', 'price'],
      next_question_slot: 'propertyType',
      optional_collection_status: 'not_asked',
      search_ready: false,
      budget_currency: null,
      clarification: null,
    },
    resolution: null,
    resolved_property_id: null,
    resolved_by: null,
    user_reference: null,
    property_reference: null,
    property_detail: null,
    property_gallery: null,
    seller_contact: null,
    properties: [],
    search: {
      status: 'not_ready',
      search_id: null,
      result_count: 0,
      shown_count: 0,
      page_size: 5,
      has_more: false,
      visible_reference_map: [],
      min_price_fallback: null,
    },
    show_image_offer: false,
    has_more: false,
    min_price_fallback: null,
    session_id: '11111111-1111-4111-8111-111111111111',
    fallback: false,
    ...overrides,
  };
}

export function complaintCase(overrides: Partial<NonNullable<ChatResponse['complaint_case']>> = {}) {
  return {
    status: 'active' as const,
    stage: 'awaiting_issue' as const,
    issue_summary: null,
    issue_language: 'en',
    phone_status: 'none' as const,
    follow_up_phone_normalized: null,
    follow_up_phone_attempts: 0,
    reviewable: true,
    events: [],
    ...overrides,
  };
}

export function searchState(overrides: Partial<NonNullable<ChatResponse['search']>> = {}): NonNullable<ChatResponse['search']> {
  return {
    status: 'results' as const,
    search_id: 'search-1',
    result_count: 3,
    shown_count: 3,
    page_size: 5,
    has_more: false,
    visible_reference_map: [
      { position: 1, listing_id: 42 },
      { position: 2, listing_id: 17 },
      { position: 3, listing_id: 88 },
    ],
    min_price_fallback: null,
    budget_fallback: null,
    ...overrides,
  };
}
