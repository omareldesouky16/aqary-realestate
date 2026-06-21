import { bootstrapApplication } from '@angular/platform-browser';
import { ChatbotWidgetComponent } from './app/chatbot/chatbot-widget/chatbot-widget.component';
import { provideHttpClient } from '@angular/common/http';

bootstrapApplication(ChatbotWidgetComponent, {
  providers: [
    provideHttpClient()
  ]
}).catch(err => console.error(err));
