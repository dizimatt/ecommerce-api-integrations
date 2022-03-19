<?php

namespace App\DB;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    public function filter($filters)
    {
        $filter_list = $this;

        foreach ($filters as $filterKey => $filterVal)
        {
            foreach ($this->table_mapper as $mapperKeyUI => $mapperKeyDb){
                if ($mapperKeyUI == $filterKey) {
                    $filterKey = $mapperKeyDb;
                }
            }

            $isColExist = Schema::hasColumn($this->table,$filterKey);
            if ($isColExist) {

                if ($filterVal != '') {
//                    echo 'Filter: ' . $filterKey . ' | ' . $filterVal . '</br>';
                    $filter_list = $filter_list->where($filterKey, 'LIKE', '%' . $filterVal . '%')
                        ->where('store_id', store()->id);
                }
            }

        }

        $filter_list = $filter_list->get();

        return $filter_list;
    }

    public static function reorder($data, $order){

        $result = array();

        foreach ($data as &$dataRow)
        {
            $dataArray = array();
            foreach ($order as $key => $value) {
                if ( array_key_exists($value, $dataRow) ) {
                    $dataArray[$value] = $dataRow[$value];
                }
            }

            $result[] = $dataArray;
        }

        return $result;
    }

    public static function hide($data, $hideFields)
    {
        foreach ($data as &$dataRow)
        {
            foreach ($hideFields as $field){
                if (array_key_exists($field, $dataRow)){
                    unset($dataRow[$field]);
                }
            }
        }

        return $data;
    }

    public function isValid()
    {
        $values = $this->getAttributes();

        // parsing validation rules
        $rules = $this->validationRules;

        $v = \Validator::make($values, $rules);
        $isValid = !$v->fails();
        $this->errors = $isValid ? new \Illuminate\Support\MessageBag() : $v->messages();

        return $isValid;
    }
}
