// « Organiser l'écran » : le MJ glisse un bloc du mode Session dans l'autre colonne, le masque,
// le fait revenir, puis revient à la disposition par défaut (Alpine, invisible aux tests PHP).
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('ScreenLayoutSeeder'));

const order = (page, column) => page.locator(`[data-screen-column=${column}] > [data-block]`).evaluateAll((blocks) => blocks.map((b) => b.dataset.block));

test('the GM drags a Session block to the other column, hides it, then resets the layout', async ({ page }) => {
    await login(page, 'e2e-mj-ecrans@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne écrans e2e' }).first().click();
    await page.getByRole('link', { name: 'Mode Session →' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Session 1' })).toBeVisible();
    await page.getByRole('button', { name: 'Organiser l’écran' }).click();
    await expect(page.getByRole('button', { name: 'Terminé' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Déplacer « Musique »' })).toBeVisible();

    // Glisser « Musique » par sa poignée au-dessus de « Maintenant », dans la colonne principale.
    const handle = await page.getByRole('button', { name: 'Déplacer « Musique »' }).boundingBox();
    const target = await page.locator('[data-block=now]').boundingBox();
    await page.mouse.move(handle.x + handle.width / 2, handle.y + handle.height / 2);
    await page.mouse.down();
    await page.mouse.move(target.x + 40, target.y + 30, { steps: 8 });
    await page.mouse.move(target.x + 40, target.y + 10, { steps: 4 });
    const dropped = page.waitForResponse((response) => response.url().includes('/update'));
    await page.mouse.up();
    await dropped;
    await expect.poll(() => order(page, 'main')).toEqual(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes']);

    // Les flèches : « Épinglé » remonte d'un cran.
    const saved = page.waitForResponse((response) => response.url().includes('/update'));
    await page.getByRole('button', { name: 'Monter « Épinglé »' }).click();
    await saved;
    await expect.poll(() => order(page, 'side')).toEqual(['to-play', 'table', 'pins', 'give']);

    // La disposition tient après rechargement.
    await page.reload();
    expect(await order(page, 'main')).toEqual(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes']);
    expect(await order(page, 'side')).toEqual(['to-play', 'table', 'pins', 'give']);

    // Masquer puis réafficher.
    await page.getByRole('button', { name: 'Organiser l’écran' }).click();
    await page.locator('[data-block=music]').getByRole('button', { name: 'Masquer' }).click();
    await expect(page.locator('[data-block=music]')).toHaveCount(0);
    await page.getByRole('button', { name: 'Afficher « Musique »' }).click();
    await expect(page.locator('[data-block=music]')).toHaveCount(1);

    // Disposition par défaut.
    page.once('dialog', (dialog) => dialog.accept());
    await Promise.all([page.waitForEvent('load'), page.getByRole('button', { name: 'Disposition par défaut' }).click()]);
    await expect(page.getByRole('button', { name: 'Organiser l’écran' })).toBeVisible();
    await expect.poll(() => order(page, 'main')).toEqual(['now', 'cards', 'secrets', 'rules-docs', 'notes']);
    expect(await order(page, 'side')).toEqual(['to-play', 'music', 'table', 'give', 'pins']);
});
