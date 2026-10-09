// Outils partagés des tests navigateur.
import { execSync } from 'node:child_process';

export const PASSWORD = 'navigateur-e2e-42';

/** Lance un seeder de tests/Browser (database/seeders/Browser), avec des variables d'environnement. */
export function seed(seeder, env = {}) {
    execSync(`php artisan db:seed --class='Database\\Seeders\\Browser\\${seeder}' --force`, { stdio: 'inherit', env: { ...process.env, ...env } });
}

export async function login(page, email, { remember = false } = {}) {
    await page.goto('/login');
    await page.fill('input[name=email]', email);
    await page.fill('input[name=password]', PASSWORD);
    if (remember) {
        await page.check('input[name=remember]');
    }
    await page.click('button[type=submit]');
    await page.waitForURL((url) => !url.pathname.startsWith('/login'));
}
