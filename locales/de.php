<?php

return [
    'errors'    => [
        'illegal_env'       => 'Die Umgebungs­konfiguration ist nicht erlaubt.',
        'not_found'         => 'Die Datei ist nicht vorhanden oder es kann nicht darauf zugegriffen werden.',
        'not_found_2'       => 'Das Verzeichnis existiert nicht oder ist nicht zugänglich.',
        'corrupt'           => 'Die Datei kann nicht korrekt gelesen werden oder ist nicht richtig formatiert.',
        'corrupt_2'         => 'Dateiendung nicht erlaubt.',
        'overflow'          => 'Die CSV-Datei ist zu groß.',
        'native_logic'      => 'Bei der nativen PHP-Konfiguration muss der Pfad in $filename vollständig sein.',
        'symfony_logic'     => 'Mit der Symfony-PHP-Konfiguration kann das Laufwerk nicht akzeptiert werden.',
        'same_csv_chars'    => "Die Parameter 'delimiter', 'enclosure' und 'escape' müssen unterschiedlich sein.",
        'invalid_csv_Char'  => "Das Zeichen '%s' ist nicht erlaubt."
    ],
    'messages'  => []
];
