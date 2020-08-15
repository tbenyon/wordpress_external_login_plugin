<?php

/** EXLOG - Whether the plugin code is activated or not */
define('EXTERNAL_LOGIN_OPTION_ENABLE_EXTERNAL_LOGIN', getenv('EXTERNAL_LOGIN_OPTION_ENABLE_EXTERNAL_LOGIN') ? getenv('EXTERNAL_LOGIN_OPTION_ENABLE_EXTERNAL_LOGIN') : 'on');

/** EXLOG - The External Database Name */
define('EXTERNAL_LOGIN_OPTION_DB_NAME', getenv('EXTERNAL_LOGIN_OPTION_DB_NAME') ? getenv('EXTERNAL_LOGIN_OPTION_DB_NAME') : 'externalDb');

/** EXLOG - The External Database Host */
define('EXTERNAL_LOGIN_OPTION_DB_HOST', getenv('EXTERNAL_LOGIN_OPTION_DB_HOST') ? getenv('EXTERNAL_LOGIN_OPTION_DB_HOST') : 'externalDbMySql5.7');

/** EXLOG - The External Database Port */
define('EXTERNAL_LOGIN_OPTION_DB_PORT', getenv('EXTERNAL_LOGIN_OPTION_DB_PORT') ? getenv('EXTERNAL_LOGIN_OPTION_DB_PORT') : '3306');

/** EXLOG - The External Database Username */
define('EXTERNAL_LOGIN_OPTION_DB_USERNAME', getenv('EXTERNAL_LOGIN_OPTION_DB_USERNAME') ? getenv('EXTERNAL_LOGIN_OPTION_DB_USERNAME') : 'externalDbUser');

/** EXLOG - The External Database Password */
define('EXTERNAL_LOGIN_OPTION_DB_PASSWORD', getenv('EXTERNAL_LOGIN_OPTION_DB_PASSWORD') ? getenv('EXTERNAL_LOGIN_OPTION_DB_PASSWORD') : 'externalDbPassword');

/** EXLOG - Hash Type */
define('EXTERNAL_LOGIN_OPTION_HASH_ALGORITHM', getenv('EXTERNAL_LOGIN_OPTION_HASH_ALGORITHM') ? getenv('EXTERNAL_LOGIN_OPTION_HASH_ALGORITHM') : 'bcrypt');

/** EXLOG - Disable Local Login */
define('EXTERNAL_LOGIN_OPTION_DISABLE_LOCAL_LOGIN', getenv('EXTERNAL_LOGIN_OPTION_DISABLE_LOCAL_LOGIN') ? getenv('EXTERNAL_LOGIN_OPTION_DISABLE_LOCAL_LOGIN') : 'on');

/** EXLOG - Salting Method */
define('EXTERNAL_LOGIN_OPTION_DB_SALTING_METHOD', getenv('EXTERNAL_LOGIN_OPTION_DB_SALTING_METHOD'));

/** EXLOG - Salt Location */
define('EXTERNAL_LOGIN_OPTION_DB_SALT_LOCATION', getenv('EXTERNAL_LOGIN_OPTION_DB_SALT_LOCATION'));

/** EXLOG - Password Salt */
define('EXTERNAL_LOGIN_OPTION_DB_SALT', getenv('EXTERNAL_LOGIN_OPTION_DB_SALT'));

/** EXLOG - Database Type */
define('EXTERNAL_LOGIN_OPTION_DB_TYPE', getenv('EXTERNAL_LOGIN_OPTION_DB_TYPE') ? getenv('EXTERNAL_LOGIN_OPTION_DB_TYPE') : 'mysql');

/** EXLOG - Database Structure Fields */
define('EXLOG_DBSTRUCTURE_USERNAME', getenv('EXLOG_DBSTRUCTURE_USERNAME') ? getenv('EXLOG_DBSTRUCTURE_USERNAME') : 'NickName');
define('EXLOG_DBSTRUCTURE_PASSWORD', getenv('EXLOG_DBSTRUCTURE_PASSWORD') ? getenv('EXLOG_DBSTRUCTURE_PASSWORD') : 'Hash');
define('EXLOG_DBSTRUCTURE_TABLE', getenv('EXLOG_DBSTRUCTURE_TABLE') ? getenv('EXLOG_DBSTRUCTURE_TABLE') : 'User');
define('EXLOG_DBSTRUCTURE_ROLE', getenv('EXLOG_DBSTRUCTURE_ROLE') ? getenv('EXLOG_DBSTRUCTURE_ROLE') : 'UserType');
define('EXLOG_DBSTRUCTURE_EMAIL', getenv('EXLOG_DBSTRUCTURE_EMAIL') ? getenv('EXLOG_DBSTRUCTURE_EMAIL') : 'Email');
define('EXLOG_DBSTRUCTURE_LAST_NAME', getenv('EXLOG_DBSTRUCTURE_LAST_NAME') ? getenv('EXLOG_DBSTRUCTURE_LAST_NAME') : 'LastName');
define('EXLOG_DBSTRUCTURE_FIRST_NAME', getenv('EXLOG_DBSTRUCTURE_FIRST_NAME') ? getenv('EXLOG_DBSTRUCTURE_FIRST_NAME') : 'FirstName');
