#!/usr/bin/env node
/**
 * Normalizes homepage slider banners to 1920x567 (letterbox on #afbccd).
 *
 * Usage:
 *   node scripts/generate-slider-images.mjs
 */

import { chromium } from 'playwright';
import { readFile, rename, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const uploadDir = path.join(projectRoot, 'upload');

const SLIDER_WIDTH = 1920;
const SLIDER_HEIGHT = 567;
const BG = '#afbccd';

const slides = [
    {
        elementId: 10550,
        fileId: 37431,
        relPath: 'iblock/f89/c9hvck8kkko1vu5g4eeb9b4jbhuf5j6z.png',
        format: 'png',
    },
    {
        elementId: 10551,
        fileId: 37430,
        relPath: 'iblock/aa1/ehwhdr53vsp25uzuiavgo1uuiu1p0o3m.png',
        format: 'png',
    },
];

async function letterboxSlide(browser, slide) {
    const inputPath = path.join(uploadDir, slide.relPath);
    const tempPath = `${inputPath}.tmp`;
    const source = await readFile(inputPath);
    const mime = slide.format === 'jpg' ? 'jpeg' : 'png';
    const fileUrl = `data:image/${mime};base64,${source.toString('base64')}`;

    const page = await browser.newPage({
        viewport: { width: SLIDER_WIDTH, height: SLIDER_HEIGHT },
        deviceScaleFactor: 1,
    });

    try {
        await page.setContent(
            `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  html, body {
    margin: 0;
    width: ${SLIDER_WIDTH}px;
    height: ${SLIDER_HEIGHT}px;
    background: ${BG};
  }
  body {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }
</style>
</head>
<body>
  <img src="${fileUrl}" alt="">
</body>
</html>`,
            { waitUntil: 'networkidle' },
        );

        await page.screenshot({
            path: tempPath,
            type: slide.format === 'jpg' ? 'jpeg' : 'png',
            quality: slide.format === 'jpg' ? 92 : undefined,
            animations: 'disabled',
        });

        await rename(tempPath, inputPath);
        const fileStat = await stat(inputPath);

        return {
            ...slide,
            inputPath,
            bytes: fileStat.size,
        };
    } finally {
        await page.close();
    }
}

async function writeManifest(results) {
    const manifestPath = path.join(uploadDir, 'slider-images-manifest.json');
    const manifest = {
        generatedAt: new Date().toISOString(),
        size: { width: SLIDER_WIDTH, height: SLIDER_HEIGHT },
        slides: results.map((item) => ({
            elementId: item.elementId,
            fileId: item.fileId,
            path: `/upload/${item.relPath.replace(/\\/g, '/')}`,
            bytes: item.bytes,
        })),
    };

    await writeFile(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`, 'utf8');

    return manifestPath;
}

async function main() {
    const browser = await chromium.launch({ headless: true });

    try {
        const results = [];
        for (const slide of slides) {
            process.stdout.write(`Rendering ${slide.relPath}...\n`);
            results.push(await letterboxSlide(browser, slide));
        }

        const manifestPath = await writeManifest(results);

        process.stdout.write('\nUpdated slider images:\n');
        for (const item of results) {
            process.stdout.write(`- ${item.relPath} (${item.bytes} bytes)\n`);
        }
        process.stdout.write(`\nManifest: ${manifestPath}\n`);
        process.stdout.write('\nRun SQL on DB:\n');
        for (const item of results) {
            process.stdout.write(
                `UPDATE b_file SET WIDTH=${SLIDER_WIDTH}, HEIGHT=${SLIDER_HEIGHT}, FILE_SIZE=${item.bytes} WHERE ID=${item.fileId};\n`,
            );
        }
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
