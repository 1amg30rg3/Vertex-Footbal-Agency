<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class RepeaterSync
{
    /**
     * @param  HasMany  $relation  the relation to reconcile
     * @param  array<int, array<string, mixed>>  $rows  the submitted payload, in display order
     * @param  callable(array, int, ?Model): array  $map  row payload => model attributes
     * @param  array<string, string>  $mediaFields  attribute => storage folder
     * @return Collection<int, Model> the persisted models, in order
     */
    public static function sync(
        HasMany $relation,
        array $rows,
        callable $map,
        array $mediaFields = [],
        ?callable $afterEach = null,
    ): Collection {
        $existing = $relation->get()->keyBy('id');
        $kept = [];
        $result = collect();

        foreach (array_values($rows) as $index => $row) {
            $id = $row['id'] ?? null;
            /** @var Model|null $model */
            $model = $id ? $existing->get((int) $id) : null;

            $attributes = $map($row, $index, $model);

            foreach ($mediaFields as $attribute => $folder) {
                if (! array_key_exists($attribute, $row)) {
                    continue;
                }

                $attributes[$attribute] = MediaUploader::store(
                    $row[$attribute],
                    $folder,
                    $model?->getAttribute($attribute),
                );
            }

            if ($model) {
                $model->fill($attributes)->save();
            } else {
                $model = $relation->create($attributes);
            }

            $kept[] = $model->getKey();
            $result->push($model);

            if ($afterEach) {
                $afterEach($model, $row, $index);
            }
        }

        $existing
            ->reject(fn (Model $model) => in_array($model->getKey(), $kept, true))
            ->each(function (Model $model) use ($mediaFields) {
                foreach (array_keys($mediaFields) as $attribute) {
                    MediaUploader::forget($model->getAttribute($attribute));
                }

                $model->delete();
            });

        return $result;
    }
}
