{{--
  Callout — horizontal pill card with leading icon badge, eyebrow label,
  title, description, and optional dismiss button. Replicates the
  "REMINDER / Your Upcoming Appointment" pattern from the designer's
  reference.

  Single root element (Livewire requires this). Self-contained styles
  inside the wrapper so embedding the component anywhere just works.

  Props:
    icon         blade-icons name. Default 'heroicon-o-bell'.
    label        small uppercase eyebrow text. Default 'REMINDER'.
    title        main heading inside the card.
    description  optional subtext below the title.
    dismissible  show a close (×) button in the top-right. Alpine-driven —
                 no JS callback wiring needed at the consumer.
--}}
@props([
    'icon' => 'heroicon-o-bell',
    'label' => 'REMINDER',
    'title' => '',
    'description' => null,
    'dismissible' => false,
])

<div
    {{ $attributes->class(['fi-ds-callout-wrap']) }}
    @if ($dismissible)
        x-data="{ open: true }"
        x-show="open"
        x-transition.opacity
    @endif
>
    <style>
        .fi-ds-callout {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 0.875rem 1.25rem 0.875rem 0.875rem;
            border-radius: 9999px;
            background-color: var(--pink26-menu-bg, var(--gray-900, #1a1a1f));
            box-shadow: var(--pink26-menu-shadow, 0 4px 24px rgb(0 0 0 / 0.25));
            position: relative;
        }

        .fi-ds-callout-badge {
            flex: 0 0 auto;
            width: 4rem; height: 4rem;
            border-radius: 9999px;
            background-color: var(--gray-950, #0a0a0d);
            display: flex; align-items: center; justify-content: center;
        }

        .fi-ds-callout-icon { width: 2.25rem; height: 2.25rem; color: #fff; }

        .fi-ds-callout-body { flex: 1; min-width: 0; }

        .fi-ds-callout-label {
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--primary-500, #d264ed);
            margin-bottom: 0.125rem;
        }

        .fi-ds-callout-title {
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .fi-ds-callout-description {
            font-size: 0.8125rem;
            opacity: 0.7;
            margin-top: 0.125rem;
        }

        .fi-ds-callout-dismiss {
            flex: 0 0 auto;
            width: 1.75rem; height: 1.75rem;
            border-radius: 9999px;
            background-color: var(--primary-500, #d264ed);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border: none;
            position: absolute;
            top: 0.5rem;
            inset-inline-end: 0.5rem;
        }

        .fi-ds-callout-dismiss:hover {
            background-color: var(--primary-600, #ae18d2);
        }

        .fi-ds-callout-dismiss svg { width: 1rem; height: 1rem; }
    </style>

    <div class="fi-ds-callout">
        <div class="fi-ds-callout-badge">
            @svg($icon, 'fi-ds-callout-icon')
        </div>

        <div class="fi-ds-callout-body">
            @if ($label)
                <div class="fi-ds-callout-label">{{ $label }}</div>
            @endif
            <div class="fi-ds-callout-title">{{ $title }}</div>
            @if ($description)
                <div class="fi-ds-callout-description">{{ $description }}</div>
            @endif
        </div>

        @if ($dismissible)
            <button
                type="button"
                class="fi-ds-callout-dismiss"
                aria-label="Dismiss"
                @click="open = false"
            >
                @svg('heroicon-m-x-mark')
            </button>
        @endif
    </div>
</div>
