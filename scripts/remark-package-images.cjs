/**
 * Resolve package-relative screenshots through the site's generated static directory.
 * Markdown readers in the package checkout still see the original asset paths.
 * @param {object} line Documentation line with registered image paths.
 * @returns {function} Remark transformer.
 */
function remarkPackageImages(line) {
    const urls = new Map(Object.entries(line.images).map(([asset, filename]) =>
        [`../${asset}`, `/img/${filename}`]));
    return (tree) => {
        for (const block of tree.children) {
            if (block.type !== 'paragraph') continue;
            for (const node of block.children) {
                if (node.type === 'image' && urls.has(node.url)) {
                    node.url = urls.get(node.url);
                }
            }
        }
    };
}

module.exports = remarkPackageImages;
