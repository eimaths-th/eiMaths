# Affiliate Tutorial — 3 September 2026

Implemented in the `eiMaths` Laravel application, served locally at `/affiliate/dashboard`.

## User experience

- Orange **Tutorial — วิธีใช้งาน** button on Dashboard; native modal with a spotlight on the real control, Thai/English explanations, progress, Back, Next, Skip, and Finish.
- Up to 19 steps: welcome → referral code → referral URL → copy → guide → media folder → search → download → share → date range → analytics → available/reserved balances → commission history → PromptPay → bank account → password confirmation → withdrawal → withdrawal history → finish.
- Media and analytics steps follow the existing feature flags. A missing media card or withdrawal form shows an explanation and a fallback target.
- Automatically opens on the first Dashboard visit of an eligible affiliate without saved tutorial history. This also introduces the tutorial once to existing eligible accounts after rollout.
- Progress belongs to the authenticated affiliate profile and is stored in the database. Reloading offers Continue/Restart. Skipping or completing suppresses automatic reopening; the Dashboard button starts another walkthrough.
- Cross-page navigation saves the destination step first. A failed save leaves the current step open with a retry message. The walkthrough does not submit payout-account forms, request money, download files, or send social shares.

## Implementation and deployment

- `AffiliateTutorialService` owns the steps, feature filtering, translations, and persisted state. `AffiliateTutorialController` accepts only version, action, and step; ownership comes from the signed-in session.
- `POST /affiliate/tutorial/progress` uses the existing authentication, affiliate access, locale, and wallet-feature middleware, plus web CSRF protection.
- A plain JavaScript module and isolated CSS implement the overlay; no npm dependency or build step was added. Native modal focus containment, Escape to skip, responsive positioning, and resize alignment are included.
- New table: `affiliate_tutorial_progress`, one record per affiliate profile, with status, step, version, and timestamps.

The new migration has already been applied to the local database. Apply it before enabling the updated views in another environment:

```sh
php artisan migrate --path=database/migrations/2026_09_03_000001_create_affiliate_tutorial_progress_table.php
```

Use the application's normal production deployment procedure and migration approval. Deploy the new `public/css`, `public/js`, and `public/images/affiliate/tutorial` files with the PHP/views/translations.

## Verification

- PHP feature tests: **10 passed, 79 assertions** (`AffiliateTutorialTest` and existing `AffiliateGuideTest`). Covered first visit, progress/resume/replay, skip, separate owners, rejected client ownership fields, authentication/approval, invalid steps, feature flags, translation coverage, and absence of financial writes.
- JavaScript: **5 tests passed** for step fallback, same-origin navigation, and desktop/mobile panel positioning.
- Browser: completed all **19 steps** on the authenticated local account; tested Back across routes, reload/Continue, Finish then reload without auto-open, manual replay, Skip, and the no-payout-account state.
- Checked 375×812 and 1440×900 responsive viewports. Fixed off-screen targets when resizing between breakpoints. Restored the user's original viewport.
- The wider, existing `AffiliateTranslationStructureTest` reports an unrelated mismatch between the existing Thai/English media translation keys in `affiliate.php`; these files are unchanged. The new tutorial translations have matching keys.

Commands used on this machine (SQLite drivers are enabled only for the test process):

```powershell
C:\xampp\php-8.4.8\php.exe -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter='AffiliateTutorialTest|AffiliateGuideTest' --no-progress
node --test tests/JavaScript/affiliate-tutorial.test.mjs
git diff --check
```

Screenshots:

- [Desktop spotlight](affiliate-tutorial/desktop-focus.png)
- [Mobile welcome](affiliate-tutorial/mobile-welcome.png)
- [Mobile spotlight](affiliate-tutorial/mobile-focus.png)

## Illustration assets

Saved under `public/images/affiliate/tutorial/`:

- `welcome.png`: original AI-generated decorative illustration, 1536×1024, copied from the generated output. Orange referral link, blue math book, and wallet. No textual UI or invented labels.
- `media-folders.png`: screenshot of the existing local media-folder interface.
- `payout-form.png`: screenshot of empty payout-account forms; visible number is the existing input placeholder. No real banking information was entered or captured.

The screenshots are examples; live spotlight targets remain the source for actual controls. Refresh these images if their source pages change.

Image generation prompt (single generation, no revisions):

> Use case: stylized-concept. Asset type: welcome illustration for the eiMaths Affiliate interactive onboarding inside a Thai educational web portal. Create one polished friendly soft 3D clay illustration, wide landscape composition 3:2, on a very pale warm ivory background. A small orange chain-link symbol next to a clean blue framed abstract course card containing a small open mathematics book (no text), connected with a short gently curved blue trail to a rounded wallet with a subtle checkmark. Visual idea: share an educational referral link, support learning, receive a commission. Brand-informed palette from the existing portal: warm orange #F39A22, blue #1266AB, deep navy #24364B, ivory #FFF8EE. Keep objects centered, modest detail, generous 15% blank padding on all edges, soft studio shadows, inviting and calm. This is decorative art beside live instructional text, not a screenshot, UI mockup or infographic. No people, no numbers, no text, no typography, no invented logos, no watermark, no busy background.
