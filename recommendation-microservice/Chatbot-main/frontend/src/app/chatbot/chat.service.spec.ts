import { chatResponse, searchState } from './testing/chatbot-test.factory';

describe('ChatService contract helpers', () => {
  it('provides the phase 3 search defaults', () => {
    const response = chatResponse({ search: searchState() });

    expect(response.search?.status).toBe('results');
    expect(response.properties.length).toBe(0);
    expect(response.property_detail ?? null).toBeNull();
    expect(response.property_gallery ?? null).toBeNull();
    expect(response.complaint_case ?? null).toBeNull();
  });
});
