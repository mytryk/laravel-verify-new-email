<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Tappable;

/**
 * Class PendingUserEmail
 * @package ProtoneMedia\LaravelVerifyNewEmail
 * @method forUser
 * @method whereEmail
 * @property string $token
 * @property string $email
 */
class PendingUserEmail extends Model
{
    use Tappable;

    /**
     * This model won't be updated.
     */
    public const UPDATED_AT = null;

    public const USER_MODEL_APPEND_VERIFIED = 'new_email_verified';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * User relationship
     *
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * Scope for the user.
     *
     * @param $query
     * @param Model $user
     * @return void
     */
    public function scopeForUser($query, Model $user): void
    {
        $query->where([
            $this->qualifyColumn('user_type') => get_class($user),
            $this->qualifyColumn('user_id')   => $user->getKey(),
        ]);
    }

    /**
     * Updates the associated user and removes all pending models with this email.
     *
     * @return void
     */
    public function activate(): void
    {
        $user = $this->user;

        $dispatchEvent = !$user->hasVerifiedEmail() || $user->email !== $this->email;
        $user->append([static::USER_MODEL_APPEND_VERIFIED => true]);
        $user->email = $this->email;
        $user->save();
        $user->markEmailAsVerified();

        static::whereEmail($this->email)->get()->each->delete();

        $dispatchEvent ? event(new Verified($user)) : null;
    }

    /**
     * Creates a temporary signed URL to verify the pending email.
     *
     * @return string
     */
    public function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            config('verify-new-email.route') ?: 'pendingEmail.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['token' => $this->token]
        );
    }

}
