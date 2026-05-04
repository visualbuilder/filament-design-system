<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Visualbuilder\Lottie\Components\Lottie;

/**
 * Lottie animations catalogue. Demonstrates the visualbuilder/filament-lottie
 * Schema component with the most common triggers, alongside short usage notes.
 *
 * Only registered by FilamentDesignSystemPlugin when visualbuilder/filament-lottie
 * is installed — this page hard-references its Lottie schema component and
 * would fatal otherwise.
 */
class Animations extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $title = 'Animations';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament-design-system::pages.animations';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->introSection(),
            $this->mountSection(),
            $this->loopSection(),
            $this->clickSection(),
            $this->visibleSection(),
            $this->reducedMotionSection(),
            $this->bladeSection(),
            $this->cheatSheetSection(),
        ]);
    }

    protected function introSection(): Section
    {
        return Section::make('visualbuilder/filament-lottie')
            ->description('Lottie animations as a Filament Schema component and a Blade component, backed by @lottiefiles/dotlottie-wc.')
            ->schema([
                Text::make(new HtmlString(
                    '<div class="prose prose-sm dark:prose-invert max-w-none">'
                    .'<p>Use animations sparingly — they make sense as a one-off welcome moment, a celebration after a successful action, or to draw attention to a single state change. Looping background animations rarely earn their visual weight.</p>'
                    .'<p>Both <code>.lottie</code> (compressed bundles) and <code>.json</code> sources are supported. Prefer <code>.lottie</code> from your designer when available — it can carry multiple animations and themes in one file.</p>'
                    .'</div>'
                )),
            ]);
    }

    protected function mountSection(): Section
    {
        return Section::make('Play once on mount')
            ->description('Default trigger. Plays as soon as the component is in the DOM, then stops. Use for welcome / hero moments.')
            ->schema([
                Lottie::make('mount_demo')
                    ->src('lottie/smoke-test.json')
                    ->size('160px'),
                Text::make(new HtmlString(
                    '<pre class="text-xs"><code>Lottie::make(\'welcome\')'."\n"
                    .'    -&gt;src(\'lottie/welcome.lottie\')'."\n"
                    .'    -&gt;size(\'160px\');</code></pre>'
                )),
            ]);
    }

    protected function loopSection(): Section
    {
        return Section::make('Looping')
            ->description('Set ->loop(true) to repeat indefinitely. Reserve for genuinely ambient elements — empty-state illustrations, background flourishes — never for content the user needs to read.')
            ->schema([
                Lottie::make('loop_demo')
                    ->src('lottie/smoke-test.json')
                    ->size('160px')
                    ->loop(true),
                Text::make(new HtmlString(
                    '<pre class="text-xs"><code>Lottie::make(\'pulse\')'."\n"
                    .'    -&gt;src(\'lottie/pulse.lottie\')'."\n"
                    .'    -&gt;size(\'160px\')'."\n"
                    .'    -&gt;loop(true);</code></pre>'
                )),
            ]);
    }

    protected function clickSection(): Section
    {
        return Section::make('Replay on click')
            ->description('Trigger "click" disables autoplay; tapping the animation itself plays it from the start. The animation should rest in a visible state so the user sees what they’re clicking — Lottie animates from one visible frame to another, not from invisibility.')
            ->schema([
                Lottie::make('click_demo')
                    ->src('lottie/smoke-test.json')
                    ->size('160px')
                    ->autoplay(false)
                    ->trigger('click'),
                Text::make(new HtmlString(
                    '<pre class="text-xs"><code>Lottie::make(\'celebrate\')'."\n"
                    .'    -&gt;src(\'lottie/celebrate.lottie\')'."\n"
                    .'    -&gt;size(\'160px\')'."\n"
                    .'    -&gt;autoplay(false)'."\n"
                    .'    -&gt;trigger(\'click\');</code></pre>'
                )),
            ]);
    }

    protected function visibleSection(): Section
    {
        return Section::make('Play when scrolled into view')
            ->description('Trigger "visible" uses an IntersectionObserver to play once the element crosses 50% in the viewport. Pair with ->loop(false) so the animation lands as the user reaches the section.')
            ->schema([
                Lottie::make('visible_demo')
                    ->src('lottie/smoke-test.json')
                    ->size('160px')
                    ->autoplay(false)
                    ->trigger('visible'),
                Text::make(new HtmlString(
                    '<pre class="text-xs"><code>Lottie::make(\'reveal\')'."\n"
                    .'    -&gt;src(\'lottie/reveal.lottie\')'."\n"
                    .'    -&gt;size(\'160px\')'."\n"
                    .'    -&gt;autoplay(false)'."\n"
                    .'    -&gt;trigger(\'visible\');</code></pre>'
                )),
            ]);
    }

    protected function reducedMotionSection(): Section
    {
        return Section::make('Reduced motion')
            ->description('All animations honour the OS-level prefers-reduced-motion: reduce setting by default — they freeze on the first frame. Override per-instance with ->respectReducedMotion(false) only when motion is essential to comprehension.')
            ->schema([
                Text::make(new HtmlString(
                    '<div class="prose prose-sm dark:prose-invert max-w-none">'
                    .'<p>Toggle "Reduce motion" in your OS accessibility settings and reload this page — the animations above should freeze on the first frame.</p>'
                    .'</div>'
                )),
            ]);
    }

    protected function bladeSection(): Section
    {
        return Section::make('Blade component')
            ->description('For non-Filament views (marketing pages, customer-portal blades, mail templates rendered to HTML) the same surface is exposed as <x-lottie>.')
            ->schema([
                Text::make(new HtmlString(
                    '<pre class="text-xs"><code>&lt;x-lottie src="lottie/welcome.lottie"'."\n"
                    .'          size="160px"'."\n"
                    .'          :autoplay="true"'."\n"
                    .'          :loop="false" /&gt;</code></pre>'
                )),
            ]);
    }

    protected function cheatSheetSection(): Section
    {
        return Section::make('API cheat-sheet')
            ->collapsible()
            ->collapsed(true)
            ->description('Every fluent setter on Lottie::make().')
            ->schema([
                Text::make(new HtmlString(
                    '<div class="prose prose-sm dark:prose-invert max-w-none">'
                    .'<ul>'
                    .'<li><code>src(string)</code> — relative paths resolve through <code>asset()</code>; absolute URLs pass through.</li>'
                    .'<li><code>autoplay(bool = true)</code> — defaults to true. The "click", "hover", "visible" and "event:NAME" triggers force this to false.</li>'
                    .'<li><code>loop(bool = true)</code> — defaults to false. Repeats forever when true.</li>'
                    .'<li><code>speed(float)</code> — 1.0 = normal; &lt;1 slower, &gt;1 faster.</li>'
                    .'<li><code>size(string)</code> — shorthand for matching width + height (e.g. <code>"120px"</code>).</li>'
                    .'<li><code>width(string)</code> / <code>height(string)</code> — independent dimensions.</li>'
                    .'<li><code>trigger(string)</code> — <code>mount</code> (default) | <code>click</code> | <code>hover</code> | <code>visible</code> | <code>event:NAME</code>.</li>'
                    .'<li><code>onComplete(string)</code> — dispatches a window event when playback finishes (e.g. <code>"event:welcome-done"</code>). Useful from Filament action <code>after()</code> hooks.</li>'
                    .'<li><code>respectReducedMotion(bool = true)</code> — set false to ignore the OS preference.</li>'
                    .'</ul>'
                    .'<p><strong>Panel-level defaults:</strong></p>'
                    .'<pre class="text-xs"><code>-&gt;plugins(['."\n"
                    .'    LottiePlugin::make()'."\n"
                    .'        -&gt;defaultSize(\'80px\')'."\n"
                    .'        -&gt;defaultRespectReducedMotion(true),'."\n"
                    .'])</code></pre>'
                    .'</div>'
                )),
            ]);
    }
}
