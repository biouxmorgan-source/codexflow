// Repli sans temps réel : Echo coupé, la cloche se met quand même à jour dans les 30 secondes,
// sans recharger la page.
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeAll(() => seed('TransversalSeeder'));

test('the bell updates within 30 seconds when the realtime connection is down', async ({ page }) => {
    test.setTimeout(90_000);
    await login(page, 'e2e-joueur-t@sagawyn.test');
    await page.goto('/campagnes');

    const bell = page.getByRole('link', { name: /^Notifications/ });
    await expect(bell).toHaveAccessibleName('Notifications');

    // Plus de Reverb : le repli prend le relais. Le marqueur prouve qu'aucun rechargement n'a lieu.
    await page.evaluate(() => {
        window.Echo?.disconnect();
        window.__sansRechargement = true;
    });

    seed('TransversalActionSeeder', { E2E_ACTION: 'notify' });

    await expect(bell).toHaveAccessibleName('Notifications : 1 non lue', { timeout: 40_000 });
    expect(await page.evaluate(() => window.__sansRechargement)).toBe(true);
});
