<?php

namespace Ignite\Crud\Repositories\Relations;

use Ignite\Crud\Fields\Relations\OneRelationField;
use Ignite\Crud\Models\Relation;
use Ignite\Crud\Repositories\BaseFieldRepository;
use Ignite\Crud\Requests\CrudUpdateRequest;

class OneRelationRepository extends BaseFieldRepository
{
    use Concerns\ManagesRelated;

    /**
     * Create new OneRelationRepository instance.
     */
    public function __construct($config, $controller, $form, OneRelationField $field)
    {
        parent::__construct($config, $controller, $form, $field);
    }

    /**
     * Create new oneRelation.
     *
     * @param CrudUpdateRequest $request
     * @param mixed             $model
     *
     * @return void
     */
    public function create(CrudUpdateRequest $request, $model)
    {
        $related = $this->getRelated($request, $model);

        $query = [
            'from_model_type' => get_class($model),
            'from_model_id'   => $model->id,
            'to_model_type'   => get_class($related),
            'field_id'        => $this->field->id,
        ];

        $class = config('lit.models.relation');

        // Replace previous relation with new one.
        $class::where($query)->delete();
        $query['to_model_id'] = $related->id;
        $class::create($query);
    }

    /**
     * Destroy oneRelation.
     *
     * @param CrudUpdateRequest $request
     * @param mixed             $model
     *
     * @return void
     */
    public function destroy(CrudUpdateRequest $request, $model)
    {
        $related = $this->getRelated($request, $model);

        $query = [
            'from_model_type' => get_class($model),
            'from_model_id'   => $model->id,
            'to_model_type'   => get_class($related),
            'to_model_id'     => $related->id,
            'field_id'        => $this->field->id,
        ];

        $class = config('lit.models.relation');

        $class::where($query)->delete();

        $this->deleteIfDesired($request, $related);
    }
}
