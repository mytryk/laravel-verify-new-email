<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;

class VerifiedEmailUserObserver
{
    public function updating(Model $user): void
    {
        $dirty = $user->getDirty();
        $original_email = $user->getOriginal('email');
        if (array_key_exists('email', $dirty) && $dirty['email'] !== $original_email) {
            if (!$user->hasAppended(PendingUserEmail::USER_MODEL_APPEND_VERIFIED)) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
                $user->email = $original_email;
            }
        }
    }
}
