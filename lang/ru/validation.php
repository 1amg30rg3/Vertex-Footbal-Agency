<?php

return [
    'required' => 'Поле «:attribute» обязательно для заполнения.',
    'email' => 'Поле «:attribute» должно содержать корректный адрес эл. почты.',
    'min' => ['string' => 'Поле «:attribute» должно содержать не менее :min символов.'],
    'max' => ['string' => 'Поле «:attribute» не должно превышать :max символов.'],
    'string' => 'Поле «:attribute» должно быть строкой.',
    'prohibited' => 'Поле «:attribute» недопустимо.',

    'attributes' => [
        'name' => 'имя',
        'email' => 'эл. почта',
        'subject' => 'тема',
        'message' => 'сообщение',
    ],
];
