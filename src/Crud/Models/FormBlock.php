<?php

namespace Fjord\Crud\Models;

use Fjord\Crud\Fields\Block\Block;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormBlock extends FjordFormModel
{
    /**
     * Translation model.
     *
     * @var string
     */
    protected $translationModel = Translations\FormBlockTranslation::class;

    /**
     * No timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'model_type',
        'model_id',
        'type',
        'content',
        'order_column',
        'value'
    ];

    /**
     * Appends.
     *
     * @var array
     */
    protected $appends = ['fields', 'translation'];

    /**
     * Eager loads.
     *
     * @var array
     */
    protected $with = ['translations', 'media'];

    /**
     * Model relation.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get config key from model relation.
     *
     * @return string
     */
    public function getConfigKey()
    {
        if ($this->model instanceof FormField) {
            return $this->model->getConfigKey();
        }

        return "crud." . lcfirst(class_basename(get_class($this->model)));
    }

    /**
     * Get fields from config.
     *
     * @return Field
     */
    public function getFieldsAttribute()
    {
        $fields = $this->config->show->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field instanceof Block && $field->id == $this->field_id) {

                // Returning fields from repeatables form.
                return $field->repeatables->{$this->type}->getRegisteredFields();
            }
        }
    }

    /**
     * Get an attribute array of all arrayable relations.
     *
     * @return array
     */
    protected function getArrayableRelations()
    {
        $items = $this->getArrayableItems($this->relations);

        // Removing model relation from arrayable items to avoid infinite loop.
        unset($items['model']);

        return $items;
    }
}
