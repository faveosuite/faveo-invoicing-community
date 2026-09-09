import js from "@eslint/js";
import globals from "globals";
import pluginVue from "eslint-plugin-vue";
import vueParser from "vue-eslint-parser";
import { defineConfig } from "eslint/config";

export default defineConfig([
  // ── Base JS rules ────────────────────────────────────────────────────────
  {
    files: ["**/*.{js,mjs,cjs,vue}"],
    plugins: { js },
    extends: ["js/recommended"],
    rules: {
      "no-console": "off",
      "no-unused-vars": ["error", {
        varsIgnorePattern: "^_",
        argsIgnorePattern: "^_",
        caughtErrors: "none",
      }],
    },
  },

  // ── Vue plugin (essential rule set) ──────────────────────────────────────
  pluginVue.configs["flat/essential"],

  // ── Vue files — explicit parser ───────────────────────────────────────────
  {
    files: ["**/*.vue"],
    languageOptions: { parser: vueParser },
  },

  // ── Source files ─────────────────────────────────────────────────────────
  {
    files: [
      "resources/assets/js/**/*.{js,vue}",
      "app/**/*.{js,vue}",
    ],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.es2021,
        __: "readonly",
        translator: "readonly",
      },
    },
    rules: {
      // Component naming — many single-word names are intentional
      "vue/multi-word-component-names": "off",

      // Real bug-catchers
      "vue/no-unused-vars": "warn",
      "vue/require-v-for-key": "error",
      "vue/no-use-v-if-with-v-for": "error",
      "vue/no-deprecated-v-on-native-modifier": "error",

      // Formatting — leave to Prettier / team preference
      "vue/html-self-closing": "off",
      "vue/max-attributes-per-line": "off",
      "vue/singleline-html-element-content-newline": "off",
      "vue/multiline-html-element-content-newline": "off",
      "vue/html-indent": "off",
    },
  },

  // ── Node-context config files ────────────────────────────────────────────
  {
    files: [
      "*.config.js",
      "*.config.cjs",
      "*.config.mjs",
    ],
    languageOptions: {
      globals: {
        ...globals.node,
      },
    },
  },

  // ── Test / spec files ────────────────────────────────────────────────────
  {
    files: [
      "**/tests/**/*.{js,mjs,cjs}",
      "**/*.spec.{js,mjs,cjs}",
      "jest.setup.js",
    ],
    languageOptions: {
      globals: {
        ...globals.jest,
        ...globals.node,
        ...globals.browser,   // jest-environment-jsdom provides window/document
        // jest.setup.js injects these onto global — tell ESLint they exist
        flushPromises: "readonly",
        mockHttp:      "readonly",
      },
    },
    rules: {
      // Prefer const when a variable is never reassigned
      "prefer-const": ["error", { destructuring: "all" }],

      // No var — use let / const
      "no-var": "error",

      // Every imported name must be used (same as source files)
      "no-unused-vars": ["error", {
        varsIgnorePattern: "^_",
        argsIgnorePattern: "^_",
        caughtErrors: "none",
      }],

      // Avoid leaving stray console.log in test files
      "no-console": "warn",
    },
  },

  // ── Ignores ───────────────────────────────────────────────────────────────
  {
    ignores: [
      "public/**",
      "vendor/**",
      "node_modules/**",
      "storage/**",
    ],
  },
]);
