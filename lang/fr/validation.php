<?php

return [
    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => "Le champ :attribute n'est pas une URL valide.",
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, chiffres et tirets.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être une liste.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => "Le champ :attribute n'est pas une date valide.",
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'email' => "L'adresse e-mail n'est pas valide.",
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'ip' => "Le champ :attribute doit être une adresse IP valide.",
    'lowercase' => 'Le champ :attribute doit être en minuscules.',
    'max' => [
        'numeric' => 'Le champ :attribute ne peut pas dépasser :max.',
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
        'array' => 'Le champ :attribute ne peut pas contenir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
    ],
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => [
        'letters' => 'Le mot de passe doit contenir au moins une lettre.',
        'mixed' => 'Le mot de passe doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
        'symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        'uncompromised' => 'Ce mot de passe est apparu dans une fuite de données. Choisis-en un autre.',
    ],
    'required' => 'Le champ :attribute est obligatoire.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'string' => 'Le champ :attribute doit être du texte.',
    'unique' => 'Cette valeur pour :attribute est déjà utilisée.',
    'uploaded' => "Le fichier :attribute n'a pas pu être téléversé.",
    'url' => "Le champ :attribute doit être une URL valide.",

    // Messages personnalisés par champ (les plus clairs pour l'utilisateur).
    'custom' => [
        'email' => [
            'unique' => 'Cette adresse e-mail est déjà utilisée. Connecte-toi ou utilise « Mot de passe oublié ».',
            'email' => "L'adresse e-mail n'est pas valide.",
            'required' => "L'adresse e-mail est obligatoire.",
        ],
        'password' => [
            'confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'min' => 'Le mot de passe doit contenir au moins :min caractères.',
        ],
        'phone' => [
            'required' => 'Le numéro de téléphone est obligatoire.',
        ],
        'name' => [
            'required' => 'Le nom complet est obligatoire.',
        ],
        'id_country' => [
            'required' => 'Choisis ton pays.',
            'exists' => 'Le pays choisi est invalide.',
        ],
        'sexe' => [
            'required' => 'Choisis ton sexe.',
        ],
    ],

    // Noms « lisibles » des champs.
    'attributes' => [
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'name' => 'nom complet',
        'phone' => 'téléphone',
        'sexe' => 'sexe',
        'id_country' => 'pays',
        'ville' => 'ville',
        'quartier' => 'quartier',
        'sujet' => 'sujet',
        'contenu' => 'message',
    ],
];
