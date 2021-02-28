<?php

namespace Seat\Eveapi\Mapping;

use Closure;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DataMapping.
 * @package Seat\Eveapi\Mapping
 */
abstract class DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        // target model property => source data field
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $data
     * @param array $overrides
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function make(Model $model, $data, array $overrides = []): Model
    {
        // merge both mapping and overrides to build final mapping rules
        $rules = array_merge(static::$mapping, $overrides);

        // prepare a JSON Query navigator
        $json_path = new JSONPath($data);

        // loop over rules and apply data mapping
        foreach ($rules as $model_field => $source_field) {

            // if source field is a closure, apply its business logic
            if ($source_field instanceof Closure) {
                $model->{$model_field} = $source_field();
            } else {
                try {
                    $model->{$model_field} = $json_path->find($source_field)->first();
                } catch (JSONPathException $e) {
                    $model->{$model_field} = null;

                    logger()->error($e->getMessage(), $e->getTrace());
                }
            }
        }

        return $model;
    }
}
