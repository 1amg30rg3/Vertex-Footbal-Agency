<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Head metadata for the page being rendered.
 *
 * The site is an Inertia SPA, so the body arrives empty and only fills in once
 * JavaScript runs. Crawlers that do not execute JS therefore see nothing, and
 * even Google defers rendering to a second pass. Titles, descriptions,
 * canonicals, social cards and structured data are assembled here on the
 * server instead, so every crawler gets them in the first response.
 *
 * Controllers override what they need; anything left unset falls back to the
 * site settings and the current route.
 */
class Seo
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $image = null;

    public ?string $canonical = null;

    public string $type = 'website';

    public bool $noindex = false;

    /** @var list<array<string, mixed>> */
    public array $schemas = [];

    /** @var array<string, string>|null locale code => URL */
    public ?array $alternates = null;

    /** @var array<string, string> extra og/meta properties, e.g. article times */
    public array $properties = [];

    public function title(?string $value): static
    {
        $this->title = $this->clean($value);

        return $this;
    }

    public function description(?string $value, int $limit = 160): static
    {
        $value = $this->clean($value);

        $this->description = $value === null ? null : Str::limit($value, $limit);

        return $this;
    }

    public function image(?string $value): static
    {
        $this->image = $value;

        return $this;
    }

    public function canonical(?string $value): static
    {
        $this->canonical = $value;

        return $this;
    }

    public function type(string $value): static
    {
        $this->type = $value;

        return $this;
    }

    public function noindex(bool $value = true): static
    {
        $this->noindex = $value;

        return $this;
    }

    /** @param  array<string, mixed>  $schema */
    public function schema(array $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /** @param  array<string, string>  $alternates */
    public function alternates(array $alternates): static
    {
        $this->alternates = $alternates;

        return $this;
    }

    public function property(string $name, ?string $value): static
    {
        if (filled($value)) {
            $this->properties[$name] = $value;
        }

        return $this;
    }

    public function resolvedTitle(): string
    {
        $site = Setting::publicPayload()['name'] ?? config('app.name');

        return $this->title ? $this->title.' — '.$site : $site;
    }

    public function resolvedDescription(): ?string
    {
        return $this->description ?: (Setting::publicPayload()['tagline'] ?? null);
    }

    public function resolvedImage(): ?string
    {
        $site = Setting::publicPayload();

        // A page-specific photo wins; otherwise the branded share card, which is
        // sized for social previews. The logo is the last resort - it is a wide
        // banner and gets letterboxed badly on Facebook and LinkedIn.
        $image = $this->image ?: ($site['share_image'] ?? null) ?: ($site['logo'] ?? null);

        if (blank($image)) {
            return null;
        }

        return Str::startsWith($image, ['http://', 'https://']) ? $image : url($image);
    }

    public function resolvedCanonical(): string
    {
        // Query strings split one page across many URLs; filters and paging are
        // navigation, not separate documents, so they are dropped here.
        return $this->canonical ?: url(request()->getPathInfo());
    }

    /**
     * One URL per language for hreflang, built by swapping the locale segment
     * of the current path so every translation points at its own equivalent.
     *
     * @return array<string, string>
     */
    public function resolvedAlternates(): array
    {
        if ($this->alternates !== null) {
            return $this->alternates;
        }

        $segments = array_values(array_filter(explode('/', request()->getPathInfo())));

        if ($segments === [] || ! Locales::supports($segments[0])) {
            return [];
        }

        $out = [];

        foreach (Locales::codes() as $code) {
            $segments[0] = $code;
            $out[$code] = url('/'.implode('/', $segments));
        }

        return $out;
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');

        return $value === '' ? null : $value;
    }
}
