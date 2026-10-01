<?php

namespace Tests\Unit;

use App\Services\BlogContentRenderer;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogContentRendererSecurityTest extends TestCase
{
    #[Test]
    public function HttpsEditorVideoSurvivesWithControls(): void
    {
        $source = '<video src="https://cdn.example.test/lesson.mp4" onerror="alert(1)" autoplay></video><video src="javascript:alert(2)"></video><video src="http://cdn.example.test/unsafe.mp4"></video>';

        $html = app(BlogContentRenderer::class)->Render($source);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><div id="article-body">'.$html.'</div>');
        $xpath = new DOMXPath($document);
        $body_path = '//*[@id="article-body"]';

        $this->assertSame(1, $xpath->query($body_path.'//video[@src="https://cdn.example.test/lesson.mp4" and @controls]')->length);
        $this->assertSame(0, $xpath->query($body_path.'//video[@onerror or @autoplay or starts-with(@src, "javascript:") or starts-with(@src, "http:")]')->length);
        $this->assertSame(1, $xpath->query($body_path.'//video')->length);
    }

    #[Test]
    public function EditorColorsSurviveButUnsafeCssIsRemoved(): void
    {
        $source = '<p><span style="color:#12abcd">Text</span><span style="background-color:#abc">Highlight</span><span style="color:#fff;position:fixed;background-image:url(javascript:alert(1))">Mixed</span></p>';

        $html = app(BlogContentRenderer::class)->Render($source);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><div id="article-body">'.$html.'</div>');
        $xpath = new DOMXPath($document);
        $body_path = '//*[@id="article-body"]';

        $this->assertSame('color:#12abcd', $xpath->evaluate('string('.$body_path.'//span[normalize-space()="Text"]/@style)'));
        $this->assertSame('background-color:#abc', $xpath->evaluate('string('.$body_path.'//span[normalize-space()="Highlight"]/@style)'));
        $this->assertSame('color:#fff', $xpath->evaluate('string('.$body_path.'//span[normalize-space()="Mixed"]/@style)'));
    }

    #[Test]
    public function RenderedArticleKeepsFormattingAndYoutubeWithoutExecutableMarkup(): void
    {
        $source = '<p>Safe <strong>formatting</strong></p><img src="x" onerror="alert(1)"><a href="javascript:alert(2)">unsafe link</a><a href="https://example.test/guide">guide</a><iframe src="https://www.youtube.com/embed/abc_DEF12" onload="alert(3)"></iframe><iframe src="https://evil.example/embed/abc_DEF12"></iframe><svg onload="alert(4)"></svg><script>alert(5)</script>';

        $html = app(BlogContentRenderer::class)->Render($source);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><div id="article-body">'.$html.'</div>');
        $xpath = new DOMXPath($document);
        $body_path = '//*[@id="article-body"]';

        $this->assertSame(1, $xpath->query($body_path.'//strong[normalize-space()="formatting"]')->length);
        $this->assertSame(1, $xpath->query($body_path.'//img[@src="x"]')->length);
        $this->assertSame(1, $xpath->query($body_path.'//a[@href="https://example.test/guide"]')->length);
        $this->assertSame(1, $xpath->query($body_path.'//iframe[@src="https://www.youtube.com/embed/abc_DEF12"]')->length);
        $this->assertSame(1, $xpath->query($body_path.'//iframe[contains(@allow, "picture-in-picture") and @allowfullscreen]')->length);
        $this->assertSame(0, $xpath->query($body_path.'//*[@onerror or @onload]')->length);
        $this->assertSame(0, $xpath->query($body_path.'//a[starts-with(@href, "javascript:")]')->length);
        $this->assertSame(0, $xpath->query($body_path.'//iframe[contains(@src, "evil.example")]')->length);
        $this->assertSame(0, $xpath->query($body_path.'//script | '.$body_path.'//svg')->length);
    }
}
