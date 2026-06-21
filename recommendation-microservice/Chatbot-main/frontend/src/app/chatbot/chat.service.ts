import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { ChatRequest, ChatResponse } from './chat.types';

@Injectable({ providedIn: 'root' })
export class ChatService {
  constructor(private readonly http: HttpClient) {}

  send(request: ChatRequest): Observable<ChatResponse> {
    return this.http.post<ChatResponse>('/api/chat', request).pipe(map((response) => this.normalizeResponse(response)));
  }

  private normalizeResponse(response: ChatResponse): ChatResponse {
    return {
      ...response,
      properties: response.properties ?? [],
      slot_collection: response.slot_collection ?? {
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
      resolution: response.resolution ?? null,
      complaint_case: response.complaint_case ?? null,
      property_reference: response.property_reference ?? null,
      property_detail: response.property_detail ?? null,
      property_gallery: response.property_gallery ?? null,
      seller_contact: response.seller_contact ?? null,
      search: response.search ?? {
        status: 'not_ready',
        search_id: null,
        result_count: 0,
        shown_count: 0,
        page_size: 5,
        has_more: false,
        visible_reference_map: [],
        min_price_fallback: null,
        budget_fallback: null,
      },
    };
  }
}
