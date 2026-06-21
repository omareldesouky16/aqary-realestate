import { SafeChatMarkdownPipe } from './safe-chat-markdown.pipe';

describe('SafeChatMarkdownPipe', () => {
  it('strips html and unsafe markdown links', () => {
    const pipe = new SafeChatMarkdownPipe();

    expect(pipe.transform('<script>alert(1)</script>[bad](javascript:alert(1)) [ok](https://example.test)')).toBe(
      'alert(1)bad ok (https://example.test)',
    );
  });
});
