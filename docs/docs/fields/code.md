# Code Editor

A code editor using [Codemirror](https://codemirror.net/).

```php
$form->code('code')
    ->title('Google Analytics Id')
    ->hint('Insert your google analytics tag.')
    ->width(6);
```

## Methods

| Method          | Description                                                                      |
| --------------- | -------------------------------------------------------------------------------- |
| `title`         | The title description for this form field.                                       |
| `hint`          | A short hint that should describe how to use the form field.                     |
| `width`         | Width of the field.                                                              |
| `theme`         | Codemirror [theme](https://codemirror.net/demo/theme.html). (default: "default") |
| `language`      | Codemirrot [languages](https://codemirror.net/mode/). (default: "text/html")     |
| `tabSize`       | Number of spaces used for a tab. (default: 4)                                    |
| `line_numbers`  | Should the line numbers be visible. (default: true) field.                       |
| `rules`         | Rules that should be applied when **updating** and **creating**.                 |
| `creationRules` | Rules that should be applied when **creating**.                                  |
| `updateRules`   | Rules that should be applied when **updating**.                                  |
