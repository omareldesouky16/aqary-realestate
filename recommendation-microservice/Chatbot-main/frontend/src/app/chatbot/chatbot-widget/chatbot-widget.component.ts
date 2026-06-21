import { Component, Input } from '@angular/core';
import { NgIf } from '@angular/common';
import { finalize } from 'rxjs';
import { ChatMessage } from '../chat.types';
import { ChatService } from '../chat.service';
import { MessageListComponent } from '../message-list/message-list.component';
import { MessageInputComponent } from '../message-input/message-input.component';

@Component({
  selector: 'app-chatbot-widget',
  standalone: true,
  imports: [NgIf, MessageListComponent, MessageInputComponent],
  template: `
    <div class="chatbot-widget">
      <div class="chatbot-toggle" (click)="toggle()">
        {{ expanded ? 'Close' : 'Chat' }}
      </div>
      <div *ngIf="expanded" class="chatbot-panel">
        <div class="chatbot-header">Aqary Assistant</div>
        <app-message-list [messages]="messages"></app-message-list>
        <app-message-input [disabled]="loading" (sendMessage)="send($event)"></app-message-input>
      </div>
    </div>
  `,
  styles: [`
    .chatbot-widget { position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: sans-serif; }
    .chatbot-toggle { background: #1976d2; color: #fff; padding: 10px 20px; border-radius: 24px; cursor: pointer; text-align: center; font-weight: 600; box-shadow: 0 2px 10px rgba(0,0,0,.2); }
    .chatbot-panel { position: absolute; bottom: 60px; right: 0; width: 360px; height: 500px; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.15); display: flex; flex-direction: column; overflow: hidden; }
    .chatbot-header { background: #1976d2; color: #fff; padding: 12px 16px; font-weight: 600; }
  `],
})
export class ChatbotWidgetComponent {
  @Input() contextPropertyId: number | null = null;

  readonly messages: ChatMessage[] = [];
  sessionId: string = crypto.randomUUID();
  loading = false;
  expanded = false;
  currentSearchId: string | null = null;

  constructor(private readonly chat: ChatService) {}

  toggle(): void {
    this.expanded = !this.expanded;
  }

  send(message: string): void {
    const text = message.trim();
    if (!text || this.loading) {
      return;
    }

    this.messages.push({ role: 'user', text });
    this.loading = true;

    const includeContext = this.messages.filter((item) => item.role === 'user').length === 1;
    this.chat
      .send({
        session_id: this.sessionId,
        message: text,
        context_property_id: includeContext ? this.contextPropertyId : undefined,
      })
      .pipe(finalize(() => (this.loading = false)))
      .subscribe((response) => {
        this.sessionId = response.session_id;
        if (response.search?.search_id && response.search.search_id !== this.currentSearchId) {
          this.currentSearchId = response.search.search_id;
          for (const item of this.messages) {
            if (item.response?.search?.search_id && item.response.search.search_id !== response.search.search_id) {
              item.response = { ...item.response, properties: [], has_more: false };
            }
          }
        }
        this.messages.push({ role: 'assistant', text: response.reply, response });
      });
  }

  acceptCashRedirect(): void {
    this.send('cash is fine');
  }
}
