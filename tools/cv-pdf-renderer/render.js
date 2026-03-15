#!/usr/bin/env node

const fs = require('fs/promises');
const path = require('path');
const { chromium } = require('playwright');
const { PDFDocument } = require('pdf-lib');

function parseArgs(argv) {
  const parsed = {};
  for (let i = 0; i < argv.length; i += 1) {
    const token = argv[i];
    if (!token.startsWith('--')) {
      continue;
    }
    const key = token.slice(2);
    const value = argv[i + 1];
    if (typeof value === 'undefined' || value.startsWith('--')) {
      parsed[key] = '1';
      continue;
    }
    parsed[key] = value;
    i += 1;
  }
  return parsed;
}

async function waitForImages(page) {
  await page.evaluate(async () => {
    const images = Array.from(document.images || []);
    await Promise.all(images.map((img) => {
      if (img.complete) {
        return Promise.resolve();
      }
      return new Promise((resolve) => {
        const done = () => resolve();
        img.addEventListener('load', done, { once: true });
        img.addEventListener('error', done, { once: true });
      });
    }));
  });
}

async function addImagePage(pdfDoc, pngBytes) {
  const embedded = await pdfDoc.embedPng(pngBytes);
  const targetWidth = 595.28;
  const targetHeight = 841.89;
  const page = pdfDoc.addPage([targetWidth, targetHeight]);
  const scale = Math.min(targetWidth / embedded.width, targetHeight / embedded.height);
  const width = embedded.width * scale;
  const height = embedded.height * scale;
  const x = (targetWidth - width) / 2;
  const y = (targetHeight - height) / 2;
  page.drawImage(embedded, { x, y, width, height });
}

async function renderImageFallbackPdf(page, outputPath) {
  const pdfDoc = await PDFDocument.create();
  const locator = page.locator('article.page');
  const count = await locator.count();

  if (count > 0) {
    for (let index = 0; index < count; index += 1) {
      const pngBytes = await locator.nth(index).screenshot({
        type: 'png',
        omitBackground: false,
      });
      await addImagePage(pdfDoc, pngBytes);
    }
  } else {
    const pngBytes = await page.screenshot({
      type: 'png',
      fullPage: true,
      omitBackground: false,
    });
    await addImagePage(pdfDoc, pngBytes);
  }

  const pdfBytes = await pdfDoc.save();
  await fs.writeFile(outputPath, pdfBytes);
}

async function renderPdf(inputPath, outputPath, chromiumPath) {
  const html = await fs.readFile(inputPath, 'utf8');
  const browser = await chromium.launch({
    headless: true,
    executablePath: chromiumPath || undefined,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  let mode = 'page.pdf';
  let fallbackReason = '';

  try {
    const page = await browser.newPage({
      viewport: { width: 1280, height: 1810 },
      deviceScaleFactor: 1,
    });

    await page.setContent(html, { waitUntil: 'load' });
    await waitForImages(page);
    await page.emulateMedia({ media: 'print' });
    await page.waitForTimeout(150);

    try {
      await page.pdf({
        path: outputPath,
        format: 'A4',
        printBackground: true,
        preferCSSPageSize: true,
        margin: { top: '0', right: '0', bottom: '0', left: '0' },
      });
    } catch (error) {
      mode = 'image-fallback';
      fallbackReason = error instanceof Error ? error.message : String(error || 'unknown page.pdf failure');
      await renderImageFallbackPdf(page, outputPath);
    }

    return { mode, fallbackReason };
  } finally {
    await browser.close();
  }
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const inputPath = args.input ? path.resolve(args.input) : '';
  const outputPath = args.output ? path.resolve(args.output) : '';
  const chromiumPath = args['chromium-path']
    ? path.resolve(args['chromium-path'])
    : (process.env.CMN_CV_PDF_CHROMIUM_PATH || '').trim();

  if (!inputPath || !outputPath) {
    throw new Error('Missing required --input or --output argument.');
  }

  const result = await renderPdf(inputPath, outputPath, chromiumPath);
  process.stdout.write(JSON.stringify({ ok: true, mode: result.mode, fallbackReason: result.fallbackReason }) + '\n');
}

main().catch((error) => {
  const message = error instanceof Error ? error.stack || error.message : String(error || 'unknown renderer failure');
  process.stderr.write(message + '\n');
  process.exit(1);
});
