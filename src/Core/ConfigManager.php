<?php

namespace CsvManager\Core;

@trigger_error(
    'Using CsvManager\Core\ConfigManager is deprecated. Use CsvMananger\Core\Config instead.',
    E_USER_DEPRECATED
);
class ConfigManager extends Config
{
}