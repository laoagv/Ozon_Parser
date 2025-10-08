const { chromium } = require('playwright');

(async () => {
  const url = process.argv[2];
  if (!url) {
    console.error('Ошибка: не передан URL');
    process.exit(1);
  }

  const userDataDir = '/tmp/playwright-profile';

  // Правильная установка User-Agent при создании контекста
  const context = await chromium.launchPersistentContext(userDataDir, {
    headless: true,
    viewport: { width: 1280, height: 800 },
    userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.5993.90 Safari/537.36',
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-accelerated-2d-canvas',
      '--disable-gpu',
      '--window-size=1280,800'
    ]
  });

  const page = await context.newPage();

  await page.setExtraHTTPHeaders({
    'Accept-Language': 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    'Referer': 'https://www.google.com/'
  });

  await page.addInitScript(() => {
    Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
    Object.defineProperty(navigator, 'languages', { get: () => ['ru-RU','ru'] });
    Object.defineProperty(navigator, 'plugins', { get: () => [1,2,3] });
    window.chrome = { runtime: {} };
  });

  await page.goto(url, { waitUntil: 'networkidle', timeout: 45000 });

  await page.waitForSelector('h1', { timeout: 15000 });
  await page.waitForSelector('[data-widget="webPrice"]', { timeout: 8000 }).catch(() => {});

  const html = await page.content();
  console.log(html);

  await context.close();
})();

