<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdjustCreatedAtScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable(); // الحصول على اسم الجدول ديناميكيًا
        $builder->addSelect([
            "$table.*", // جلب جميع الأعمدة
            \DB::raw("DATE_ADD($table.created_at, INTERVAL 3 HOUR) as created_at")
        ]);
    }
}
