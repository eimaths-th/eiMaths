# Affiliate Tutorial v2 — 2026-09-10

## Result

The Affiliate portal has 21 lessons across five topics when Media and Analytics are enabled: sharing a referral link, promotional media, income, payout accounts, and withdrawals/help. Disabled features are omitted. Thai and English lessons use the current application labels and actual business settings.

The Dashboard banner now describes the interactive walkthrough. The separate eiMaths guide has its own link. Tutorial can be reopened from the Affiliate navigation on every eligible portal page.

## Content and examples

- Explain the full referral link and the existing attribution rule; include the parent enrolment/payment process and K1–K2 / P1–P6 documents.
- Describe media search by title/description, downloads, the actual share chooser, and the device-share clipboard fallback.
- Explain applying custom dates, report income versus wallet balances, commission release/statuses, and withdrawal statuses in customer-facing language.
- Use the configured commission rate, holding period, attribution duration, minimum withdrawal, and support phone.
- Explain PromptPay and bank details as alternatives, the Affiliate login password, saved-account confirmation, and the existing 24-hour cooldown after changing account details.
- Provide nine readable, zoomable HTML examples: referral message, sharing, wallet, commission statuses, PromptPay, bank, saved accounts, withdrawal request, and withdrawal history. They are explicitly marked as fictional examples and contain no live personal or financial records or submittable forms.
- Use the generated decorative journey illustration on the welcome and completion lessons.

## Interaction and compatibility

- Preserve prior completed/skipped records; map legacy `code` and `password` lesson keys to current lessons.
- Auto-start only for an eligible user's first Dashboard visit. Pause and resume manually, restart, or jump to a topic.
- Keep Pause, Skip, and Escape usable during a pending or failed save. Cancel obsolete client operations and ignore late responses so they cannot navigate after dismissal. Remember a local resume point if saving fails.
- Open only the local sharing chooser as a preview; remove the preview on leaving the lesson. Stabilize the preview while measuring its position.
- On small screens, focus suitable groups of labelled fields. Add temporary scrolling room while the tutorial is open and keep the inert fixed top bar from covering the focus area. Restore the regular layout on close.
- Cache-bust changed Tutorial and Analytics assets using their file modification time.

## Verification

- PHP: `tests/Feature/Affiliate/AffiliateTutorialTest.php` — 10 tests, 178 assertions passed, including automatic start, access control, progress ownership, pause/resume, legacy progress, feature flags, current settings, translations, and rendering every tutorial destination.
- JavaScript: Tutorial, Tutorial resilience, Analytics page, Media library, and Media viewer — 26 tests passed. Covers failed saves, dismissal during a pending save, late responses, retry, translated status after a live refresh, layout placement, copy/share fallbacks, and viewers.
- PHP syntax and `git diff --check` passed.
- Authenticated Chrome against local Laravel: visited all 21 lessons across Dashboard, Guide, Media, Analytics, Payout Accounts, and Withdrawals; inspected the real share preview and all nine zoomed examples.
- Inspected desktop and 390 × 844 mobile layouts; verified the bank fields and tutorial panel have a 16px gap and that the fixed top bar does not obscure labels. Checked the focused net commission card on mobile.
- Verified a profile with no payout accounts receives explanatory withdrawal guidance and the example instead of a broken focus target.
- Verified topic jumps, Pause → reload (closed with saved `paused` state) → manual resume, and Finish → reload (`completed`, `done`, `autoStart: false`, dialog closed, top bar visible).
- Browser viewport override was reset after testing. No real payout account, withdrawal request, social post, or email was submitted by the walkthrough test. Feature tests used isolated in-memory SQLite and array mail.

### Commands

```powershell
$env:LOG_CHANNEL = 'stderr'
& 'C:\xampp\php-8.4.8\php.exe' -d extension_dir=C:\xampp\php-8.4.8\ext -d extension=php_pdo_sqlite.dll -d extension=php_sqlite3.dll vendor/bin/phpunit tests/Feature/Affiliate/AffiliateTutorialTest.php
node --test tests/JavaScript/affiliate-tutorial.test.mjs tests/JavaScript/affiliate-tutorial-resilience.test.mjs tests/JavaScript/affiliate-analytics-page.test.mjs tests/JavaScript/affiliate-media-library.test.mjs tests/JavaScript/affiliate-media-viewer.test.mjs
git diff --check
```

## Generated asset provenance

Current asset: `public/images/affiliate/tutorial/journey-v2.png`, 970 × 396 PNG. Replaced on 2026-09-10 with the second image supplied by the user (`codex-clipboard-cf0e8189-6e77-4c6a-b13c-c499dc9a1aa5.png`), copied without modifying its pixels. Both welcome and completion lessons use this image. The image keeps its full aspect ratio on desktop and mobile, and its URL includes a modification-time query so browsers load the replacement.

The original 1536 × 1024 artwork was generated with the built-in `image_gen.imagegen` tool, without reference images. No CLI image generation command was used. The original prompt below is retained as change history.

Final generation prompt:

> Use case: stylized-concept. Asset type: a production-ready decorative illustration for the eiMaths Affiliate step-by-step tutorial. Create a wide, clear, friendly 3D clay illustration on a pale warm ivory background. Show a simple orange link symbol beside a blue mathematics book, followed by a small rising statistics card and a blue wallet with a warm orange checkmark, connected by a discreet curved path. These represent sharing an educational referral link, viewing performance, and receiving commission. Palette: orange #F39A22, blue #1266AB, navy #24364B, ivory #FFF8EE. Clean soft lighting, rounded tactile shapes, generous negative space, high legibility when rendered at 400px wide. Landscape composition about 3:2. This is decorative only: no people, no numbers, no text, no logos, no watermark, no invented UI controls, no currency promises. Keep all objects centered inside a generous 15% margin.

## Release

This change was applied and verified locally in the `eiMaths` portal project. Deploy the PHP/views/translations, JavaScript/CSS, and new PNG together through the normal release process. No new migration is required; v2 uses the existing `affiliate_tutorial_progress` table. Clear/rebuild Laravel view caches as part of the normal deployment. Existing completed users can open Tutorial manually to see the new content.
