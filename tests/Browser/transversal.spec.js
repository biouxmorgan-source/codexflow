// Parcours transverses : rester connecté, lien d'évitement, affichage sur téléphone en texte
// très grand, fonction coupée par le MJ pendant qu'une page est ouverte.
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('TransversalSeeder'));

/** Oublie la session, comme un navigateur fermé longtemps : seul le cookie « se souvenir de moi » reste. */
async function closeBrowserSession(context) {
    const cookies = await context.cookies();
    await context.clearCookies();
    await context.addCookies(cookies.filter((cookie) => cookie.name.startsWith('remember_')));
}

test('“Remember me” keeps the person signed in after the browser session ends', async ({ browser }) => {
    // Deux comptes, pour rester sous la limite de connexions par minute et par adresse.
    for (const [email, remember] of [['e2e-mj-t@loremundi.test', true], ['e2e-joueur-t@loremundi.test', false]]) {
        const context = await browser.newContext();
        const page = await context.newPage();
        await login(page, email, { remember });

        await closeBrowserSession(context);
        await page.goto('/campagnes');

        if (remember) {
            await expect(page).toHaveURL(/\/campagnes$/);
            await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
        } else {
            await expect(page).toHaveURL(/\/login$/);
        }
        await context.close();
    }
});

test('the skip link is the first stop and jumps to the content', async ({ page }) => {
    await login(page, 'e2e-mj-t@loremundi.test');
    await page.goto('/campagnes');

    await page.keyboard.press('Tab');
    const skip = page.getByRole('link', { name: 'Aller au contenu' });
    await expect(skip).toBeFocused();
    await expect(skip).toBeInViewport();

    await page.keyboard.press('Enter');
    await expect(page.locator('#contenu')).toBeFocused();
});

test('no horizontal scrolling on a phone with very large text', async ({ browser }) => {
    const context = await browser.newContext({ viewport: { width: 360, height: 740 }, isMobile: true, hasTouch: true });
    const page = await context.newPage();
    await login(page, 'e2e-joueur-t@loremundi.test');

    await page.goto('/campagnes');
    await expect(page.locator('html')).toHaveAttribute('data-size', 'xlarge');
    await page.getByRole('link', { name: 'Campagne transverse e2e' }).first().click();
    await page.waitForLoadState('networkidle');
    const campaign = page.url();

    for (const path of ['/campagnes', campaign, '/preferences', '/aide', '/notifications', '/recherche?q=lanternes']) {
        await page.goto(path);
        await page.waitForLoadState('networkidle');
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        expect(overflow, `défilement horizontal sur ${path}`).toBeLessThanOrEqual(0);
    }
    await context.close();
});

test('a feature turned off by the GM is refused on a page that was already open', async ({ page }) => {
    await login(page, 'e2e-mj-t@loremundi.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne transverse e2e' }).first().click();
    await page.waitForURL(/\/campagnes\/\d+$/);
    await page.goto(page.url() + '/graphe');
    const select = page.locator('#graph-as');
    await expect(select).toBeVisible();

    seed('TransversalActionSeeder', { E2E_ACTION: 'disable-graph' });

    const refused = page.waitForResponse((response) => /\/livewire[^/]*\/update/.test(response.url()));
    await select.selectOption({ index: 1 });
    expect((await refused).status()).toBe(403);

    // Un rechargement ne rouvre pas la page non plus.
    const reload = await page.reload();
    expect(reload?.status()).toBe(403);
});
