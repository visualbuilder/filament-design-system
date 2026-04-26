@props(['label', 'hex'])

<div class="ds-swatch">
    <div class="ds-swatch__tile" style="background-color: {{ $hex }};"></div>
    <div class="ds-swatch__meta">
        <code class="ds-swatch__label">{{ $label }}</code>
        <code class="ds-swatch__hex">{{ $hex }}</code>
    </div>
</div>
