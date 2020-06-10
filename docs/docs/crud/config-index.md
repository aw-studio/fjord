# Index Config

[[toc]]

Each Crud configuration can have an index page that shows an overview of the models. On this page you can display an **index table** and add your own `Vue` components to display additional data. The frontend **container** of the index page is configured in the `index` function.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    // Build your index page here.
}
```

In the Config for a CRUD model its index table is defined.

## Table

The index table is built using the method `table` like this:

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(function($table) {
        $table->col('Name')->value('{first_name} {last_name}');
    });
}
```

The [table config](/docs/crud/config-table) describes how columns with **images**, **relations** and much more can be built.

## Query

In the `query` method the `query` for the index table is initialized. Here you have to specify all the `relations` that should be searchable or to be displayed in the table.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)
        ->query(function($query) {
            $query->with('department')
            ->withCount('projects_count');
        });
}
```

## Search

All attributes to be searched for are specified in `search`. You can also specify `relations` and their attributes.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)->search('title', 'department.name');
}
```

## Sort

You can sort by all model `attributes` as well as `relations` `attributes`. The sortBy attributes are specified as follows: `{attributes}.{desc|asc}`. The default attribute to sort by is specified in the `sortByDefault` method.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)->sortByDefault('id.desc');
}
```

In this example you can see how the array for the sort attributes can look like to sort by `id` or by a `relation`.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)
        ->sortBy([
            'id.desc' => 'New first',
            'id.asc' => 'Old first',
            'department.name.desc' => 'Department A-Z',
            'department.name.asc' => 'Department Z-A'
        ]);
}
```

## Filter

Filters are specified in groups. Laravel's model [`scopes`](https://laravel.com/docs/7.x/eloquent#local-scopes) are used to filter the index table as shown in the example:

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)
        ->filter([
            'Department' => [
                'development' => 'Development',
                'marketing' => 'Marketing',
            ],
        ]);
}
```

```php
// Model
public function scopeDevelopment()
{
    return $this->hasOne('App\Models\Department')->where('name', 'development');
}

public function scopeMarketing()
{
    return $this->hasOne('App\Models\Department')->where('name', 'marketing');
}
```

## Pagination

The maximum number of items to be displayed on a page is defined in `perPage`. The default is `10`.

```php
use Fjord\Crud\CrudIndex;

public function index(CrudIndex $container)
{
    $container->table(...)->perPage(5);
}
```
