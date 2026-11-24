const defaultColors = {
    primary: '#ff5b22',
    accent: '#7558e8',
    neutral: '#64748b',
};

const densities = {
    compact: { section: '1.75rem', control: '2.5rem', header: '3rem', font: '0.9375rem' },
    comfortable: { section: '2.25rem', control: '2.75rem', header: '3.5rem', font: '1rem' },
    spacious: { section: '3rem', control: '3rem', header: '4rem', font: '1.0625rem' },
};

const rgb = (hex) => {
    const value = typeof hex === 'string' && /^#[0-9a-f]{6}$/i.test(hex) ? hex : defaultColors.neutral;
    return [1, 3, 5].map((offset) => Number.parseInt(value.slice(offset, offset + 2), 16));
};

const toHex = (...channels) => `#${channels.map((channel) => Math.max(0, Math.min(255, channel)).toString(16).padStart(2, '0')).join('')}`;

const luminance = (channels) => {
    const [red, green, blue] = channels.map((channel) => {
        const value = channel / 255;
        return value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
};

const palette = (hex, neutral = false) => {
    let [red, green, blue] = rgb(hex);

    if (!neutral) {
        let attempts = 0;
        while (1.05 / (luminance([red, green, blue]) + 0.05) < 3 && attempts < 40) {
            red = Math.floor(red * 0.95);
            green = Math.floor(green * 0.95);
            blue = Math.floor(blue * 0.95);
            attempts += 1;
        }
    }

    const mixes = neutral
        ? { 50: [255, 255, 255, 0.95], 100: [255, 255, 255, 0.88], 200: [255, 255, 255, 0.75], 300: [255, 255, 255, 0.6], 400: [255, 255, 255, 0.42], 600: [0, 0, 0, 0.12], 700: [0, 0, 0, 0.25], 800: [0, 0, 0, 0.4], 900: [0, 0, 0, 0.58], 950: [0, 0, 0, 0.72] }
        : { 50: [255, 255, 255, 0.92], 100: [255, 255, 255, 0.84], 200: [255, 255, 255, 0.7], 300: [255, 255, 255, 0.54], 400: [255, 255, 255, 0.3], 600: [0, 0, 0, 0.12], 700: [0, 0, 0, 0.26], 800: [0, 0, 0, 0.4], 900: [0, 0, 0, 0.55], 950: [0, 0, 0, 0.65] };
    const colors = { 500: toHex(red, green, blue) };

    Object.entries(mixes).forEach(([shade, [targetRed, targetGreen, targetBlue, amount]]) => {
        colors[shade] = toHex(
            Math.round(red * (1 - amount) + targetRed * amount),
            Math.round(green * (1 - amount) + targetGreen * amount),
            Math.round(blue * (1 - amount) + targetBlue * amount),
        );
    });

    return colors;
};

const buildTokens = (state) => {
    const brand = palette(state.primaryColor);
    const accent = palette(state.accentColor);
    const neutral = palette(state.neutralColor, true);
    const density = densities[state.density] || densities.comfortable;
    const radius = Math.max(0, Math.min(24, Number(state.borderRadius) || 0));
    const width = Math.max(1120, Math.min(1536, Number(state.containerWidth) || 1280));
    const font = state.fonts[state.fontFamily] || Object.values(state.fonts)[0];
    const darkFooter = state.footerStyle === 'dark';
    const cardShadow = state.cardStyle === 'elevated'
        ? '0 18px 45px -20px rgb(15 23 42 / 0.28)'
        : state.cardStyle === 'minimal' ? 'none' : '0 1px 3px 0 rgb(15 23 42 / 0.06)';
    const cardBorder = state.cardStyle === 'elevated' ? '65%' : state.cardStyle === 'minimal' ? '100%' : '80%';
    const tokens = {
        '--font-sans': font,
        '--font-size-base': density.font,
        '--space-section': density.section,
        '--control-height': density.control,
        '--header-height': density.header,
        '--container-width': `${width}px`,
        '--radius-sm': `${Math.max(0, radius - 4)}px`,
        '--radius-md': `${radius}px`,
        '--radius-lg': `${radius + 2}px`,
        '--radius-xl': `${radius + 4}px`,
        '--radius-2xl': `${radius + 8}px`,
        '--radius-control': `${radius}px`,
        '--radius-card': `${radius + 8}px`,
        '--card-shadow': cardShadow,
        '--card-border-strength': cardBorder,
        '--surface': neutral[50],
        '--surface-strong': '#ffffff',
        '--text': neutral[900],
        '--text-muted': neutral[500],
        '--border': neutral[200],
        '--header-bg': 'rgb(255 255 255 / 0.96)',
        '--header-border': neutral[100],
        '--footer-bg': darkFooter ? neutral[900] : '#ffffff',
        '--footer-text': darkFooter ? neutral[100] : neutral[500],
        '--footer-heading': darkFooter ? '#ffffff' : neutral[900],
        '--footer-border': darkFooter ? neutral[800] : neutral[100],
    };

    Object.entries({ brand, accent, neutral }).forEach(([name, colors]) => {
        Object.entries(colors).forEach(([shade, color]) => {
            tokens[`--color-${name}-${shade}`] = color;
        });
    });

    tokens['--color-ink-50'] = neutral[50];
    tokens['--color-ink-100'] = neutral[100];
    tokens['--color-ink-200'] = neutral[200];
    tokens['--color-ink-300'] = neutral[300];
    tokens['--color-ink-400'] = neutral[400];
    tokens['--color-ink-500'] = neutral[500];
    tokens['--color-ink-600'] = neutral[600];
    tokens['--color-ink-700'] = neutral[700];
    tokens['--color-ink-800'] = neutral[800];
    tokens['--color-ink-900'] = neutral[900];
    tokens['--color-slate-50'] = neutral[50];
    tokens['--color-slate-100'] = neutral[100];
    tokens['--color-slate-200'] = neutral[200];
    tokens['--color-slate-300'] = neutral[300];
    tokens['--color-slate-400'] = neutral[400];
    tokens['--color-slate-500'] = neutral[500];
    tokens['--color-slate-600'] = neutral[600];
    tokens['--color-slate-700'] = neutral[700];
    tokens['--color-slate-800'] = neutral[800];
    tokens['--color-slate-900'] = neutral[900];
    tokens['--color-slate-950'] = neutral[950];

    return tokens;
};

export function appearancePreview(config) {
    return {
        preset: config.appearance_preset,
        primaryColor: config.primary_color,
        accentColor: config.accent_color,
        neutralColor: config.neutral_color,
        fontFamily: config.font_family,
        borderRadius: Number(config.border_radius),
        containerWidth: Number(config.container_width),
        density: config.density,
        headerStyle: config.header_style,
        footerStyle: config.footer_style,
        cardStyle: config.card_style,
        presets: config.presets,
        fonts: config.fonts,

        get previewTokens() {
            return buildTokens(this);
        },

        get previewStyle() {
            return Object.entries(this.previewTokens).map(([token, value]) => `${token}:${value}`).join(';');
        },

        get previewFont() {
            return this.fonts[this.fontFamily];
        },

        get primaryPalette() {
            return [50, 100, 200, 400, 500, 700].map((shade) => this.previewTokens[`--color-brand-${shade}`]);
        },

        get accentPalette() {
            return [50, 100, 200, 400, 500, 700].map((shade) => this.previewTokens[`--color-accent-${shade}`]);
        },

        applyPreset() {
            const values = this.presets[this.preset]?.values;
            if (!values || Object.keys(values).length === 0) {
                return;
            }

            this.primaryColor = values.primary_color;
            this.accentColor = values.accent_color;
            this.neutralColor = values.neutral_color;
            this.fontFamily = values.font_family;
            this.borderRadius = Number(values.border_radius);
            this.containerWidth = Number(values.container_width);
            this.density = values.density;
            this.headerStyle = values.header_style;
            this.footerStyle = values.footer_style;
            this.cardStyle = values.card_style;
        },
    };
}
