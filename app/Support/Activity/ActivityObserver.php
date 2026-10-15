<?php

namespace App\Support\Activity;

use Illuminate\Database\Eloquent\Model;

final class ActivityObserver
{
    private \WeakMap $deletions;

    public function __construct(private ActivityRecorder $recorder)
    {
        $this->deletions = new \WeakMap;
    }

    public function created(Model $model): void { $this->recorder->modelChange($model, 'created'); }
    public function updated(Model $model): void { $this->recorder->modelChange($model, 'updated'); }

    public function deleting(Model $model): void
    {
        if ($this->recorder->enabled()) {
            $this->deletions[$model] = ['branch' => $this->recorder->branch($model), 'protected' => $this->recorder->protectedSubject($model)];
        }
    }

    public function deleted(Model $model): void
    {
        $this->recorder->modelChange($model, 'deleted', $this->deletions[$model] ?? null);
        unset($this->deletions[$model]);
    }
}
