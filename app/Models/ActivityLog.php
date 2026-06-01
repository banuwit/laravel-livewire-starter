<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;

class ActivityLog extends BaseActivity
{
    public function getChangesAttribute(): array
    {
        $old = $this->properties->get('old', []);
        $new = $this->properties->get('attributes', []);

        if (empty($old) && empty($new)) {
            return [];
        }

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $changes = [];

        foreach ($keys as $key) {
            $before = $old[$key] ?? null;
            $after  = $new[$key] ?? null;

            if ($before !== $after) {
                $changes[] = [
                    'field'  => $key,
                    'before' => $before,
                    'after'  => $after,
                ];
            }
        }

        return $changes;
    }

    public function subjectLabel(): string
    {
        if (!$this->subject_type) {
            return '—';
        }
        return class_basename($this->subject_type);
    }
}
