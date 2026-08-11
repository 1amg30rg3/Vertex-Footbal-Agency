<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => ':attribute debe ser una dirección de correo válida.',
    'min' => ['string' => ':attribute debe tener al menos :min caracteres.'],
    'max' => ['string' => ':attribute no debe superar los :max caracteres.'],
    'string' => ':attribute debe ser texto.',
    'prohibited' => 'El campo :attribute está prohibido.',

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'subject' => 'asunto',
        'message' => 'mensaje',
    ],
];
