{{--
  Welcome hero — circular illustration badge + light/extralight greeting
  + user name. Replicates the "Welcome / Zoe Chadwick" pattern from the
  designer's reference.

  Props:
    icon      blade-icons name. Default 'heroicon-o-hand-raised'. Hosts that
              ship their own illustration (e.g. resources/svg/wave.svg) can
              pass it by name.
    greeting  upper light/pink heading. Default 'Welcome'.
    name      lower bolder heading (typically the user's name).
              Default 'Design Reviewer'.
--}}
@props([
    'icon' => 'heroicon-o-hand-raised',
    'greeting' => 'Welcome',
    'name' => 'Design Reviewer',
])

{{-- Single root element — Livewire/Blade requires components to render
     one element. The <style> block lives inside the wrapper so it ships
     with the component without introducing a second root. --}}
<div {{ $attributes->class(['fi-ds-welcome-wrap']) }}>
    <style>
        .fi-ds-welcome { display: flex; align-items: center; gap: 1.5rem; padding-block: 0.5rem; }
        .fi-ds-welcome-badge {
            flex: 0 0 auto; width: 5rem; height: 5rem;
            border-radius: 9999px;
            background-color: var(--gray-900, #1a1a1f);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .fi-ds-welcome-icon { width: 3rem; height: 3rem; color: #fff; }
        .fi-ds-welcome-greeting {
            font-size: 2.5rem; font-weight: 200; line-height: 1;
            color: var(--primary-500, #d264ed);
            letter-spacing: -0.01em;
        }
        .fi-ds-welcome-name {
            font-size: 1.875rem; font-weight: 600;
            margin-top: 0.5rem; line-height: 1.1;
        }
    </style>

    <div class="fi-ds-welcome">
        <div class="fi-ds-welcome-badge">
            @svg($icon, 'fi-ds-welcome-icon')
        </div>
        <div class="fi-ds-welcome-text">
            <div class="fi-ds-welcome-greeting">{{ $greeting }}</div>
            <div class="fi-ds-welcome-name">{{ $name }}</div>
        </div>
    </div>
</div>
