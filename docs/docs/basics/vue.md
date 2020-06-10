# Extend With Vue

The Fjord interface can be extended with custom Vue components for numerous purposes.

## Setup

To include your own `Vue` components in the Fjord application, the locale npm package `vendor/aw-studio/fjord` has to be installed. This can be done using the following artisan command:

```shell
php artisan fjord:extend
```

At the beginning of your `webpack.mix.js` the import of the fjord mix config will be added automatically. Two files are compiled:

-   `fjord/resources/js/app.js` => `public/fjord/js/app.js`
-   `fjord/resources/sass/app.scss` => `public/fjord/css/app.css`

Add them to **assets** in the config.

```php
// config/fjord.php
'assets' => [
    'js' => '/fjord/js/app.js',
    'css' => [
        '/public/fjord/css/app.css',
        // Add more css files here ...
    ],
],
```

All javascript files can be found in `fjord/resources/js`.

::: tip
Components that are created in the `components` folder are automatically registered.
:::

Run `npm run watch` and you are good to go.

::: warning
Dont forget to compile your assets every time you **update** your Fjord version.
:::

## Bootstrap Vue

To make it easy to build uniform Fjord pages, Fjord uses [Bootstrap Vue](https://bootstrap-vue.org/docs/components) for all frontend components. Bootstrap Vue comes with a large number of components to cover all the necessary areas needed to build an application.

## Build Your Own Page

To build a new page for your Fjord application you have to view your root component. This is done by giving the View `fjord::app` the name of your `Vue` component and passing the required data as props like so.

```php
use App\Models\Post;

return view('fjord::app')
    ->withComponent('my-component') // Name of your Vue component.
    ->withTitle('Posts') // Html title.
    ->props([
        'posts' => Post::all()
    ]);
```

In this case `posts` is passed as prop to the component `my-component`.

The following example shows how to build a root component of a page for a Fjord application.

```javascript
<template>
    <fj-container>
        <fj-navigation/>
        <fj-header title="Posts"/>

        <b-row>
            <fj-col :width="1 / 3" v-for="(post, key) in posts" :key="key">
                <b-card :title="post.title">
                    {{ post.text }}
                </b-card>
            </fj-col>
        </b-row>
    </fj-container>
</template>
<script>
export default {
    name: 'MyComponent',
    props: {
        posts: {
            required: true,
            type: Array
        }
    }
}
</script>
```

::: tip
Read more about how to build your custom page in the [Vue Components](/docs/frontend/components.html#custom-pages) section.
:::
