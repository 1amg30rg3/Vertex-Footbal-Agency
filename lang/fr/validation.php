<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => ':attribute doit être une adresse e-mail valide.',
    'min' => ['string' => ':attribute doit contenir au moins :min caractères.'],
    'max' => ['string' => ':attribute ne peut pas dépasser :max caractères.'],
    'string' => ':attribute doit être une chaîne de caractères.',
    'prohibited' => 'Le champ :attribute est interdit.',

    'attributes' => [
        'name' => 'nom',
        'email' => 'e-mail',
        'subject' => 'objet',
        'message' => 'message',
    ],
];
