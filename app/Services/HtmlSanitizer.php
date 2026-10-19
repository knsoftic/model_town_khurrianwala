<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans rich-text content written in the admin editor before it is stored,
 * so it can be printed unescaped on the public site without XSS risk.
 */
class HtmlSanitizer
{
    protected ?SymfonyHtmlSanitizer $sanitizer = null;

    public function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $clean = trim($this->sanitizer()->sanitize($html));

        return $clean === '' || $clean === '<p></p>' || $clean === '<p><br /></p>' ? null : $clean;
    }

    protected function sanitizer(): SymfonyHtmlSanitizer
    {
        return $this->sanitizer ??= new SymfonyHtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('u')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('h4')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('hr')
                ->allowElement('a', ['href', 'title', 'target'])
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(200000)
        );
    }
}
