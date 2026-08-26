#!/usr/bin/env node
/**
 * Upscale homepage slider PNGs (1520×449) to 1920×567.
 *
 * Usage:
 *   node scripts/fix-slider-pngs.mjs
 */

import sharp from 'sharp';
import { stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const uploadDir = path.join(projectRoot, 'upload');
const sourcesDir = path.join(__dirname, 'slider-sources');

const TARGET_W = 1920;
const TARGET_H = 567;
const SOURCE_W = 1520;

const slides = [
    {
        elementId: 10550,
        fileId: 37431,
        relPath: 'iblock/f89/c9hvck8kkko1vu5g4eeb9b4jbhuf5j6z.png',
        source: 'delivery-1520x449.png',
        mode: 'resize',
    },
    {
        elementId: 10551,
        fileId: 37430,
        relPath: 'iblock/aa1/ehwhdr53vsp25uzuiavgo1uuiu1p0o3m.png',
        source: 'brand-letterboxed-1920x567.png',
        mode: 'crop-letterbox',
    },
];

async function upscale(slide) {
    const sourcePath = path.join(sourcesDir, slide.source);
    const outputPath = path.join(uploadDir, slide.relPath);

    let pipeline = sharp(sourcePath);
    const meta = await pipeline.metadata();

    if (slide.mode === 'crop-letterbox' && meta.width > SOURCE_W) {
        const bar = Math.round((meta.width - SOURCE_W) / 2);
        pipeline = sharp(sourcePath).extract({
            left: bar,
            top: 0,
            width: SOURCE_W,
            height: meta.height,
        });
    }

    await pipeline
        .resize(TARGET_W, TARGET_H, { fit: 'fill', kernel: 'lanczos3' })
        .png()
        .toFile(outputPath);

    const fileStat = await stat(outputPath);
    return { ...slide, bytes: fileStat.size };
}

async function main() {
    const results = [];
    for (const slide of slides) {
        process.stdout.write(`Processing ${slide.relPath}...\n`);
        results.push(await upscale(slide));
    }

    process.stdout.write('\nSQL:\n');
    for (const item of results) {
        process.stdout.write(
            `UPDATE b_file SET WIDTH=${TARGET_W}, HEIGHT=${TARGET_H}, FILE_SIZE=${item.bytes} WHERE ID=${item.fileId};\n`,
        );
    }
    process.stdout.write(
        "UPDATE b_iblock_element SET PREVIEW_TEXT='' WHERE ID=10550;\n",
    );
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
