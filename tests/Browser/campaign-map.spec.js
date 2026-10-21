// Carte de campagne : un clic sur un point ouvre la sous-carte, on remonte par le fil d'Ariane ;
// en « Placer des points », un clic pose un point et on le glisse (Alpine, invisible aux tests PHP).
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('CampaignMapSeeder'));

test('the GM opens a sub-map from a point, goes back up, then places and drags a point', async ({ page }) => {
    await login(page, 'e2e-mj-carte@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne carte e2e' }).first().click();
    await page.getByRole('link', { name: 'Carte de campagne' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Le continent' })).toBeVisible();

    // Explorer : le point mène à Port-Brume.
    await page.locator('[data-point]', { hasText: 'Port-Brume' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Port-Brume' })).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Cartes parentes' }).getByRole('link', { name: 'Le continent' })).toBeVisible();
    await page.getByRole('link', { name: '← Remonter' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'Le continent' })).toBeVisible();

    // En thème sombre aussi, le nom du point reste blanc sur son voile noir.
    await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'));
    const label = page.locator('[data-point]', { hasText: 'Port-Brume' }).locator('span').last();
    await expect(label).toHaveCSS('color', 'rgb(255, 255, 255)');
    await page.evaluate(() => document.documentElement.removeAttribute('data-theme'));

    // Zoomer : la carte grossit, les points gardent leur taille à l'écran.
    const pin = page.locator('[data-point]', { hasText: 'Port-Brume' });
    const before = await pin.boundingBox();
    await page.getByRole('button', { name: 'Zoomer', exact: true }).click();
    await expect(page.locator('[x-ref=canvas]')).toHaveAttribute('style', /scale\(1\.4\)/);
    const after = await pin.boundingBox();
    expect(Math.abs(after.width - before.width)).toBeLessThan(2);
    await page.getByRole('button', { name: 'Carte entière' }).click();
    await expect(page.locator('[x-ref=canvas]')).toHaveAttribute('style', /scale\(1\)/);

    // Placer un point là où l'on clique, le nommer.
    await page.getByRole('button', { name: 'Placer des points' }).click();
    await expect(page.locator('[x-ref=stage]')).toHaveAttribute('data-editing', '1');
    const stage = await page.locator('[x-ref=stage]').boundingBox();
    await page.mouse.click(stage.x + stage.width * 0.75, stage.y + stage.height * 0.5);
    await expect(page.locator('#point-label')).toHaveValue('Nouveau point');
    await page.fill('#point-label', 'La tour');
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    const tower = page.locator('[data-point]', { hasText: 'La tour' });
    await expect(tower).toBeVisible();
    expect(parseFloat(await tower.evaluate((el) => el.style.left))).toBeCloseTo(75, 0);

    // En mode placement, cliquer sur Port-Brume le sélectionne sans ouvrir la sous-carte.
    await page.locator('[data-point]', { hasText: 'Port-Brume' }).click();
    await expect(page.locator('#point-label')).toHaveValue('Port-Brume');
    await expect(page.getByRole('heading', { level: 1, name: 'Le continent' })).toBeVisible();

    // Glisser la tour vers la gauche : la position tient après rechargement.
    const box = await tower.boundingBox();
    await page.mouse.move(box.x + box.width / 2, box.y + 10);
    await page.mouse.down();
    await page.mouse.move(stage.x + stage.width * 0.5, stage.y + stage.height * 0.3, { steps: 8 });
    await page.mouse.up();
    await page.waitForLoadState('networkidle');
    await page.reload();
    const left = await page.locator('[data-point]', { hasText: 'La tour' }).evaluate((el) => parseFloat(el.style.left));
    expect(left).toBeCloseTo(50, 0);
});
