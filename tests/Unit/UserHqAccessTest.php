<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserHqAccessTest extends TestCase
{
    public function test_hq_user_is_identified_by_the_hq_department(): void
    {
        $user = new User(['department_id' => Department::HQ_ID]);

        $this->assertTrue($user->isHq());
    }

    public function test_branch_user_is_not_identified_as_hq(): void
    {
        $user = new User(['department_id' => 3]);

        $this->assertFalse($user->isHq());
    }
}
