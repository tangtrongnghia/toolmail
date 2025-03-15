/* eslint-env node */
require('@rushstack/eslint-patch/modern-module-resolution')

module.exports = {
    root: true,
    extends: ['plugin:vue/vue3-essential', 'eslint:recommended', '@vue/eslint-config-prettier'],
    parserOptions: {
        ecmaVersion: 'latest',
    },
    rules: {
        'vue/multi-word-component-names': 'off',
        'no-undef': 'off',
        'vue/attributes-order': [
            'error',
            {
                order: [
                    'DEFINITION', // is, v-is
                    'LIST_RENDERING', // v-for
                    'CONDITIONALS', // v-if, v-else-if, v-else, v-show, v-cloak
                    'RENDER_MODIFIERS', // v-once, v-pre
                    'GLOBAL', // id
                    'UNIQUE', // ref, key
                    'TWO_WAY_BINDING', // v-model
                    'OTHER_DIRECTIVES', // v-custom-directive
                    'OTHER_ATTR', // custom attributes
                    'EVENTS', // @click, v-on
                    'CONTENT', // v-html, v-text
                ],
                alphabetical: true,
            },
        ],

        'vue/html-self-closing': [
            'error',
            {
                html: {
                    void: 'always',
                    normal: 'always',
                    component: 'always',
                },
                svg: 'always',
                math: 'always',
            },
        ],

        'vue/component-name-in-template-casing': [
            'error',
            'PascalCase',
            {
                registeredComponentsOnly: false,
                ignores: ['/^img-/', 'fa', 'big'],
            },
        ],

        'vue/attribute-hyphenation': ['error', 'never'],
        'vue/no-v-text-v-html-on-component': 'off',
    },
}
