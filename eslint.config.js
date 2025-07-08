import pluginVue from 'eslint-plugin-vue'
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'
import ts from 'typescript-eslint'

export default [
    {
        name: 'app/files-to-lint',
        files: ['resources/**/*.{js,ts,mts,tsx,vue}'],
    },

    {
        name: 'app/files-to-ignore',
        ignores: ['**/dist/**', '**/dist-ssr/**', '**/coverage/**', "*.d.ts"],
    },
    ...ts.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/component-api-style': ['error',
                ['script-setup', 'composition']
            ],
            'vue/define-emits-declaration': ['error', 'type-based'],
            'vue/define-props-declaration': ['error', 'type-based'],
            'vue/block-lang': ['error',
                { script: { lang: 'ts' } }
            ],
            'vue/no-v-html': 'warn',
        },
        files: ['*.vue', '**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: '@typescript-eslint/parser'
            }
        }
    },
    skipFormatting,
]
