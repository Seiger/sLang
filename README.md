# sLang documentation site

The Docusaurus site reads the same Markdown that dDocs reads from the package branches.

- `2.x` is current at `/sLang/` with English, Ukrainian, German, French and Polish guides.
- `1.x` is archived at `/sLang/1.x/`. It uses the original English 1.x guides. The other site languages display the English archive where a translation is unavailable.
- `docs.config.json` defines the published branches, locales and screenshots. Page text stays in `docs/<locale>/` on each package branch.

The [sSettings](https://github.com/Seiger/sSettings/tree/docs) and [sCommerce](https://github.com/Seiger/sCommerce/tree/docs) documentation sites use the same structure.

## Local preview

Use Node.js 22 or newer. Run the following commands from the site checkout. Use the paths to your local package checkouts.

```sh
npm ci
npm test
npm run docs:prepare -- "2.x=/path/to/sLang-2x" "1.x=/path/to/sLang-1x"
npm run build
npm run serve
```

Open [the local preview](http://127.0.0.1:3000/sLang/). Docusaurus generates its build inputs in `.generated/`. Keep new screenshots in the package's `docs/assets/images/` add their paths to docs.config.json and reference them in the localized overviews. Consecutive image paragraphs form a gallery on the site and render as ordinary Markdown images in dDocs.

## GitHub Pages

Set the repository's Pages source to **GitHub Actions**. Publish the source and site commits on `1.x`, `2.x` and `docs` together, for example with a single atomic push. The push to `docs` starts the first build. You can also run the workflow manually after all three refs exist on GitHub.

The package branches call the reusable workflow from `docs` when documentation changes. The workflow reads the registry, checks out the canonical files from each package branch, runs the site tests and deploys a single Pages artifact. If the `github-pages` environment restricts which branches can publish, allow `docs` and the package branches that call the workflow.
