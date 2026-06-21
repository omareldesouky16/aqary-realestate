import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-message-input',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="message-input">
      <input
        [disabled]="disabled"
        [(ngModel)]="draft"
        (keyup.enter)="send()"
        placeholder="Type a message..."
        autocomplete="off"
      />
      <button [disabled]="disabled || !draft.trim()" (click)="send()">Send</button>
    </div>
  `,
  styles: [`
    .message-input { display: flex; border-top: 1px solid #ddd; padding: 8px; }
    .message-input input { flex: 1; border: 1px solid #ccc; border-radius: 20px; padding: 8px 14px; font-size: 14px; outline: none; }
    .message-input input:focus { border-color: #1976d2; }
    .message-input button { margin-left: 8px; background: #1976d2; color: #fff; border: none; border-radius: 20px; padding: 8px 16px; cursor: pointer; font-weight: 600; }
    .message-input button:disabled { opacity: .5; cursor: not-allowed; }
  `],
})
export class MessageInputComponent {
  @Input() disabled = false;
  @Output() sendMessage = new EventEmitter<string>();

  draft = '';

  send(): void {
    const value = this.draft.trim();
    if (!value || this.disabled) {
      return;
    }

    this.sendMessage.emit(value);
    this.draft = '';
  }
}
