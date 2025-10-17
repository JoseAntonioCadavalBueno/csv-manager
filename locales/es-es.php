<?php

return [
    'errors'    => [
        'illegal_env'       => 'La configuración del entorno no es válida.',
        'not_found'         => 'El fichero no existe o no es accesible.',
        'not_found_2'       => 'El directorio no existe o no es accesible',
        'corrupt'           => 'El fichero no se puede leer correctamente o no tiene el formato adecuado.',
        'corrupt_2'         => 'Extensión de archivo no permitida.',
        'overflow'          => 'El fichero csv es demasiado grande.',
        'native-logic'      => 'Con la configuración nativa de php la ruta debe ser completa en $filename.',
        'symfony_logic'     => 'En la configuración para symfony de php el atributo disk no está permitido.',
        'same_csv_chars'    => "'delimiter', 'enclosure' y 'escape' deben ser distintos.",
        'invalid_csv_Char'  => "El '%s' no es un carácter permitido."
    ],
    'messages'  => []
];
