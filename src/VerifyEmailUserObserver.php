<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;

class VerifyEmailUserObserver
{
    public function updating(Model $user): void
    {
        $dirty = $user->getDirty();
        $original_email = $user->getOriginal('email');
        if (array_key_exists('email', $dirty) && $dirty['email'] !== $original_email) {
            if (!$user->hasAppended(PendingUserEmail::USER_MODEL_APPEND_VERIFIED)) {
                $user->sendEmailVerificationNotification();
                $user->email = $original_email;
            }
        }
    }
}
