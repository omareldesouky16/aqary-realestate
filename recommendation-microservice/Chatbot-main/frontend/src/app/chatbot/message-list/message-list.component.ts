import { Component, Input } from '@angular/core';
import { NgClass, NgFor, NgIf } from '@angular/common';
import { ChatMessage, PropertyDetail, PropertyGalleryImage, PropertyReference, PropertyReferenceCandidate, SearchResultItem } from '../chat.types';
import { SafeChatMarkdownPipe } from '../safe-chat-markdown.pipe';

@Component({
  selector: 'app-message-list',
  standalone: true,
  imports: [SafeChatMarkdownPipe, NgClass, NgFor, NgIf],
  template: `
    <div class="message-list">
      <div *ngFor="let msg of messages" class="message" [ngClass]="msg.role">
        <div class="bubble">
          <p *ngIf="msg.role === 'assistant' && msg.response" [innerHTML]="msg.response.reply | safeChatMarkdown"></p>
          <p *ngIf="msg.role === 'user'">{{ msg.text }}</p>
          <div *ngIf="searchResults(msg).length > 0" class="search-results">
            <div *ngFor="let prop of searchResults(msg)" class="property-card">
              <div class="prop-title">{{ safeResultTitle(prop) }}</div>
              <div class="prop-facts">{{ resultFacts(prop).join(' · ') }}</div>
            </div>
            <div *ngIf="canShowMore(msg)" class="show-more">Show more results</div>
          </div>
          <div *ngIf="propertyDetail(msg)" class="property-detail">
            <div class="detail-facts">{{ propertyDetailFacts(propertyDetail(msg)).join(' · ') }}</div>
          </div>
          <div *ngIf="galleryImages(msg).length > 0" class="gallery">
            <div *ngFor="let img of galleryImages(msg)" class="gallery-image">
              <img [src]="img.image_url" [alt]="img.alt_text || 'Property photo'" />
            </div>
          </div>
          <div *ngIf="sellerPhone(msg)" class="seller-contact">
            Phone: {{ sellerPhone(msg) }}
          </div>
          <div *ngIf="complaintStage(msg)" class="complaint-info">
            <div *ngIf="isComplaintFallback(msg)" class="fallback-notice">Complaint fallback preserved</div>
            <div *ngIf="complaintIssue(msg)">Issue: {{ complaintIssue(msg) }}</div>
            <div *ngIf="complaintPhone(msg)">Phone: {{ complaintPhone(msg) }}</div>
          </div>
          <div *ngIf="resolutionClarificationLabel(msg)" class="resolution-clarification">
            <p>Which {{ resolutionClarificationLabel(msg) }}?</p>
            <div *ngFor="let opt of resolutionCandidates(msg)" class="candidate-option">{{ opt }}</div>
          </div>
          <div *ngIf="propertyReferenceOptions(msg).length > 0" class="property-reference-options">
            <p>Which property?</p>
            <div *ngFor="let opt of propertyReferenceOptions(msg)" class="candidate-option">{{ opt.title }}</div>
          </div>
          <div *ngIf="clarificationOptions(msg).length > 0" class="clarification-options">
            <div *ngFor="let opt of clarificationOptions(msg)" class="candidate-option">{{ safeTitle(opt) }}</div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .message-list { flex: 1; overflow-y: auto; padding: 12px; }
    .message { margin-bottom: 8px; display: flex; }
    .message.user { justify-content: flex-end; }
    .bubble { max-width: 80%; padding: 8px 12px; border-radius: 12px; font-size: 14px; line-height: 1.4; }
    .message.user .bubble { background: #1976d2; color: #fff; border-bottom-right-radius: 4px; }
    .message.assistant .bubble { background: #f0f0f0; color: #333; border-bottom-left-radius: 4px; }
    .message-list p { margin: 0; }
    .property-card { background: #e8e8e8; border-radius: 8px; padding: 8px; margin: 6px 0; }
    .prop-title { font-weight: 600; }
    .prop-facts { font-size: 12px; color: #666; }
    .show-more { color: #1976d2; cursor: pointer; font-size: 13px; margin-top: 4px; }
    .gallery-image img { max-width: 100%; border-radius: 6px; margin: 4px 0; }
    .candidate-option { background: #e3f2fd; border-radius: 16px; padding: 4px 12px; margin: 4px 0; font-size: 13px; }
    .fallback-notice { font-style: italic; color: #888; font-size: 12px; }
    .seller-contact { color: #2e7d32; font-weight: 600; }
  `],
})
export class MessageListComponent {
  @Input() messages: ChatMessage[] = [];

