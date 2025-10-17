<?php

return [
    'errors'    => [
        'illegal_env'       => '環境設定は許可されていません。',
        'not_found'         => 'ファイルは存在しないか、アクセスできません。',
        'not_found_2'       => 'ディレクトリが存在しないか、アクセスできません。',
        'corrupt'           => 'ファイルは正しく読み取れないか、適切な形式ではありません。',
        'corrupt_2'         => '許可されていないファイル拡張子です。',
        'overflow'          => 'CSVファイルは大きすぎます。',
        'logic'             => 'phpのネイティブ設定では、$filenameのパスは完全でなければなりません。',
        'symfony_logic'     => 'Symfony PHP の構成ではディスクを受け付けることができません。',
        'same_csv_chars'    => "「delimiter」「enclosure」「escape」はそれぞれ異なる必要があります。",
        'invalid_csv_Char'  => "「%s」は許可されていない文字です。"
    ],
    'messages'  => []
];
