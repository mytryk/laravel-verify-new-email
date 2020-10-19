<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

trait VerifiesPendingEmails
{
    /**
     * Mark the user's new email address as verified.
     * @param string $token
     * @return Application|RedirectResponse|Redirector
     * @throws InvalidVerificationLinkException
     */
    public function verify(string $token)
    {
        $user = app(config('verify-new-email.model'))->where('token', '=', $token)->firstOr(['*'], function () {
            throw new InvalidVerificationLinkException(
                __('The verification link is not valid anymore.')
            );
        })->tap(function ($pendingUserEmail) {
            /** @var PendingUserEmail $pendingUserEmail */
            $pendingUserEmail->activate();
        })->user
        ;

        return $this->authenticated($user);
    }

    protected function authenticated($user)
    {
        if (config('verify-new-email.login_after_verification')) {
            Auth::guard()->login($user, config('verify-new-email.login_remember'));
        }
        return redirect(config('verify-new-email.redirect_to'))->with('verified', true);
    }
}