  safeTitle(property: PropertyReference): string {
    return property.title;
  }

  clarificationOptions(message: ChatMessage): PropertyReference[] {
    return message.response?.properties ?? [];
  }

  propertyReferenceOptions(message: ChatMessage): PropertyReferenceCandidate[] {
    return message.response?.property_reference?.candidates ?? [];
  }

  propertyDetail(message: ChatMessage): PropertyDetail | null {
    return message.response?.property_detail ?? null;
  }

  propertyDetailFacts(detail: PropertyDetail | null): string[] {
    if (!detail) {
      return [];
    }

    const facts = [];
    if (detail.price !== undefined && detail.price !== null) facts.push(`EGP ${detail.price}`);
    if (detail.area !== undefined && detail.area !== null) facts.push(`${detail.area} sqm`);
    if (detail.bedrooms !== undefined && detail.bedrooms !== null) facts.push(`${detail.bedrooms} beds`);
    if (detail.bathrooms !== undefined && detail.bathrooms !== null) facts.push(`${detail.bathrooms} baths`);
    if (detail.furnished_status) facts.push(detail.furnished_status);
    if (detail.location) facts.push(detail.location);

    return facts;
  }

  galleryImages(message: ChatMessage): PropertyGalleryImage[] {
    return message.response?.property_gallery?.images ?? [];
  }

  sellerPhone(message: ChatMessage): string | null {
    const contact = message.response?.seller_contact;

    return contact?.contact_available ? contact.phone ?? null : null;
  }

  complaintStage(message: ChatMessage): string | null {
    return message.response?.complaint_case?.stage ?? null;
  }

  complaintIssue(message: ChatMessage): string | null {
    return message.response?.complaint_case?.issue_summary ?? null;
  }

  complaintPhone(message: ChatMessage): string | null {
    const complaint = message.response?.complaint_case;

    return complaint?.phone_status === 'valid' ? complaint.follow_up_phone_normalized ?? complaint.follow_up_phone ?? null : null;
  }

  isComplaintFallback(message: ChatMessage): boolean {
    return message.response?.complaint_case?.status === 'fallback_pending';
  }

  searchResults(message: ChatMessage): SearchResultItem[] {
    return (message.response?.properties ?? []) as SearchResultItem[];
  }

  resolutionCandidates(message: ChatMessage): string[] {
    const candidates = message.response?.resolution?.pending_clarification?.candidates ?? [];

    return candidates.slice(0, 3).map((candidate) => candidate.canonical_name);
  }

  resolutionClarificationLabel(message: ChatMessage): string | null {
    return message.response?.resolution?.pending_clarification?.preference_type ?? null;
  }

  hasCoverImage(property: SearchResultItem): boolean {
    return Boolean(property.has_cover_image || property.cover_image_url);
  }

  safeResultTitle(property: SearchResultItem): string {
    return this.safeTitle(property);
  }

  resultFacts(property: SearchResultItem): string[] {
    const facts = [];
    if (property.price !== undefined && property.price !== null) facts.push(`EGP ${property.price}`);
    if (property.area !== undefined && property.area !== null) facts.push(`${property.area} sqm`);
    if (property.bedrooms !== undefined && property.bedrooms !== null) facts.push(`${property.bedrooms} beds`);
    if (property.bathrooms !== undefined && property.bathrooms !== null) facts.push(`${property.bathrooms} baths`);
    if (property.furnished_status) facts.push(property.furnished_status);
    if (property.location) facts.push(property.location);

    return facts;
  }

  canShowMore(message: ChatMessage): boolean {
    return message.response?.has_more ?? false;
  }
}
