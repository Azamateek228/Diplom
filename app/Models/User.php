<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ticket;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'city_id',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'is_locked',
        'locked_at',
        'lock_expires_at',
        'failed_login_attempts',
        'lock_reason',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function twoFactorCodes(): HasMany
    {
        return $this->hasMany(TwoFactorCode::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'lock_expires_at' => 'datetime',
    ];

    /**
     * Проверка, заблокирован ли пользователь
     */
    public function isLocked(): bool
    {
        if (!$this->is_locked) {
            return false;
        }

        // Если срок блокировки истёк, разблокировать
        if ($this->lock_expires_at && $this->lock_expires_at->isPast()) {
            $this->unlock();
            return false;
        }

        return true;
    }

    /**
     * Блокировка пользователя
     */
    public function lock(string $reason = null, int $minutes = 30): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'lock_expires_at' => now()->addMinutes($minutes),
            'lock_reason' => $reason,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Разблокировка пользователя
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'lock_expires_at' => null,
            'lock_reason' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Увеличение счётчика неудачных попыток входа
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        
        // Блокировка после 5 неудачных попыток
        if ($this->failed_login_attempts >= 5) {
            $this->lock('Слишком много неудачных попыток входа', 30);
        }
    }

    /**
     * Сброс счётчика неудачных попыток
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update(['failed_login_attempts' => 0]);
    }

    /**
     * Генерация кода двухфакторной аутентификации
     */
    public function generateTwoFactorCode(): string
    {
        // Удаляем старые неиспользованные коды
        $this->twoFactorCodes()->where('used', false)->delete();

        // Генерируем 6-значный код
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->twoFactorCodes()->create([
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        return $code;
    }

    /**
     * Проверка кода двухфакторной аутентификации
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $twoFactorCode = $this->twoFactorCodes()
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($twoFactorCode) {
            $twoFactorCode->update(['used' => true]);
            return true;
        }

        return false;
    }
}
