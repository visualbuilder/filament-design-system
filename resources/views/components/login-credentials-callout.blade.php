<style>
    .fi-ds-credentials-callout {
        background-color: rgb(var(--primary-50));
        box-shadow: inset 0 0 0 1px rgb(var(--primary-200));
    }
    .fi-ds-credentials-callout-title { color: rgb(var(--primary-900)); }
    .fi-ds-credentials-callout-body { color: rgb(var(--primary-800)); }
    .fi-ds-credentials-callout-footnote { color: rgb(var(--primary-700)); }

    .dark .fi-ds-credentials-callout {
        background-color: rgb(var(--primary-950) / 0.4);
        box-shadow: inset 0 0 0 1px rgb(var(--primary-900));
    }
    .dark .fi-ds-credentials-callout-title { color: rgb(var(--primary-100)); }
    .dark .fi-ds-credentials-callout-body { color: rgb(var(--primary-200)); }
    .dark .fi-ds-credentials-callout-footnote { color: rgb(var(--primary-300)); }
</style>

<div class="fi-ds-credentials-callout mb-6 rounded-md p-4 text-sm">
    <p class="fi-ds-credentials-callout-title font-medium mb-1">Demo credentials (pre-filled)</p>
    <p class="fi-ds-credentials-callout-body">
        Email: <code class="font-mono">{{ config('design-system.demo_user.email') }}</code>
    </p>
    <p class="fi-ds-credentials-callout-body">
        Password: <code class="font-mono">{{ config('design-system.demo_user.password') }}</code>
    </p>
    <p class="fi-ds-credentials-callout-footnote mt-2 text-xs">
        No real data is shown anywhere in this panel.
    </p>
</div>
