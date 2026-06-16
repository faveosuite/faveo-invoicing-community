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

  // ── Test / spec files ────────────────────────────────────────────────────
  {
    files: [
      "**/tests/**/*.{js,mjs,cjs}",
      "**/*.spec.{js,mjs,cjs}",
    ],
    languageOptions: {
      globals: {
        ...globals.jest,
        ...globals.node,
        flushPromises: "readonly",
      },
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
