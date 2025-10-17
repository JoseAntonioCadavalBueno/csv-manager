<?php

return [
    'errors'    => [
        'illegal_env'       => 'La configuration de l’environnement n’est pas autorisée.',
        'not_found'         => 'Le fichier n’existe pas ou n’est pas accessible.',
        'not_found_2'       => 'Le répertoire n’existe pas ou n’est pas accessible.',
        'corrupt'           => 'Le fichier ne peut pas être lu correctement ou n’est pas correctement formaté.',
        'corrupt_2'         => 'L’extension de fichier n’est pas autorisée.',
        'overflow'          => 'Le fichier csv est trop volumineux.',
        'native-logic'      => 'Avec la configuration php native, le chemin doit être complet en $filename.',
        'symfony_logic'     => 'Avec la configuration Symfony PHP, le disque ne peut pas être accepté.',
        'same_csv_chars'    => "Les paramètres 'delimiter', 'enclosure' et 'escape' doivent être différents.",
        'invalid_csv_Char'  => "Le caractère '%s' n’est pas autorisé."
    ],
    'messages'  => []
];
