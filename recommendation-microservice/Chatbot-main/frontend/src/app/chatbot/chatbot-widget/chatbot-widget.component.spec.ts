import { of } from 'rxjs';
import { ChatbotWidgetComponent } from './chatbot-widget.component';
import { chatResponse } from '../testing/chatbot-test.factory';

describe('ChatbotWidgetComponent', () => {
  it('preserves response session id and records assistant reply', () => {
    const service = { send: jasmine.createSpy().and.returnValue(of(chatResponse({ session_id: '22222222-2222-4222-8222-222222222222' }))) };
    const component = new ChatbotWidgetComponent(service as any);

    component.send('I need an apartment');

    expect(component.sessionId).toBe('22222222-2222-4222-8222-222222222222');
    expect(component.messages.at(-1)?.role).toBe('assistant');
  });

  it('continues same visible session after cash redirect acceptance', () => {
    const session_id = '11111111-1111-4111-8111-111111111111';
    const service = { send: jasmine.createSpy().and.returnValue(of(chatResponse({ intent: 'installment_redirect', installment_redirect: true, session_id }))) };
    const component = new ChatbotWidgetComponent(service as any);

    component.acceptCashRedirect();

    expect(component.sessionId).toBe(session_id);
    expect(service.send).toHaveBeenCalled();
  });

  it('clears stale properties when a fresh search id arrives', () => {
    const first = chatResponse({
      search: { status: 'results', search_id: 'search-1', result_count: 1, shown_count: 1, page_size: 5, has_more: false, visible_reference_map: [] },
      properties: [{ id: 1, title: 'Old', url: '/old', position: 1, rank_position: 1 }],
    });
    const second = chatResponse({
      search: { status: 'results', search_id: 'search-2', result_count: 1, shown_count: 1, page_size: 5, has_more: false, visible_reference_map: [] },
      properties: [{ id: 2, title: 'New', url: '/new', position: 1, rank_position: 1 }],
    });
    const service = { send: jasmine.createSpy().and.returnValues(of(first), of(second)) };
    const component = new ChatbotWidgetComponent(service as any);

    component.send('first search');
    component.send('new location');

    expect(component.messages[1].response?.properties).toEqual([]);
    expect(component.messages.at(-1)?.response?.properties[0].id).toBe(2);
  });

  it('sends first-turn property page context only on the first user message', () => {
    const service = { send: jasmine.createSpy().and.returnValue(of(chatResponse())) };
    const component = new ChatbotWidgetComponent(service as any);
    component.contextPropertyId = 42;

    component.send('Does it have parking?');
    component.send('Show photos');

    expect(service.send.calls.argsFor(0)[0].context_property_id).toBe(42);
    expect(service.send.calls.argsFor(1)[0].context_property_id).toBeUndefined();
  });
});
