<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Mews\Purifier\Facades\Purifier;

class TextFormatter
{
    /**
     * Convert Markdown to HTML.
     *
     * @param  string  $text  The markdown text to convert
     * @param  int  $capLevel  The minimum header level to display (default: 3)
     *
     * @return string The purified HTML output
     */
    public static function markdown(string $text, int $capLevel = 3): string
    {
        try {
            $environment = new Environment([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 10,
                'max_delimiters_per_line' => 100,
            ]);
            $environment->addExtension(new CommonMarkCoreExtension);

            // Overrides the heading renderer with the adjusted one:
            $environment->addRenderer(Heading::class, new class($capLevel) implements NodeRendererInterface
            {
                private int $capLevel;

                public function __construct(int $capLevel)
                {
                    $this->capLevel = $capLevel;
                }

                public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
                {
                    if (! ($node instanceof Heading)) {
                        throw new InvalidArgumentException('Incompatible node type: ' . get_class($node));
                    }

                    $originalLevel = $node->getLevel();
                    $level = $originalLevel;

                    // Caps headers based on the configured cap level:
                    if ($level < $this->capLevel) {
                        $level = $this->capLevel;
                    }

                    $attrs = $node->data->get('attributes', []);

                    // Add the class based on the original header level
                    if (! isset($attrs['class'])) {
                        $attrs['class'] = '';
                    }

                    // Add appropriate class name with the original level
                    if ($attrs['class'] !== '') {
                        $attrs['class'] .= ' ';
                    }

                    $attrs['class'] .= 'markdown-header-' . $originalLevel;

                    if ($originalLevel < $this->capLevel) {
                        $attrs['class'] .= ' markdown-header-top';
                    }

                    return new HtmlElement(
                        'h' . $level,
                        $attrs,
                        $childRenderer->renderNodes($node->children())
                    );
                }
            });

            $converter = new MarkdownConverter($environment);
            $html = $converter->convert($text)->getContent();

            return Purifier::clean($html, 'markdown');

        } catch (CommonMarkException $e) {
            Log::error('Markdown parsing failed: ' . $e->getMessage(), [
                'exception' => $e,
                'text' => $text,
            ]);

            return self::paragraphize($text);

        }
    }

    /**
     * Convert text with line breaks into paragraphs.
     */
    public static function paragraphize(string $text): string
    {
        return collect(explode("\n", $text))
            ->map(fn ($line) => '<p>' . e($line) . '</p>')
            ->implode('');
    }
}
