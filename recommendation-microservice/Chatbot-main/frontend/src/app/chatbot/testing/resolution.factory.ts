import { ResolutionState } from '../chat.types';

export function resolutionState(overrides: Partial<ResolutionState> = {}): ResolutionState {
  return {
    outcomes: {
      location: null,
      propertyType: null,
      features: [],
    },
    pending_clarification: null,
    review_item_ids: [],
    ...overrides,
  };
}
