# WYSIWYG

A **W**hat-**Y**ou-**S**ee-**I**s-**W**hat-You-**G**et editor using [CKEditor](https://ckeditor.com/).

```php
$form->wysiwyg('text')
    ->translatable()
    ->title('Description')
    ->hint('The Description for some Object.')
    ->width(1/2);
```

## Methods

| Method          | Description                                                                                                           |
| --------------- | --------------------------------------------------------------------------------------------------------------------- |
| `title`         | The title description for this field.                                                                                 |
| `hint`          | A short hint that should describe how to use the field.`                                                              |
| `width`         | Width of the field.                                                                                                   |
| `translatable`  | Should the field be translatable? For translatable crud models, the translatable fields are automatically recognized. |
| `max`           | Max characters.                                                                                                       |
| `rules`         | Rules that should be applied when **updating** and **creating**.                                                      |
| `creationRules` | Rules that should be applied when **creating**.                                                                       |
| `updateRules`   | Rules that should be applied when **updating**.                                                                       |
