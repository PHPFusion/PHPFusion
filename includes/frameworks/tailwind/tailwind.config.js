const spacing = ['0', '1', '2', '4', '6', '8', '10'];
const breakpoints = ['sm', 'md', 'lg', 'xl', '2xl'];
const marginUtilities = ['m', 'mt', 'mb', 'ms', 'me', 'mx', 'my'];
const paddingUtilities = ['p', 'pt', 'pb', 'ps', 'pe', 'px', 'py'];
const gapUtilities = ['gap', 'gap-x', 'gap-y'];
const responsiveUtilities = [
    'hidden', 'inline', 'block', 'inline-block', 'grid', 'inline-grid', 'flex', 'inline-flex',
    'flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse', 'flex-1',
    'flex-wrap', 'flex-wrap-reverse', 'flex-nowrap', 'grow-0', 'grow', 'shrink-0', 'shrink',
    'justify-start', 'justify-center', 'justify-end', 'justify-between', 'justify-around', 'justify-evenly',
    'items-start', 'items-center', 'items-end', 'items-baseline', 'items-stretch',
    'content-start', 'content-center', 'content-end', 'content-between', 'content-around', 'content-stretch',
    'self-auto', 'self-start', 'self-center', 'self-end', 'self-baseline', 'self-stretch',
    'text-start', 'text-center', 'text-end', 'float-start', 'float-end', 'float-none',
    'object-contain', 'object-cover', 'object-fill', 'object-scale-down', 'object-none',
    'order-first', 'order-last', 'order-0', 'order-1', 'order-2', 'order-3', 'order-4', 'order-5',
];

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: {
        relative: true,
        files: [
            './tailwind_include.php',
            './tailwind_css.php',
            './tailwind.js',
            './templates/**/*.php'
        ]
    },
    safelist: [
<<<<<<< HEAD
        ...spacing.flatMap((space) =>
            [...marginUtilities, ...paddingUtilities, ...gapUtilities].map((utility) => `tw-${utility}-${space}`)
        ),
        ...spacing.flatMap((space) => [`tw-w-${space}`, `tw-h-${space}`]),
        'tw-divide-x',
        'tw-divide-y',
        'tw-divide-ui-border',
        ...marginUtilities.map((utility) => `tw-${utility}-auto`),
        ...spacing.slice(1).flatMap((space) => marginUtilities.map((utility) => `-tw-${utility}-${space}`)),
        ...responsiveUtilities.map((utility) => `tw-${utility}`),
        ...breakpoints.flatMap((breakpoint) => [
            ...spacing.flatMap((space) =>
                [...marginUtilities, ...paddingUtilities, ...gapUtilities]
                    .map((utility) => `${breakpoint}:tw-${utility}-${space}`)
            ),
            ...marginUtilities.map((utility) => `${breakpoint}:tw-${utility}-auto`),
            ...spacing.slice(1).flatMap((space) =>
                marginUtilities.map((utility) => `${breakpoint}:-tw-${utility}-${space}`)
            ),
            ...responsiveUtilities.map((utility) => `${breakpoint}:tw-${utility}`),
        ]),
        ...Array.from({length: 12}, (_, index) => `tw-col-span-${index + 1}`),
        ...breakpoints.flatMap((breakpoint) =>
            Array.from({length: 12}, (_, index) => `${breakpoint}:tw-col-span-${index + 1}`)
        )
=======
        ...[0, 1, 2, 3, 4, 5].flatMap((space) =>
            ['m', 'mt', 'mb', 'ms', 'me', 'mx', 'my', 'p', 'pt', 'pb', 'ps', 'pe', 'px', 'py', 'gap']
                .map((utility) => `tw-${utility}-${space}`)
        ),
        ...['', 'sm:', 'md:', 'lg:', 'xl:', '2xl:'].flatMap((breakpoint) => [
            `${breakpoint}tw-col-auto`,
            `${breakpoint}tw-col-start-auto`,
            ...Array.from({length: 12}, (_, index) => `${breakpoint}tw-col-span-${index + 1}`),
            ...Array.from({length: 11}, (_, index) => `${breakpoint}tw-col-start-${index + 2}`),
        ]),
>>>>>>> 8414028acb29856d694a9dd20b694a0435ef005e
    ],
    prefix: 'tw-',
    darkMode: ['class', '[data-bs-theme="dark"]'],
    theme: {
        // Bootstrap and Tabler share these responsive breakpoints.
        screens: {
            sm: '576px',
            md: '768px',
            lg: '992px',
            xl: '1200px',
            '2xl': '1400px'
        },
        extend: {
            colors: {
                ui: {
                    background: 'var(--ui-background)',
                    foreground: 'var(--ui-foreground)',
                    card: 'var(--ui-card)',
                    'card-foreground': 'var(--ui-card-foreground)',
                    muted: 'var(--ui-muted)',
                    'muted-foreground': 'var(--ui-muted-foreground)',
                    border: 'var(--ui-border)',
                    input: 'var(--ui-input)',
                    ring: 'var(--ui-ring)',
                    primary: 'var(--ui-primary)',
                    'primary-foreground': 'var(--ui-primary-foreground)',
                    secondary: 'var(--ui-secondary)',
                    accent: 'var(--ui-accent)',
                    destructive: 'var(--ui-destructive)',
                    success: 'var(--ui-success)',
                    warning: 'var(--ui-warning)',
                    info: 'var(--ui-info)'
                }
            },
            fontFamily: {
                sans: ['Geist', 'ui-sans-serif', 'system-ui', 'sans-serif']
            },
            boxShadow: {
                menu: '0 16px 44px rgba(0, 0, 0, .22)',
                dialog: '0 28px 80px rgba(0, 0, 0, .34)'
            }
        }
    },
    plugins: []
};
