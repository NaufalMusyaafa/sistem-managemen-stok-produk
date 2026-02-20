<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WarehouseScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * If the authenticated user has the 'admin_up3' role, this scope
     * automatically filters queries to only return records matching
     * the user's assigned warehouse_id.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin_up3' && $user->warehouse_id) {
            $builder->where($model->getTable() . '.warehouse_id', $user->warehouse_id);
        }
    }
}
