<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Branch\BranchAccess;
use App\Support\Branch\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class BranchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(BranchContext::class, fn () => new BranchContext);
    }

    public function boot(): void
    {
        \Spatie\Permission\Models\Permission::saved(fn ($permission) => app(BranchContext::class)->audit(
            $permission->wasRecentlyCreated ? 'permission.created' : 'permission.updated',
            get_class($permission), $permission->id, ['name' => $permission->name]));
        \Spatie\Permission\Models\Permission::deleted(fn ($permission) => app(BranchContext::class)->audit(
            'permission.deleted', get_class($permission), $permission->id, ['name' => $permission->name]));
        foreach (config('branch.models') as $class => $rule) {
            $class::addGlobalScope('branch-access', function (Builder $query): void {
                app(BranchContext::class)->scope($query);
            });
            $class::saving(fn (Model $model) => app(BranchContext::class)->validateWrite($model));
            $class::deleting(function (Model $model): void {
                app(BranchContext::class)->validateWrite($model);
                if ($model instanceof User && $model->hasRole('SystemAdmin')) {
                    BranchAccess::assertOwnerRemains([$model->id]);
                }
            });
            $class::saved(fn (Model $model) => app(BranchContext::class)->audit($model->wasRecentlyCreated ? 'created' : 'updated', get_class($model), $model->getKey(), array_intersect_key($model->getChanges(), array_flip(['department_id', 'centre', 'centre_id', 'branch_id', 'status', 'disable', 'enabled_modules']))));
            $class::deleted(fn (Model $model) => app(BranchContext::class)->audit('deleted', get_class($model), $model->getKey()));
        }
    }
}
