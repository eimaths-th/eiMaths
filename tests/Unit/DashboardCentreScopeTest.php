<?php

namespace Tests\Unit;

use App\Http\Controllers\DashboardController;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardCentreScopeTest extends TestCase
{
    public function test_branch_user_cannot_override_their_centre(): void
    {
        $user = new User(['department_id' => 3]);
        $user->setRelation('roles', collect());
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/dashboard', 'POST', ['dcentre' => '2']);

        $this->assertSame('3', $this->controller()->resolveCentre($request));
    }

    public function test_hq_user_can_select_a_branch(): void
    {
        $user = new User(['department_id' => Department::HQ_ID]);
        $user->setRelation('roles', collect([new \Spatie\Permission\Models\Role(['name' => 'SystemAdmin', 'guard_name' => 'web'])]));
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/dashboard', 'POST', ['dcentre' => '3']);

        $this->assertSame('3', $this->controller()->resolveCentre($request));
    }

    public function test_hq_defaults_to_all_centres(): void
    {
        $user = new User(['department_id' => Department::HQ_ID]);
        $user->setRelation('roles', collect([new \Spatie\Permission\Models\Role(['name' => 'SystemAdmin', 'guard_name' => 'web'])]));
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/dashboard', 'POST');

        $this->assertSame((string) Department::HQ_ID, $this->controller()->resolveCentre($request));
    }

    private function controller(): DashboardController
    {
        return new class extends DashboardController
        {
            public function resolveCentre(Request $request): string
            {
                return $this->selectedCentre($request);
            }
        };
    }
}
