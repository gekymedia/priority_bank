<?php

namespace App\Console\Commands;

use App\Models\SystemRegistry;
use Illuminate\Console\Command;

class ReportLegacySystemOwners extends Command
{
    protected $signature = 'bank:report-legacy-system-owners';

    protected $description = 'Print systems_registry rows for legacy split system_ids (personal_ceo, gekymedia, priority_admissions, priority_agriculture) with linked user id/name — use for .env LEGACY_USER_* or to verify imports.';

    private const LEGACY_SYSTEM_IDS = [
        'personal_ceo',
        'gekymedia',
        'priority_admissions',
        'priority_agriculture',
    ];

    public function handle(): int
    {
        $rows = [];
        foreach (self::LEGACY_SYSTEM_IDS as $sid) {
            $sys = SystemRegistry::withTrashed()
                ->where('system_id', $sid)
                ->with('user:id,name,email,type')
                ->first();
            if (! $sys) {
                $rows[] = [$sid, '—', '—', '—', 'MISSING in systems_registry'];
                continue;
            }
            $u = $sys->user;
            $rows[] = [
                $sid,
                (string) $sys->id,
                $u ? (string) $u->id : '—',
                $u ? $u->name : '—',
                $sys->trashed() ? 'soft-deleted' : 'ok',
            ];
        }

        $this->table(
            ['system_id', 'registry_id', 'user_id', 'user_name', 'note'],
            $rows
        );

        $this->newLine();
        $this->comment('Suggested .env lines (when JSON omits user_id); importer also reads systems_registry.user_id:');
        $map = [
            'personal_ceo' => 'LEGACY_USER_PERSONAL_CEO',
            'gekymedia' => 'LEGACY_USER_GEKYMEDIA',
            'priority_admissions' => 'LEGACY_USER_PRIORITY_ADMISSIONS',
            'priority_agriculture' => 'LEGACY_USER_PRIORITY_AGRICULTURE',
        ];
        foreach (self::LEGACY_SYSTEM_IDS as $sid) {
            $sys = SystemRegistry::where('system_id', $sid)->first();
            $uid = $sys && $sys->user_id ? (string) $sys->user_id : '<id>';
            $this->line($map[$sid].'='.$uid);
        }

        return self::SUCCESS;
    }
}
