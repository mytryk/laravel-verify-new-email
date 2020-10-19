<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

trait MustVerifyNewEmail
{
    /**
     * Deletes all previous attempts for this user, creates a new model/token
     * to verify the given email address and send the verification URL
     * to the new email address.
     * @param string $email
     * @return Model|null
     * @throws InvalidEmailVerificationModelException
     */
    public function newEmail(string $email): ?Model
    {
        if ($this->getOriginal('email') === $email && $this->hasVerifiedEmail()) {
            return null;
        }

        return $this->createPendingUserEmailModel($email)->tap(function ($model) {
            $this->sendPendingEmailVerificationMail($model);
        })
            ;
    }

    public function getEmailVerificationModel(): Model
    {
        $modelClass = config('verify-new-email.model');

        if (!$modelClass) {
            throw new InvalidEmailVerificationModelException;
        }

        return app($modelClass);
    }

    /**
     * Creates new PendingUserModel model for the given email.
     * @param string $email
     * @return Model
     * @throws InvalidEmailVerificationModelException
     */
    public function createPendingUserEmailModel(string $email): Model
    {
        $this->clearPendingEmail();

        return $this->getEmailVerificationModel()->create([
            'user_type' => get_class($this),
            'user_id' => $this->getKey(),
            'email' => $email,
            'token' => Password::broker()->getRepository()->createNewToken(),
        ]);
    }

    /**
     * Returns the pending email address.
     * @return string|null
     * @throws InvalidEmailVerificationModelException
     */
    public function getPendingEmail(): ?string
    {
        return $this->getEmailVerificationModel()->forUser($this)->value('email');
    }

    /**
     * Deletes the pending email address models for this user.
     * @return void
     * @throws InvalidEmailVerificationModelException
     */
    public function clearPendingEmail()
    {
        $this->getEmailVerificationModel()->forUser($this)->get()->each->delete();
    }

    /**
     * Sends the VerifyNewEmail Mailable to the new email address.
     * @param Model $pendingUserEmail
     * @return mixed
     */
    public function sendPendingEmailVerificationMail(Model $pendingUserEmail)
    {
        $mailableClass = config('verify-new-email.mailable_for_first_verification');

        if ($pendingUserEmail->User->hasVerifiedEmail()) {
            $mailableClass = config('verify-new-email.mailable_for_new_email');
        }

        $mailable = new $mailableClass($pendingUserEmail);

        return Mail::to($pendingUserEmail->email)->send($mailable);
    }

    /**
     * Grabs the pending user email address, generates a new token and sends the Mailable.
     * @return Model|null
     * @throws InvalidEmailVerificationModelException
     */
    public function resendPendingEmailVerificationMail(): ?Model
    {
        $pendingUserEmail = $this->getEmailVerificationModel()->forUser($this)->firstOrFail();

        return $this->newEmail($pendingUserEmail->email);
    }

}
