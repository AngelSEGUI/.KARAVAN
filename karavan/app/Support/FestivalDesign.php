<?php

namespace App\Support;

/**
 * Apparence d'une page de festival : palette de couleurs et polices choisies par l'organisateur.
 * Stockée en JSON dans festivals.design, par exemple :
 *   {"palette": "custom", "primary": "#ff5a36", "secondary": "#22d3ff", "mode": "dark",
 *    "heading_font": "Unbounded", "body_font": "Inter"}
 *
 * cssVariables() produit les variables qui surchargent le thème du site sur la page du festival.
 */
class FestivalDesign
{
    public string $palette;
    public string $primary;
    public string $secondary;
    public string $mode;
    public string $headingFont;
    public string $bodyFont;

    public function __construct(?array $design)
    {
        $config = config('festival_design');
        $design ??= [];

        $this->palette = isset($config['palettes'][$design['palette'] ?? '']) || ($design['palette'] ?? '') === 'custom'
            ? $design['palette']
            : $config['defaults']['palette'];

        $preset = $config['palettes'][$this->palette] ?? null;
        $this->primary = $preset['primary'] ?? self::color($design['primary'] ?? null, '#ff5a36');
        $this->secondary = $preset['secondary'] ?? self::color($design['secondary'] ?? null, '#ff3e88');
        $this->mode = $preset['mode'] ?? (($design['mode'] ?? '') === 'dark' ? 'dark' : 'light');

        $this->headingFont = array_key_exists($design['heading_font'] ?? '', $config['fonts']['heading'])
            ? $design['heading_font'] : $config['defaults']['heading_font'];
        $this->bodyFont = array_key_exists($design['body_font'] ?? '', $config['fonts']['body'])
            ? $design['body_font'] : $config['defaults']['body_font'];
    }

    /** Valeur à enregistrer en base. */
    public function toArray(): array
    {
        return array_filter([
            'palette' => $this->palette,
            'primary' => $this->palette === 'custom' ? $this->primary : null,
            'secondary' => $this->palette === 'custom' ? $this->secondary : null,
            'mode' => $this->palette === 'custom' ? $this->mode : null,
            'heading_font' => $this->headingFont,
            'body_font' => $this->bodyFont,
        ]);
    }

    /** Lien Google Fonts pour charger les deux polices. */
    public function fontsUrl(): string
    {
        $families = collect([$this->headingFont, $this->bodyFont])->unique()
            ->map(fn ($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@400;600;700;800')
            ->implode('&');

        return "https://fonts.googleapis.com/css2?{$families}&display=swap";
    }

    /**
     * Variables CSS appliquées au conteneur .festival-page.
     * Elles remplacent celles du thème du site : boutons, onglets, cartes… prennent les couleurs du festival.
     */
    public function cssVariables(): string
    {
        $m = config('festival_design.modes.' . $this->mode);
        $fallbackH = config('festival_design.fonts.heading.' . $this->headingFont, 'sans-serif');
        $fallbackB = config('festival_design.fonts.body.' . $this->bodyFont, 'sans-serif');

        $vars = [
            '--fest-primary' => $this->primary,
            '--fest-secondary' => $this->secondary,
            '--fest-on-primary' => self::readableOn($this->primary),
            '--color-primary' => $this->primary,
            '--color-primary-hover' => self::shade($this->primary, $this->mode === 'dark' ? 0.15 : -0.12),
            '--color-on-primary' => self::readableOn($this->primary),
            '--color-primary-soft' => self::alpha($this->primary, $this->mode === 'dark' ? 0.18 : 0.12),
            '--color-accent' => $this->secondary,
            '--color-highlight' => $this->secondary,
            '--color-on-highlight' => self::readableOn($this->secondary),
            '--color-bg' => $m['bg'],
            '--color-bg-alt' => $this->mode === 'dark' ? '#15122a' : '#f2eee6',
            '--color-surface' => $m['surface'],
            '--color-input' => $m['surface'],
            '--color-text' => $m['text'],
            '--color-text-soft' => $m['text'],
            '--color-muted' => $m['muted'],
            '--color-line' => $m['line'],
            '--color-line-strong' => $m['muted'],
            '--shadow-button' => '0 6px 16px -6px ' . self::alpha($this->primary, 0.6),
            '--color-inverse' => $this->mode === 'dark' ? $m['text'] : '#17132b',
            '--color-on-inverse' => $this->mode === 'dark' ? $m['bg'] : '#ffffff',
            '--color-inverse-accent' => $this->mode === 'dark' ? self::shade($this->primary, -0.25) : $this->secondary,
            '--gradient-brand' => "linear-gradient(120deg, {$this->primary}, {$this->secondary})",
            '--font-display' => "\"{$this->headingFont}\", {$fallbackH}",
            '--font-body' => "\"{$this->bodyFont}\", {$fallbackB}",
        ];

        return collect($vars)->map(fn ($v, $k) => "{$k}: {$v}")->implode('; ');
    }

    /* ---------- Outils couleur ---------- */

    public static function color(?string $value, string $default): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $default;
    }

    /** Noir ou blanc, selon ce qui se lit le mieux sur la couleur donnée (luminance WCAG). */
    public static function readableOn(string $hex): string
    {
        [$r, $g, $b] = array_map(function ($c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, sscanf($hex, '#%02x%02x%02x'));

        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return $luminance > 0.36 ? '#17132b' : '#ffffff';
    }

    /** Éclaircit (amount > 0) ou assombrit (amount < 0) une couleur. */
    public static function shade(string $hex, float $amount): string
    {
        $rgb = sscanf($hex, '#%02x%02x%02x');
        $rgb = array_map(fn ($c) => (int) round($amount >= 0 ? $c + (255 - $c) * $amount : $c * (1 + $amount)), $rgb);

        return vsprintf('#%02x%02x%02x', $rgb);
    }

    public static function alpha(string $hex, float $opacity): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }
}
