// Remplacer le fichier d'un document : choisir un fichier puis cliquer « Remplacer »
// (le bouton doit redevenir cliquable après l'envoi, ce que les tests PHP ne voient pas).
import { deflateSync } from 'node:zlib';
import { expect, test } from '@playwright/test';
import { login, seed } from './support.js';

test.beforeEach(() => seed('CampaignMapSeeder'));

/** PNG uni de la taille demandée, construit sans dépendance. */
function png(width, height) {
    const crcTable = Array.from({ length: 256 }, (_, n) => {
        let c = n;
        for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        return c >>> 0;
    });
    const crc = (buf) => {
        let c = 0xffffffff;
        for (const b of buf) c = crcTable[(c ^ b) & 0xff] ^ (c >>> 8);
        return (c ^ 0xffffffff) >>> 0;
    };
    const chunk = (type, data) => {
        const body = Buffer.concat([Buffer.from(type), data]);
        const out = Buffer.alloc(body.length + 8);
        out.writeUInt32BE(data.length, 0);
        body.copy(out, 4);
        out.writeUInt32BE(crc(body), body.length + 4);
        return out;
    };
    const header = Buffer.alloc(13);
    header.writeUInt32BE(width, 0);
    header.writeUInt32BE(height, 4);
    header[8] = 8;
    header[9] = 2;
    const row = Buffer.concat([Buffer.from([0]), Buffer.alloc(width * 3, 200)]);
    const pixels = deflateSync(Buffer.concat(Array.from({ length: height }, () => row)));

    return Buffer.concat([Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]), chunk('IHDR', header), chunk('IDAT', pixels), chunk('IEND', Buffer.alloc(0))]);
}

test('the GM replaces the image of a map document with another size', async ({ page }) => {
    await login(page, 'e2e-mj-carte@sagawyn.test');
    await page.goto('/campagnes');
    await page.getByRole('link', { name: 'Campagne carte e2e' }).first().click();
    await page.waitForURL(/\/campagnes\/\d+$/);
    await page.goto(`${page.url()}/documents`);
    await page.getByRole('link', { name: 'Port-Brume' }).first().click();

    await expect(page.getByText('Image actuelle : 600 × 600 pixels.')).toBeVisible();
    await page.setInputFiles('#replacement', { name: 'port-brume-v2.png', mimeType: 'image/png', buffer: png(300, 200) });
    const button = page.getByRole('button', { name: 'Remplacer', exact: true });
    await expect(button).toBeEnabled();
    await button.click();

    await expect(page.getByText('Fichier remplacé.')).toBeVisible();
    await expect(page.getByText('port-brume-v2.png')).toBeVisible();
    await expect(page.getByText('Image actuelle : 300 × 200 pixels.')).toBeVisible();
});
