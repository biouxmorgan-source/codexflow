// « Organiser l'écran » : le MJ glisse un bloc du mode Session dans l'autre colonne, change sa largeur, le masque,
// le fait revenir, puis revient à la disposition par défaut ; il range aussi la page d'une carte
// (Alpine et placement en colonnes, invisibles aux tests PHP).
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('ScreenLayoutSeeder'));

const order = (page) => page.locator('[data-screen-grid] > [data-block]').evaluateAll((blocks) => blocks.map((b) => b.dataset.block));
const box = (page, block) => page.locator(`[data-block=${block}]`).boundingBox();
const grid = (page) => page.locator('[data-screen-grid]').boundingBox();
const saved = (page) => page.waitForResponse((response) => response.url().includes('/update'));

test('the GM drags a Session block to the other column, widens it, hides it, then resets the layout', async ({ page }) => {
    await login(page, 'e2e-mj-ecrans@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne écrans e2e' }).first().click();
    await page.getByRole('link', { name: 'Mode Session →' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Session 1' })).toBeVisible();

    // Par défaut : la colonne principale (deux tiers) à gauche, « À jouer » à droite, en haut.
    const area = await grid(page);
    await expect.poll(async () => (await box(page, 'to-play')).x > area.x + area.width / 2).toBe(true);
    expect(Math.abs((await box(page, 'to-play')).y - (await box(page, 'now')).y)).toBeLessThan(4);

    await page.getByRole('button', { name: 'Organiser l’écran' }).click();
    await expect(page.getByRole('button', { name: 'Terminé' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Déplacer « Musique »' })).toBeVisible();

    // Glisser « Musique » par sa poignée au-dessus de « Maintenant », dans la colonne de gauche.
    const handle = await page.getByRole('button', { name: 'Déplacer « Musique »' }).boundingBox();
    const target = await box(page, 'now');
    await page.mouse.move(handle.x + handle.width / 2, handle.y + handle.height / 2);
    await page.mouse.down();
    await page.mouse.move(target.x + 40, target.y + 30, { steps: 8 });
    await page.mouse.move(target.x + 40, target.y + 10, { steps: 4 });
    const dropped = saved(page);
    await page.mouse.up();
    await dropped;
    await expect.poll(() => order(page)).toEqual(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes', 'to-play', 'table', 'give', 'pins']);
    await expect(page.locator('[data-block=music]')).toHaveAttribute('data-column', '1');
    expect((await box(page, 'music')).y).toBeLessThan((await box(page, 'now')).y);

    // Les flèches : « Épinglé » remonte d'un cran dans sa colonne.
    let response = saved(page);
    await page.getByRole('button', { name: 'Monter « Épinglé »' }).click();
    await response;
    await expect.poll(() => order(page)).toEqual(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes', 'to-play', 'table', 'pins', 'give']);

    // « Musique » sur toute la largeur : les trois colonnes, au-dessus de tout.
    response = saved(page);
    await page.getByRole('combobox', { name: 'Largeur de « Musique »' }).selectOption('3');
    await response;
    await expect.poll(async () => (await box(page, 'music')).width).toBeGreaterThan(area.width - 40);
    await expect.poll(async () => (await box(page, 'to-play')).y > (await box(page, 'music')).y + (await box(page, 'music')).height).toBe(true);

    // La disposition tient après rechargement.
    // Hors mode « Organiser », les blocs vides (fiches, indices, règles) ne s'affichent pas.
    await page.reload();
    expect(await order(page)).toEqual(['music', 'now', 'notes', 'to-play', 'table', 'pins', 'give']);
    await expect(page.locator('[data-block=music]')).toHaveAttribute('data-span', '3');
    await expect.poll(async () => (await box(page, 'music')).width).toBeGreaterThan(area.width - 40);

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
    await expect.poll(() => order(page)).toEqual(['now', 'notes', 'to-play', 'music', 'table', 'give', 'pins']);
});

test('the GM puts the map across the whole page and the tokens side by side under it', async ({ page }) => {
    await login(page, 'e2e-mj-ecrans@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne écrans e2e' }).first().click();
    await page.waitForURL(/\/campagnes\/\d+$/);
    await page.goto(`${new URL(page.url()).pathname}/cartes`);
    await page.locator('a[href*="/cartes/"]').filter({ hasText: 'Crypte' }).first().click();
    await expect(page.getByRole('heading', { level: 1, name: 'Crypte' })).toBeVisible();

    // Par défaut : la carte sur deux colonnes, les jetons à sa droite.
    const area = await grid(page);
    await expect.poll(async () => (await box(page, 'tokens')).x > (await box(page, 'map')).x + (await box(page, 'map')).width).toBe(true);

    await page.getByRole('button', { name: 'Organiser l’écran' }).click();
    let response = saved(page);
    await page.getByRole('combobox', { name: 'Largeur de « Carte »' }).selectOption('3');
    await response;
    await expect.poll(async () => (await box(page, 'map')).width).toBeGreaterThan(area.width - 40);

    // Les jetons passent dans la colonne de gauche, les gabarits restent à droite : côte à côte, sous la carte.
    for (const column of ['2', '1']) {
        response = saved(page);
        await page.getByRole('button', { name: 'Colonne de gauche pour « Jetons »' }).click();
        await response;
        await expect(page.locator('[data-block=tokens]')).toHaveAttribute('data-column', column);
    }
    await expect.poll(async () => {
        const [map, tokens, templates] = [await box(page, 'map'), await box(page, 'tokens'), await box(page, 'templates')];
        return tokens.y > map.y + map.height && templates.x > tokens.x + tokens.width && Math.abs(templates.y - tokens.y) < 4;
    }).toBe(true);

    await page.getByRole('button', { name: 'Terminé' }).click();
    await page.reload();
    await expect(page.locator('[data-block=map]')).toHaveAttribute('data-span', '3');
    await expect.poll(async () => (await box(page, 'tokens')).y > (await box(page, 'map')).y + (await box(page, 'map')).height).toBe(true);
});
