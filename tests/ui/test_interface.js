const puppeteer = require('puppeteer');
const lighthouse = require('lighthouse');
const { expect } = require('chai');

describe('Tests Interface Utilisateur', () => {
    let browser;
    let page;
    const BASE_URL = 'http://localhost:8000';
    
    const viewports = [
        { width: 375, height: 667, name: 'mobile' },    // iPhone SE
        { width: 768, height: 1024, name: 'tablet' },   // iPad
        { width: 1366, height: 768, name: 'laptop' },   // Laptop
        { width: 1920, height: 1080, name: 'desktop' }  // Desktop
    ];

    before(async () => {
        browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    });

    after(async () => {
        await browser.close();
    });

    beforeEach(async () => {
        page = await browser.newPage();
        // Active la couverture CSS
        await page.coverage.startCSSCoverage();
        // Active la couverture JavaScript
        await page.coverage.startJSCoverage();
    });

    afterEach(async () => {
        // Récupère les données de couverture
        const cssCoverage = await page.coverage.stopCSSCoverage();
        const jsCoverage = await page.coverage.stopJSCoverage();
        
        // Analyse la couverture CSS
        let unusedCSS = 0;
        let totalCSS = 0;
        for (const entry of cssCoverage) {
            totalCSS += entry.text.length;
            for (const range of entry.ranges) {
                unusedCSS += range.end - range.start;
            }
        }
        
        console.log(`CSS inutilisé: ${((unusedCSS / totalCSS) * 100).toFixed(2)}%`);
        
        await page.close();
    });

    describe('Tests Responsive Design', () => {
        const pagesToTest = [
            '/',                            // Dashboard
            '/finance/transactions',        // Liste des transactions
            '/finance/transactions/create', // Création transaction
            '/finance/report',             // Rapport financier
            '/members',                    // Liste des membres
            '/members/create'              // Création membre
        ];

        for (const viewport of viewports) {
            describe(`Viewport ${viewport.name} (${viewport.width}x${viewport.height})`, () => {
                for (const path of pagesToTest) {
                    it(`Page ${path} devrait être responsive`, async () => {
                        await page.setViewport(viewport);
                        await page.goto(`${BASE_URL}${path}`);
                        
                        // Vérifie les éléments qui débordent horizontalement
                        const overflowElements = await page.evaluate(() => {
                            const elements = document.querySelectorAll('*');
                            const overflowing = [];
                            for (const element of elements) {
                                if (element.offsetWidth > window.innerWidth) {
                                    overflowing.push(element.tagName);
                                }
                            }
                            return overflowing;
                        });
                        
                        expect(overflowElements).to.be.empty;
                        
                        // Vérifie que le menu est adaptatif
                        const menuVisible = await page.evaluate(() => {
                            const menu = document.querySelector('.sidebar');
                            const style = window.getComputedStyle(menu);
                            return style.display !== 'none';
                        });
                        
                        if (viewport.width < 768) {
                            expect(menuVisible).to.be.false;
                        } else {
                            expect(menuVisible).to.be.true;
                        }
                    });
                }
            });
        }
    });

    describe('Tests de Compatibilité Navigateurs', () => {
        it('Devrait utiliser des propriétés CSS compatibles', async () => {
            const cssCompatibility = await page.evaluate(() => {
                const styles = window.getComputedStyle(document.documentElement);
                return {
                    hasFlexbox: styles.display.includes('flex'),
                    hasGrid: styles.display.includes('grid'),
                    hasTransform: styles.transform !== 'none',
                    hasTransition: styles.transition !== 'none'
                };
            });
            
            expect(cssCompatibility.hasFlexbox).to.be.true;
            expect(cssCompatibility.hasGrid).to.be.true;
        });

        it('Devrait charger les polyfills nécessaires', async () => {
            const polyfills = await page.evaluate(() => {
                return {
                    hasPromise: typeof Promise !== 'undefined',
                    hasFetch: typeof fetch !== 'undefined',
                    hasCustomElements: typeof customElements !== 'undefined'
                };
            });
            
            expect(polyfills.hasPromise).to.be.true;
            expect(polyfills.hasFetch).to.be.true;
        });
    });

    describe('Tests de Performance', () => {
        for (const path of pagesToTest) {
            it(`${path} devrait charger en moins de 2 secondes`, async () => {
                const start = Date.now();
                
                // Navigation avec timeouts
                await page.goto(`${BASE_URL}${path}`, {
                    waitUntil: ['load', 'networkidle0'],
                    timeout: 2000
                });
                
                const loadTime = Date.now() - start;
                expect(loadTime).to.be.below(2000);
                
                // Vérifie le First Contentful Paint
                const fcp = await page.evaluate(() => {
                    const entry = performance.getEntriesByType('paint')
                        .find(entry => entry.name === 'first-contentful-paint');
                    return entry ? entry.startTime : null;
                });
                
                expect(fcp).to.be.below(1000);
            });
            
            it(`${path} devrait avoir un bon score Lighthouse`, async () => {
                const {lhr} = await lighthouse(page.url(), {
                    port: (new URL(browser.wsEndpoint())).port,
                    output: 'json',
                    onlyCategories: ['performance']
                });
                
                expect(lhr.categories.performance.score).to.be.above(0.8);
            });
        }
    });

    describe('Tests d\'Accessibilité', () => {
        it('Devrait avoir des attributs ARIA appropriés', async () => {
            const ariaAttributes = await page.evaluate(() => {
                const elements = document.querySelectorAll('[role], [aria-label], [aria-describedby]');
                return Array.from(elements).map(el => ({
                    tag: el.tagName,
                    role: el.getAttribute('role'),
                    ariaLabel: el.getAttribute('aria-label')
                }));
            });
            
            expect(ariaAttributes).to.not.be.empty;
        });

        it('Devrait avoir un contraste suffisant', async () => {
            const contrastIssues = await page.evaluate(() => {
                const elements = document.querySelectorAll('*');
                const issues = [];
                
                for (const element of elements) {
                    const style = window.getComputedStyle(element);
                    const backgroundColor = style.backgroundColor;
                    const color = style.color;
                    
                    // Vérifie le contraste (implémentation simplifiée)
                    if (backgroundColor === 'transparent' || color === 'transparent') {
                        continue;
                    }
                    
                    issues.push({
                        element: element.tagName,
                        backgroundColor,
                        color
                    });
                }
                
                return issues;
            });
            
            expect(contrastIssues).to.be.empty;
        });
    });
});
