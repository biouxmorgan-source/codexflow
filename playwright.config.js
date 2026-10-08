// Tests navigateur (tests/Browser) : ce que les tests PHP ne voient pas, les clics qui passent par Alpine.
// En local : `npm run test:browser` (serveur lancé s'il ne tourne pas déjà ; E2E_BASE_URL pour en viser un autre).
import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8000';

export default defineConfig({
    testDir: 'tests/Browser',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL,
        locale: 'fr-FR',
        trace: 'retain-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: process.env.E2E_BASE_URL ? undefined : {
        command: 'php artisan serve --port=8000',
        url: baseURL,
        reuseExistingServer: true,
    },
});
