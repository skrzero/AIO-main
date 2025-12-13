le projet ce lance  avec "npm run watch" et le live server


# Afec Starter Kit

**Starter Kit Afec**, un point de départ simple pour tout projet front-end utilisant Webpack, Sass et JavaScript.

Ce projet est conçu pour apprendre à organiser son code, automatiser les tâches courantes (compilation, linting…) et comprendre le fonctionnement de la chaîne de production moderne côté front-end.

---

## Installation

Avant de commencer, assure-toi d’avoir Node.js installé sur ta machine.

1. Clone le dépôt :
   ```bash
   git clone https://github.com/nextader/afecstarter.git
   cd afec-starter-kit
   ```

2. Installe les dépendances :
   ```bash
   npm install
   ```

---

## Commandes disponibles

| Commande              | Description |
|-----------------------|-------------|
| `npm run watch`       | Lance Webpack en mode développement et surveille les fichiers. |
| `npm run build`       | Compile le projet en mode production (fichiers minifiés dans `dist/`). |
| `npm run scss-lint`   | Analyse les fichiers SCSS avec Stylelint. |
| `npm run scss-fix`    | Corrige automatiquement les erreurs SCSS quand c’est possible. |

---

## Configuration Webpack

Le fichier principal de configuration est `webpack.config.cjs`.

### Entrée

Le point d’entrée est défini ainsi :

```js
entry: {
  theme: ['./assets/scripts/base.js', './assets/styles/base.scss']
}
```

Cela signifie que Webpack construit à partir de ces deux fichiers (JS et SCSS).

### Sortie

Les fichiers générés sont déposés dans :

- `dist/js/` pour le JavaScript
- `dist/css/` pour le CSS

### Traitement du code

#### JavaScript

- Transpilation via Babel (`@babel/preset-env`)
- Compatible avec les navigateurs récents
- Exclusion de `node_modules`

#### SCSS / CSS

- Traitement complet de `.scss` et `.css`
- Génération de source maps en mode développement
- Stack utilisée : `sass-loader` → `postcss-loader` → `css-loader` → `MiniCssExtractPlugin`

#### Assets

- Fichiers comme `.png`, `.svg`, `.woff`, `.jpg`, etc. sont traités automatiquement
- Ils sont copiés dans `dist/css/` avec un nom unique (hashé)
- Tu peux les utiliser dans ton SCSS avec des chemins relatifs

---

## Utilisation des images dans les fichiers SCSS

Les images que tu veux appeler depuis ton SCSS doivent être placées dans un dossier comme :

```
assets/images/
```

Puis, dans ton fichier SCSS (`base.scss` par exemple), tu peux les appeler ainsi :

```scss
background-image: url('../images/mon-image.jpg');
```

Webpack va :

- détecter l’image appelée
- la copier dans le dossier `dist/css/` avec un nom hashé
- mettre à jour automatiquement l’URL dans le CSS final

Veille à :

- toujours utiliser un chemin relatif correct (depuis ton fichier `.scss`)
- ne jamais stocker d’images directement dans `dist/` (ce dossier est réinitialisé à chaque compilation)

---

## Linting

- ESLint est configuré via `eslint.config.js` (format flat config, ESLint v9)
- Il n’y a plus de fichier `.eslintignore` : les fichiers à ignorer sont définis dans la config
- Stylelint est utilisé pour les fichiers SCSS via les scripts disponibles

---

## Mode production

Quand tu exécutes :

```bash
npm run build
```

Voici ce qui se passe :

- Le projet passe en `mode: production`
- Le JS est minifié avec `terser-webpack-plugin` (les `console.log` sont supprimés)
- Le CSS est optimisé avec `csso-webpack-plugin`
- Un fichier `thirdPartyNotice.json` est généré avec les licences des dépendances

---

## Structure du projet

```
├── assets/
│   ├── scripts/
│   │   └── base.js
│   ├── styles/
│   │   └── base.scss
│   └── images/
│       └── mon-image.jpg
├── dist/
│   ├── js/
│   └── css/
├── index.html
├── package.json
├── webpack.config.cjs
├── eslint.config.js
└── .gitignore
```

---

## Conseils

- Utilise `npm run watch` pendant que tu développes : les fichiers seront automatiquement recompilés à chaque modification.
- Ne modifie jamais les fichiers dans `dist/` : ils sont régénérés automatiquement.
- Si tu ajoutes d'autres fichiers dans `assets/scripts/` ou `assets/styles/`, pense à les importer dans `base.js` ou `base.scss`.
- Pour les images appelées en SCSS, place-les dans `assets/images/` et utilise des chemins relatifs.