<?php

namespace Fjord\Crud\Fields\Relations;

use Closure;
use Fjord\Vue\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Fjord\Crud\RelationField;
use Fjord\Support\Facades\Crud;
use Fjord\Support\Facades\Config;
use Fjord\Exceptions\InvalidArgumentException;

class LaravelRelationField extends RelationField
{
    /**
     * Relation query builder.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Index query modifier.
     *
     * @var Closure|null
     */
    protected $previewModifier;

    /**
     * Relation model class.
     *
     * @var string
     */
    protected $relatedModelClass;

    /**
     * Required field attributes.
     *
     * @var array
     */
    public $laravelRelationFieldRequired = ['preview'];

    /**
     * Create new Field instance.
     *
     * @param string $id
     * @param string $model
     * @param string|null $routePrefix
     */
    public function __construct(string $id, string $model, $routePrefix, $form)
    {
        parent::__construct($id, $model, $routePrefix, $form);

        $this->initializeRelationField();
    }

    /**
     * Set default field attributes.
     *
     * @return array
     */
    public function setDefaultAttributes()
    {
        parent::setDefaultAttributes();

        $this->search('title');
        $this->confirm();
        $this->small(false);
    }

    /**
     * Set model and query builder.
     *
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    protected function initializeRelationField()
    {
        $relatedInstance = $this->getRelatedInstance();

        $this->setRelatedModelClass(get_class($relatedInstance));
        $this->query = $this->getRelatedModelClass()::query();

        $this->setOrderDefaults();
        $this->setAttribute('model', $this->getRelatedModelClass());

        // Set relation attributes.
        if (method_exists($this, 'setRelationAttributes')) {
            $this->setRelationAttributes($this->getRelationQuery(new $this->model));
        }

        return $this;
    }

    /**
     * Set related model class.
     *
     * @param string $model
     * @return $this
     */
    protected function setRelatedModelClass(string $model)
    {
        $this->relatedModelClass = $model;

        $relatedConfigKey = "crud." . Str::snake(class_basename($model));

        if (Config::exists($relatedConfigKey) && !$this->relatedConfig) {
            $this->use($relatedConfigKey);
        }

        if (!$this->names) {
            $this->names(Crud::names($model));
        }
    }

    /**
     * Merge related config.
     *
     * @param string $configKey
     * @return void
     */
    protected function mergeRelatedConfig($configKey)
    {
        if (!Config::exists($configKey)) {
            return;
        }
        $relatedConfig = Config::get($configKey);

        if (!$this->related_route_prefix) {
            $this->routePrefix($relatedConfig->route_prefix);
        }
        if ($relatedConfig->has('index')) {
            $this->setAttribute('preview', $relatedConfig->index->getTable()->getTable());
        }
    }

    /**
     * Get related model class.
     *
     * @return string
     */
    public function getRelatedModelClass()
    {
        return $this->relatedModelClass;
    }

    /**
     * Set order defaults.
     *
     * @return void
     */
    protected function setOrderDefaults()
    {
        $orders = $this->getRelationQuery(new $this->model)->getQuery()->getQuery()->orders;

        if (empty($orders)) {
            return;
        }

        $order = $orders[0];
        if (method_exists($this->relation, 'getTable')) {
            $orderColumn = str_replace($this->relation->getTable() . '.', '', $order['column']);
        } else {
            $orderColumn = $order['column'];
        }

        $this->setAttribute('orderColumn', $orderColumn);
        $this->setAttribute('orderDirection', $order['direction']);
    }

    /**
     * Set index query modifier.
     *
     * @param Closure $closure
     * @return self
     */
    public function query(Closure $closure)
    {
        $this->previewModifier = $closure;

        return $this;
    }

    /**
     * Use related config.
     *
     * @param string $config
     * @return void
     */
    public function use(string $config)
    {
        $this->relatedConfig = Config::get($config);

        if (!$this->relatedConfig) {
            throw new InvalidArgumentException("Couldn't find config {$config}");
        }

        if ($this->relatedConfig->model != $this->getRelatedModelClass() && $this->getRelatedModelClass() != null) {
            throw new InvalidArgumentException("Related config {$config} must be using model " . $this->getRelatedModelClass());
        }

        if (method_exists($this, 'model')) {
            $this->model($this->relatedConfig->model);
        }
        if ($this->relatedConfig->has('index')) {
            if ($this->relatedConfig->index) {
                $table = clone $this->relatedConfig->index
                    ->getTable()
                    ->getTable()
                    ->disableLink();

                $this->setAttribute('preview', $table);
            }
        }

        $this->routePrefix($this->relatedConfig->route_prefix);

        return $this;
    }

    /**
     * Get relation query for model.
     *
     * @param mixed $model
     * @return mixed
     */
    public function getRelationQuery($model)
    {
        return $this->modifyQuery(
            $model->{$this->id}()
        );
    }

    /**
     * Get related model instance.
     *
     * @return mixed
     */
    protected function getRelatedInstance()
    {
        return $this->getRelationQuery(
            new $this->model
        )->getRelated();
    }

    /**
     * Modify preview query with eager loads and accessors to append.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function modifyQuery($query)
    {
        if (!$this->previewModifier instanceof Closure) {
            return $query;
        }

        $modifier = $this->previewModifier;
        $modifier($query);

        return $query;
    }

    /**
     * Build relation index table.
     *
     * @param Closure $closure
     * @return $this
     */
    public function preview(Closure $closure)
    {
        $table = new Table;

        $closure($table);

        $this->attributes['preview'] = $table;

        return $this;
    }

    /**
     * Singular and plural name.
     *
     * @param Closure $closure
     * @return $this
     */
    public function names(array $names)
    {
        if (!array_key_exists('singular', $names) || !array_key_exists('plural', $names)) {
            throw new InvalidArgumentException('Singular and plural name may be present.');
        }

        $this->setAttribute('names', $names);

        return $this;
    }

    /**
     * Set prefix to related config.
     *
     * @param string $routePrefix
     * @return $this
     */
    public function routePrefix(string $routePrefix)
    {
        $this->setAttribute('related_route_prefix', $routePrefix);

        return $this;
    }

    /**
     * Set search keys
     *
     * @param array ...$keys
     * @return $this
     */
    public function search(...$keys)
    {
        $keys = Arr::wrap($keys);
        if (count($keys) == 1) {
            if (is_array($keys[0])) {
                $keys = $keys[0];
            }
        }

        $this->setAttribute('search', $keys);

        return $this;
    }

    /**
     * Set query initial builder.
     *
     * @param Closure $closure
     * @return void
     */
    public function filter(Closure $closure)
    {
        $closure($this->query);

        return $this;
    }

    /**
     * Get relation query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Small table.
     *
     * @param boolean $small
     * @return self
     */
    public function small($small = true)
    {
        $this->setAttribute('small', $small);

        return $this;
    }

    /**
     * Confirm delete in modal.
     *
     * @param boolean $confirm
     * @return self
     */
    public function confirm($confirm = true)
    {
        $this->setAttribute('confirm', $confirm);

        return $this;
    }
}
