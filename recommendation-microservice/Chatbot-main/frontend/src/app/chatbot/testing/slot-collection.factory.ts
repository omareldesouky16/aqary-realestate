import { SlotCollectionState } from '../chat.types';

export function slotCollectionState(overrides: Partial<SlotCollectionState> = {}): SlotCollectionState {
  return {
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
    ...overrides,
  };
}
