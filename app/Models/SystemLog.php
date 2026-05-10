<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'account_id',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public static function record(string $action, ?string $module = null, array $data = []): void
    {
        try {
            $request = request();
            $account = auth()->user();

            self::create([
                'account_id' => $data['account_id'] ?? $account?->id,
                'action' => $action,
                'module' => $module,
                'subject_type' => $data['subject_type'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'subject_label' => $data['subject_label'] ?? null,
                'description' => $data['description'] ?? null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable) {
            // Logging should never block the user's main workflow.
        }
    }
}
