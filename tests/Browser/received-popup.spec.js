// Fenêtre « reçu » : un joueur qui reçoit une information ou un objet la voit s'ouvrir,
// passe à la suivante, puis va la voir sur sa fiche.
import { execSync } from 'node:child_process';
import { expect, test } from '@playwright/test';

test.beforeAll(() => {
    execSync("php artisan db:seed --class='Database\\Seeders\\Browser\\ReceivedPopupSeeder' --force", { stdio: 'inherit' });
});

test('the player goes through what they received, then opens it on their sheet', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name=email]', 'e2e-joueuse@loremundi.test');
    await page.fill('input[name=password]', 'navigateur-e2e-42');
    await page.click('button[type=submit]');

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog).toContainText('Le phare est éteint');
    await expect(dialog).toContainText('2 en attente');

    // « Suivant » : la première est lue, la seconde s'affiche.
    await dialog.getByRole('button', { name: 'Suivant' }).click();
    await expect(dialog).toContainText('Clé de bronze');
    await expect(dialog.getByRole('button', { name: "C'est noté" })).toBeVisible();

    // « Voir sur ma fiche » : la fenêtre se ferme sur la page du personnage.
    await dialog.getByRole('button', { name: 'Voir sur ma fiche' }).click();
    await expect(page).toHaveURL(/\/campagnes\/\d+\/personnages\/\d+/);
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Ilse Varn' })).toBeVisible();
    await expect(page.getByText('Clé de bronze').first()).toBeVisible();

    // Plus rien en attente après un rechargement.
    await page.reload();
    await expect(page.getByRole('dialog')).toHaveCount(0);
});
