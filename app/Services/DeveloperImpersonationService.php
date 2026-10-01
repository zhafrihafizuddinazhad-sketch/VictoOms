<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DeveloperImpersonationService
{
    public const ORIGINAL_USER = 'developer_original_user_id';
    public const TARGET_USER = 'developer_impersonated_user_id';
    public const TARGET_ROLE = 'developer_impersonated_role';
    public const ACTIVE = 'developer_impersonating';

    public function start(User $target, string $role): void
    {
        $developer = Auth::user();

        abort_unless($developer?->hasRole('developer'), 403);
        abort_if(Session::get(self::ACTIVE), 403, 'An impersonation session is already active.');
        abort_if($target->hasRole('developer'), 403, 'Developer accounts cannot be impersonated.');
        abort_unless(in_array($role, ['owner', 'admin', 'designer', 'cameraman'], true), 403);
        abort_unless($target->hasRole($role), 403, 'The selected account does not have this role.');
        abort_unless($target->account_status === 'active', 403, 'Only active accounts can be impersonated.');

        Session::put([
            self::ORIGINAL_USER => $developer->getKey(),
            self::TARGET_USER => $target->getKey(),
            self::TARGET_ROLE => $role,
            self::ACTIVE => true,
        ]);

        Auth::login($target);
        Session::regenerate();
    }

    public function stop(): ?User
    {
        $originalId = Session::get(self::ORIGINAL_USER);
        $targetId = Session::get(self::TARGET_USER);
        $developer = $originalId ? User::find($originalId) : null;

        if (! Session::get(self::ACTIVE)
            || ! $developer
            || ! $developer->hasRole('developer')
            || $developer->account_status !== 'active'
            || (string) Auth::id() !== (string) $targetId) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
            return null;
        }

        Auth::login($developer);
        Session::forget([self::ORIGINAL_USER, self::TARGET_USER, self::TARGET_ROLE, self::ACTIVE]);
        Session::regenerate();

        return $developer;
    }
}
