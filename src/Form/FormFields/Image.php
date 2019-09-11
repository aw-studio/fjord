<?php

namespace AwStudio\Fjord\Form\FormFields;

class Image
{
    const TRANSLATABLE = true;

    // TODO: 'type' => 'string' / 'type' => ['string', 'array']
    const REQUIRED = [
        'type',
        'id'
    ];

    const DEFAULTS = [
        'maxFiles' => 1,
        'crop' => false,
        'ratio' => 4/3
    ];
}
