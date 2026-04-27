#!/usr/bin/env node
/*
 * Default screenshot capture for the design-system MCP server.
 *
 * Drives headless Chromium via Playwright. Works against any local URL,
 * including self-signed HTTPS, so a host with Playwright in its node_modules
 * gets a working iteration loop without registering a custom closure.
 *
 * Usage:
 *   node screenshot.cjs <url> <output.png> [WIDTHxHEIGHT] [waitMs] [--no-full-page]
 *
 * Exit codes:
 *   0  success — PNG written to <output>
 *   1  capture failed — see stderr
 *   2  usage error — see stderr
 */

const args = process.argv.slice(2);

if (args.length < 2) {
    console.error('Usage: screenshot.cjs <url> <output.png> [WIDTHxHEIGHT] [waitMs] [--no-full-page]');
    process.exit(2);
}

const url = args[0];
const output = args[1];
let viewport = '1366x768';
let waitMs = 1500;
let fullPage = true;
let theme = null; // null = leave whatever localStorage already has

for (const a of args.slice(2)) {
    if (a === '--no-full-page') fullPage = false;
    else if (/^\d+x\d+$/.test(a)) viewport = a;
    else if (/^\d+$/.test(a)) waitMs = parseInt(a, 10);
    else if (/^--theme=(light|dark|system)$/.test(a)) theme = a.split('=')[1];
    else {
        console.error(`Unrecognised argument: ${a}`);
        process.exit(2);
    }
}

const [width, height] = viewport.split('x').map(Number);

let chromium;
try {
    ({ chromium } = require('playwright'));
} catch (err) {
    console.error('Playwright is not installed in this project. Run: npm install --save-dev playwright && npx playwright install chromium');
    process.exit(1);
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    try {
        const context = await browser.newContext({
            viewport: { width, height },
            ignoreHTTPSErrors: true,
        });
        const page = await context.newPage();

        // Pre-seed localStorage so Filament's theme switcher reads the chosen
        // mode on first paint instead of toggling after load (which would
        // give us a transient flash of the wrong mode).
        if (theme !== null) {
            await page.addInitScript((t) => {
                try { localStorage.setItem('theme', t); } catch (_) {}
            }, theme);
        }

        await page.goto(url, { waitUntil: 'networkidle', timeout: 30_000 });

        // Belt-and-braces: enforce the dark-class state on <html> after load,
        // since some boot scripts derive it from system pref or session.
        if (theme !== null) {
            await page.evaluate((t) => {
                if (t === 'dark') document.documentElement.classList.add('dark');
                else if (t === 'light') document.documentElement.classList.remove('dark');
                else if (t === 'system') {
                    const prefersDark = matchMedia?.('(prefers-color-scheme: dark)').matches ?? false;
                    document.documentElement.classList.toggle('dark', prefersDark);
                }
            }, theme);
        }
        if (waitMs > 0) await page.waitForTimeout(waitMs);

        // Hide common dev-time overlays so screenshots show only the design surface.
        // Add new selectors here when a host's stack introduces another floating bar.
        await page.addStyleTag({
            content: `
                .phpdebugbar,
                [id^="phpdebugbar"],
                #__debugbar,
                .debugbar,
                #toolbar,
                .sf-toolbar,
                #ddt,
                [id^="livewire-error"]
                { display: none !important; visibility: hidden !important; }

                /* Neutralise sticky/fixed positioning during fullPage capture so
                   sticky footers/banners fall to their natural document position
                   instead of being stamped mid-page where the original viewport's
                   bottom edge sat. Doesn't affect real-browser rendering. */
                footer[class*="sticky"],
                footer[class*="fixed"],
                .fi-page-footer.sticky,
                .fi-page-footer.fixed
                { position: static !important; }
            `,
        });

        await page.screenshot({ path: output, fullPage });
    } finally {
        await browser.close();
    }
})().catch((err) => {
    console.error(err && err.message ? err.message : String(err));
    process.exit(1);
});
