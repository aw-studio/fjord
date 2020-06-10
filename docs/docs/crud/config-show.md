# Show Config

[[toc]]

In the `show` method of a **Form** or a **CRUD** config all components and fields are configured for editing the data.

```php
use Fjord\Crud\CrudShow;

public function show(CrudShow $container)
{
    // Define the page here.
}
```

## Card

Form fields must be grouped into `cards`. You are free to create only one or any number of cards for a form.

```php
use Fjord\Crud\CrudShow;

public function show(CrudShow $container)
{
    $container->card(function ($form) {

        // Build your form in here.

        $form->input('first_name')->title('First Name');

    })
    ->title('Card Title')
    ->width(8);
}
```

All available fields can be found in the documentation under [Fields](/docs/fields/introduction.html).

## Group

With `group` fields can be grouped in a column. This is useful to organize form elements of different heights side by side.

```php
$form->group(function($form) {
    // Build your form inside the col.
})->width(6);
```

## Component

With `component` a custom **Vue component** can be integrated.

```php
use Fjord\Crud\CrudShow;

public function show(CrudShow $container)
{
    $container->component('my-component');

    // ...
}
```

Read the [Extend Vue](/docs/basics/vue.html#bootstrap-vue) section to learn how to register your own Vue components.

## Info

A good content administration interface includes **descriptions** that help the user to quickly understand what is happening on the interface. Such information can be created outside and inside of cards like this:

```php
use Fjord\Crud\CrudShow;

public function show(CrudShow $container)
{
    $container->info('Address')
        ->width(4)
        ->text('This address appears on your <a href="'.route('invoices').'">invoices</a>.')
        ->text(...);

    // ...
}
```

## Container Size

By default, the container on the show page has a maximum width. If you want the containers to expand to the maximum width for a better overview, this can be achieved with the `expand` method.

```php
use Fjord\Crud\CrudShow;

public function show(CrudShow $container)
{
    $container->expand();

    // ...
}
```

## Preview

It is possible to get a `preview` of the stored data directly in the update form. The **route** for this can be easily specified using the method `previewRoute`. For a CRUD Model, the corresponding model is also passed as a parameter.

```php
public function previewRoute($article)
{
    return route('article', $article->id);
}
```

Now the page can be previewed for the devices **desktop**, **tablet** or **mobile** like in the following screenshot:

![Fjord Crud Preview](./preview.png 'Fjord Crud Preview')

### Default Device

The default device can be changed in the config `fjord.php` under 'crud.preview.default_device'.

```php
'crud' => [
    'preview' => [
        // Available devices: mobile, tablet, desktop
        'default_device' => 'desktop'
    ]
],
```
