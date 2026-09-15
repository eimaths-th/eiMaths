import test from 'node:test';
import assert from 'node:assert/strict';
import { PlaceTutorial, TutorialIndex, TutorialSteps, TutorialUrl } from '../../public/js/affiliate-tutorial.js';

test('unknown/removed steps restart at welcome instead of indexing an absent feature', () => {
    const steps = [{ key: 'welcome' }, { key: 'link' }, { key: 'done' }];
    assert.equal(TutorialIndex(steps, 'link'), 1);
    assert.equal(TutorialIndex(steps, 'media'), 0);
    assert.equal(TutorialIndex(steps, undefined), 0);
});

test('cross-page continuation stays on the configured application origin', () => {
    assert.equal(TutorialUrl('/affiliate/media', 'http://127.0.0.1:8000'), 'http://127.0.0.1:8000/affiliate/media?affiliate_tutorial=1');
    assert.equal(TutorialUrl('/affiliate/payout-accounts', 'http://127.0.0.1:8000', 'starter'), 'http://127.0.0.1:8000/affiliate/payout-accounts?affiliate_tutorial=starter');
    assert.throws(() => TutorialUrl('https://example.org', 'http://127.0.0.1:8000'));
});

test('automatic onboarding uses four essentials while a manual launch keeps the full walkthrough', () => {
    const full = Array.from({ length: 21 }, (_, index) => ({ key: `full-${index + 1}` }));
    const starter = ['copy', 'commission', 'promptpay', 'withdraw'].map(key => ({ key }));
    const config = { steps: full, starterSteps: starter };
    assert.deepEqual(TutorialSteps(config, 'starter'), starter);
    assert.deepEqual(TutorialSteps(config, 'full'), full);
    assert.deepEqual(TutorialSteps({ steps: full }, 'starter'), full);
});

test('desktop panel uses space beside the highlighted control', () => {
    const rect = { left: 400, right: 600, top: 150, bottom: 200 };
    const position = PlaceTutorial(rect, { width: 392, height: 280 }, { width: 1440, height: 900 });
    assert.equal(position.left, 616);
    assert.equal(position.top, 150);
});

test('mobile panel fits below the target and stays inside the viewport', () => {
    const rect = { left: 20, right: 355, top: 60, bottom: 170 };
    const position = PlaceTutorial(rect, { width: 351, height: 320 }, { width: 375, height: 667 });
    assert.equal(position.left, 12);
    assert.equal(position.top, 186);
    assert.ok(position.top + 320 <= 655);
});

test('low targets position the panel above; missing targets are centered', () => {
    const panel = { width: 351, height: 280 };
    const viewport = { width: 375, height: 667 };
    const position = PlaceTutorial({ left: 12, right: 363, top: 570, bottom: 612 }, panel, viewport);
    assert.equal(position.top, 274);
    const centered = PlaceTutorial(null, panel, viewport);
    assert.deepEqual(centered, { left: 12, top: 193.5 });
});
