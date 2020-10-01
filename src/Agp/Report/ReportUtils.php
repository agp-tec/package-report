<?php


namespace Agp\Report;


use Illuminate\Database\Eloquent\Model;

class ReportUtils
{
    /** Retorna array de campos de select para queryBuilder
     * @param array $models
     * @return array
     */
    public static function getModelSelects($models)
    {
        $data = [];
        foreach ($models as $model)
            foreach ($model->getFillable() as $item)
                $data[] = $model->getTable() . '.' . $item;
        return $data;
    }
}
