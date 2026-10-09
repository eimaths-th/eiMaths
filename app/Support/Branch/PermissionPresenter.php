<?php

namespace App\Support\Branch;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PermissionPresenter
{
    public static function groups(Collection $permissions): array
    {
        $definitions = config('permission-ui.groups', []);
        $remaining = $permissions->sortBy(fn ($permission) => strtolower($permission->name))->values();
        $groups = [];

        foreach ($definitions as $key => $definition) {
            if ($definition['fallback'] ?? false) {
                $matches = $remaining;
            } else {
                $matches = $remaining->filter(
                    fn ($permission) => self::matches(strtolower($permission->name), $definition)
                )->values();
            }
            if ($matches->isEmpty()) {
                continue;
            }
            $matchedIds = $matches->pluck('id')->all();
            $remaining = $remaining->reject(fn ($permission) => in_array($permission->id, $matchedIds, true))->values();
            $groups[] = [
                'key' => $key,
                'label' => $definition['label'],
                'description' => $definition['description'] ?? '',
                'icon' => $definition['icon'] ?? 'fas fa-key',
                'permissions' => $matches->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'label' => self::label($permission->name),
                ])->all(),
            ];
        }

        return $groups;
    }

    public static function label(string $permission): string
    {
        $name = strtolower($permission);
        $translation = 'user-access.permission_labels.'.$name;
        if (\Illuminate\Support\Facades\Lang::has($translation)) {
            return __($translation);
        }
        $exact = config('permission-ui.labels', []);
        if (isset($exact[$name])) {
            return $exact[$name];
        }
        foreach (config('permission-ui.action_labels', []) as $suffix => $label) {
            if ($name === $suffix) {
                return $label;
            }
            $actionSuffix = '-'.$suffix;
            if (str_ends_with($name, $actionSuffix)) {
                $resource = substr($name, 0, -strlen($actionSuffix));
                $resourceLabel = config('permission-ui.resource_labels.'.$resource)
                    ?: Str::headline(str_replace('-', ' ', $resource));

                return $label.': '.$resourceLabel;
            }
        }

        return Str::headline(str_replace('-', ' ', $permission));
    }

    private static function matches(string $permission, array $definition): bool
    {
        if (in_array($permission, array_map('strtolower', $definition['permissions'] ?? []), true)) {
            return true;
        }
        foreach ($definition['prefixes'] ?? [] as $prefix) {
            if (str_starts_with($permission, strtolower($prefix))) {
                return true;
            }
        }

        return false;
    }
}
