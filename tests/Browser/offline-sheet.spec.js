// Mode hors ligne du joueur : la fiche gardée sur l'appareil se modifie sans réseau, puis
// tout part au retour de la connexion sans effacer ce que le MJ a changé entre-temps.
import { execSync } from 'node:child_process';
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('OfflineSheetSeeder'));

/** Le MJ modifie la fiche pendant que la joueuse est hors ligne (soin du maximum de PV, humeur). */
function gameMasterEdits() {
    execSync(`php artisan tinker --execute="\\$e = App\\Models\\Entity::where('name', 'Ilse Varn')->latest('id')->first(); \\$d = \\$e->campaign->gameSystem->fieldDefinitions()->pluck('id', 'name'); \\$e->setFieldValues([\\$d['PV'] => ['value' => 12, 'max' => 14], \\$d['Humeur'] => 'Inquiète']); \\$e->save();"`, { stdio: 'inherit' });
}

test('the player edits her sheet offline and it syncs without erasing the GM', async ({ page, context }) => {
    await login(page, 'e2e-joueuse-hl@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne hors ligne e2e' }).first().click();
    await page.waitForURL(/\/personnages\/\d+$/);
    const sheet = page.url();

    // Le service worker prend la main, puis garde la fiche sur l'appareil.
    await page.evaluate(() => navigator.serviceWorker.ready);
    await page.reload();
    await expect.poll(() => page.evaluate(() => navigator.serviceWorker.controller !== null)).toBe(true);
    await page.reload();

    await context.setOffline(true);
    await page.goto(sheet);
    await expect(page.getByRole('heading', { name: 'Mode hors ligne' })).toBeVisible();

    const minus = page.getByRole('button', { name: 'Retirer le montant à PV' });
    await minus.click();
    await minus.click();
    await expect(page.getByText('10 / 12').filter({ visible: true })).toBeVisible();

    await page.getByRole('button', { name: 'Modifier mes champs hors ligne' }).click();
    await page.getByLabel('Métier').fill('Antiquaire');
    await page.getByLabel('Humeur').fill('Furieuse');
    await page.getByRole('button', { name: 'Garder sur cet appareil' }).click();

    await page.getByLabel('Note hors ligne').fill('Le gardien du phare ment.');
    await page.getByRole('button', { name: 'Ajouter la note' }).first().click();
    await expect(page.getByText('PV : −2')).toBeVisible();
    await expect(page.getByText('Métier → Antiquaire')).toBeVisible();

    // Toujours là après un rechargement sans réseau.
    await page.reload();
    await expect(page.getByText('10 / 12').filter({ visible: true })).toBeVisible();

    gameMasterEdits();
    await context.setOffline(false);

    await expect(page.getByText('Synchronisé : PV, Métier mis à jour.')).toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Le MJ a modifié entre-temps Humeur')).toBeVisible();
    await expect(page.getByText('10 / 14').filter({ visible: true })).toBeVisible();
    await expect(page.getByText('Antiquaire')).toBeVisible();
    await expect(page.getByText('Inquiète')).toBeVisible();
    await expect(page.getByText('Le gardien du phare ment.')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'En attente d’envoi' })).toBeHidden();
});
