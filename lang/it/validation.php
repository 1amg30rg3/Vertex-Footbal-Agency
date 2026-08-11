<?php

return [
    'required' => 'Il campo :attribute è obbligatorio.',
    'email' => ':attribute deve essere un indirizzo e-mail valido.',
    'min' => ['string' => ':attribute deve contenere almeno :min caratteri.'],
    'max' => ['string' => ':attribute non può superare i :max caratteri.'],
    'string' => ':attribute deve essere un testo.',
    'prohibited' => 'Il campo :attribute non è consentito.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'subject' => 'oggetto',
        'message' => 'messaggio',
    ],
];
