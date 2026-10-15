<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BranchTeacherRole extends Command
{
    protected $signature = 'branch:teacher-role {centreCode : Legacy centre code (ignored)} {--admin= : Legacy administrator reference (ignored)} {--apply : Accepted for compatibility; this retired command never writes}';

    protected $description = 'Retired: assign the existing Teacher role from Users instead of creating a trimmed branch role';

    public function handle(): int
    {
        $this->error("This legacy command is retired. Assign the existing Teacher role from Users; roles are no longer trimmed to the creator's permissions. Existing roles/users unchanged.");

        return self::FAILURE;
    }
}
