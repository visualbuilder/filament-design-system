<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Visualbuilder\FilamentDesignSystem\Models\DesignSystemUser;

/**
 * Bridge route used only by the screenshot_catalogue MCP tool.
 *
 * The MCP tool generates a signed URL pointing at this controller. The signed
 * middleware verifies the URL is fresh (60s TTL by default) and was signed by
 * this app's APP_KEY — i.e. originates from our MCP server. The controller
 * then logs in the demo user on the design_system guard and redirects to the
 * requested catalogue page. Headless browsers (Lambda Chrome, Playwright)
 * carry the session cookie through the redirect, so the catalogue renders
 * authenticated for the screenshot pass.
 *
 * The route is harmless even if exposed: it can only authenticate as the
 * stub demo user, which has no privileges outside the design-system panel.
 */
class ScreenshotSessionController
{
    public function __invoke(Request $request, string $page): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired screenshot signature.');
        }

        $user = DesignSystemUser::query()
            ->where('email', config('design-system.demo_user.email'))
            ->orderBy('id')
            ->first()
            ?? DesignSystemUser::query()->orderBy('id')->first();

        if (! $user) {
            abort(404, 'No design-system demo user is seeded.');
        }

        Auth::guard('design_system')->login($user);

        return redirect()->route("filament.design-system.pages.{$page}");
    }
}
