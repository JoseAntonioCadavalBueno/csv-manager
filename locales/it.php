<?php

return [
    'errors'    => [
        'illegal_env'       => 'La configurazione dell’ambiente non è consentita.',
        'not_found'         => 'Il file non esiste o non è accessibile.',
        'not_found_2'       => 'La directory non esiste o non è accessibile.',
        'corrupt'           => 'Il file non può essere letto correttamente o non è formattato correttamente.',
        'corrupt_2'         => 'Estensione del file non consentita.',
        'overflow'          => 'Il file csv è troppo grande.',
        'native-logic'      => 'Con la configurazione php nativa il percorso deve essere completo in $filename.',
        'symfony_logic'     => 'Con la configurazione Symfony PHP il disco non può essere accettato.',
        'same_csv_chars'    => "I parametri 'delimiter', 'enclosure' e 'escape' devono essere diversi.",
        'invalid_csv_Char'  => "Il carattere '%s' non è consentito."
    ],
    'messages'  => []
];
