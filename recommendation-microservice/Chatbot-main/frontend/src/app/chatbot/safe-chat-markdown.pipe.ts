import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'safeChatMarkdown',
  standalone: true,
})
export class SafeChatMarkdownPipe implements PipeTransform {
  transform(value: string | null | undefined): string {
    if (!value) {
      return '';
    }

    return value
      .replace(/<[^>]*>/g, '')
      .replace(/\[([^\]]*)\]\(((?:[^()]|\([^)]*\))*)\)/g, (_, text, url) =>
        /^https?:\/\//.test(url) ? `${text} (${url})` : text)
      .trim();
  }
}
