const fs = require('node:fs');
const path = require('node:path');

/**
 * Validate the single version registry before it drives routes or CI checkouts.
 * @param {object} config Parsed registry.
 * @returns {object} Validated registry with stable plugin IDs and routes.
 */
function validateConfig(config) {
    if (!config.locales?.[config.defaultLocale] || !config.lines?.length) {
        throw new Error('Declare a default locale and at least one documentation line.');
    }
    if (Object.keys(config.locales).some((locale) => !/^[a-z]{2}$/.test(locale))) {
        throw new Error('Locale keys must be two lowercase letters.');
    }
    for (const [locale, directory] of Object.entries(config.localeDirectories || {})) {
        if (!config.locales[locale] || !/^[a-z]{2}$/.test(directory)) {
            throw new Error('Locale directories must map a site locale to a two-letter package directory.');
        }
    }
    for (const [locale, source] of Object.entries(config.localeSources || {})) {
        if (locale === config.defaultLocale || !config.locales[locale] ||
            !config.locales[source] || config.localeSources[source]) {
            throw new Error('Locale aliases must use a declared source locale without an alias.');
        }
    }
    if (config.lines.filter((line) => line.status === 'current').length !== 1) {
        throw new Error('Exactly one documentation line must be current.');
    }
    const versions = new Set();
    const lines = config.lines.map((line) => {
        if (!/^[1-9]\d*\.x$/.test(line.version) || versions.has(line.version)
            || line.branch !== line.version || !line.label
            || !['current', 'previous', 'legacy'].includes(line.status)) {
            throw new Error(`Invalid or duplicate documentation line: ${line.version}`);
        }
        const screenshots = line.screenshots ?? [];
        if (!Array.isArray(screenshots)) {
            throw new Error(`Invalid screenshots for ${line.version}`);
        }
        const assets = [...(line.cover ? [line.cover] : []), ...screenshots];
        if (new Set(assets).size !== assets.length || assets.some((asset) =>
            typeof asset !== 'string' || !/^assets\/images\/[a-z0-9-]+\.png$/.test(asset))) {
            throw new Error(`Invalid image path for ${line.version}`);
        }
        versions.add(line.version);
        const id = `line-${line.version.replace('.', '-')}`;
        const images = Object.fromEntries(assets.map((asset) =>
            [asset, `${id}-${asset === line.cover ? 'cover.png' : `screenshot-${path.basename(asset)}`}`]));
        return {...line, id, images,
            route: line.status === 'current' ? '' : line.version};
    });
    return {...config, lines};
}

/**
 * Load production configuration, or an explicitly supplied local test registry.
 * @param {string} [filename] Optional registry path.
 * @returns {object} Validated registry.
 */
function loadConfig(filename = process.env.DOCS_CONFIG || path.resolve(__dirname, '../docs.config.json')) {
    return validateConfig(JSON.parse(fs.readFileSync(filename, 'utf8')));
}

module.exports = {loadConfig, validateConfig};
