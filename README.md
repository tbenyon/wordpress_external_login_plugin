# External Login

See readme.txt for the main information for the plugin.
This is done to save duplication as readme.txt is required for WordPress plugin repository.

## Development

### Getting started
1) Install docker on your machine
1) Change directory into this repo
1) Run `npm i` from the plugin directory
1) Run `npm run up` which will create the required docker images
1) Navigate to the [admin area](localhost:8000/wp-admin)
1) Setup WordPress generating a new admin username and password
1) Navigate to the plugins section of the WordPress admin area
1) Activate External Login plugin
1) Add appropriate settings by either:
    - Navigating to the [settings page](http://localhost:8000/wp-admin/options-general.php?page=external-login) to add the required settings
    - Running `npm run settingsDefault`


For front end edits to the options menu, gulp is used to compile css and auto-refresh the browser as changes are made:
1) Run `npm run frontend`

### Checking the plugin works
1) Navigate to the [settings page](http://localhost:8000/wp-admin/options-general.php?page=external-login)
1) Click the "Test Connection" button
1) You should see a pop up with some example data
1) Check the `Enable External Login` checkbox and click the `Save Changes` button below to activate the plugin flow.
1) Logout and try logging in with the following details:
    - Username: `tom`
    - Password: `externalPassword`
1) You should get logged in as the user "Thomas Benyon"
1) For further development, logging back in as your admin user is advised to gain greater access to permissions

### Modifying settings
Modifying settings can be done in three different ways:

#### 1) Through constants setup in wp-config.php
A default example of this is setup and moved into the WordPress container (`wordpress/external-login-settings.php`).
From looking at the file you can see that environment variables can also be used to modify these settings.
*IMPORTANT* - Including this file is currently commented out in `wordpress/wp-config.php` near the top in favour of using the database for development to assist testing.
Including the example file and manipulating its settings can be done but will require the container to be restarted for changed to take effect (`npm run restart`).

#### 2)  Through the CMS settings interface for the plugin
Navigate to the [settings page](http://localhost:8000/wp-admin/options-general.php?page=external-login) to add the required settings

#### 3)  Using the settings builder to manipulate the database stored settings
In `utils/SettingsBuilder.php` there are a handful of utilities for generating the desired settings.
There is a wrapper to generate the default development settings in the form of a NPM script `npm run settingsDefault`

It is important to note that the settings in `wp-config.php` take precedence and will block the CMS options from being editable if they exist. 

### Accessing Databases outside of the containers
For development a GUI can be useful for editing the databases content
The relevant ports have been exposed to help achieve this. 

For the details required to connect see `docker-compose.yml` but below are some examples:

#### The WordPress Database
- host: `127.0.0.1` 
- port: `3330` 
- username: `wordpress` 
- password: `wordpress` 
- database: `wordpress` 

#### The MySQL External Database
- host: `127.0.0.1` 
- port: `3331` 
- username: `externalDbUser` 
- password: `externalDbPassword` 
- database: `externalDb` 

## Hooks

### Action: exlog_hook_action_authenticated
This hook is run after the user has been authenticated from the external database.

This will not run if the user is authenticated from the local WordPress database.

Below is an example of code that could be added to your `functions.php` file to add additional fields from the users 
table on the external database as user meta data in WordPress.

Please note the use of the third parameter $rawResponse that returns all fields from the users table in the external 
database.
```
/**
 * Example function to do something after External Login has authenticated a user
 */
function exlog_add_additional_user_data($wp_user, $exlog_user_data, $rawResponse) {
    update_user_meta(
        $wp_user->ID,                       // User ID
        'someKeyForUserMetaData',           // WP Meta field key
        $rawResponse['someExternalField'],  // External table data
        false                               // Not unique
    );
}

add_action('exlog_hook_action_authenticated', 'exlog_add_additional_user_data', 10, 3);
```

Below is a different example that deletes a user from the external database after they have logged in for the first time.
```
/**
 * Example function to do something after External Login has authenticated a user
 *
 * In this case we are deleting the user from the external database
 *
 * WP User Object $wp_user The WordPress user object for the authenticated user.
 *
 * Array $exlog_user_data An associative array of user data generated when attempting to authenticate the user
 */
function my_function_to_do_something_after_authentication($wp_user, $exlog_user_data) {
  // Uses the data provided to the plugin to create the database object and data required for a query
  $db_data = exlog_get_external_db_instance_and_fields('mysql');

  // A query of your choice
  $rows = $db_data["db_instance"]->delete(
    esc_sql($db_data["dbstructure_table"]),
    array( esc_sql($db_data["dbstructure_username"]) => esc_sql($exlog_user_data['user_login']) )
  );

  // Checking if the user was deleted
  if ($rows) {
    error_log('User Successfully deleted from external database');
  } else {
    error_log('Unable to delete user from external database');
  }
}

add_action('exlog_hook_action_authenticated', 'my_function_to_do_something_after_authentication', 10, 2);
```

### Filter: exlog_hook_filter_authenticate_hash
The user can use this hook to check if the password is correct in a custom way. For example, if they use a hashing algorithm not supported by the plugin by default.

This hook provides the user with a range of different information:
- `$password` - the password that was typed in at the login screen 
- `$hashFromDatabase` - the hash stored in the database
- `$username` - the username that was typed in in the login screen
- `$externalUserData` - the rest of the data retrieved from the external database for the user that was found


Returning `true` will authenticate the user and returning `false` will treat them as unauthorised.

The below example shows how you could use the filter.

```
function myExlogHashAuthenticator($password, $hashFromDatabase, $username, $externalUserData) {
    return password_verify($password, $hashFromDatabase);
}
add_filter('exlog_hook_filter_authenticate_hash', 'myExlogHashAuthenticator', 10, 4);
```

### Filter: exlog_hook_filter_assign_roles
This hook is used to add additional roles to a user with custom logic and potentially custom queries.

This hook provides the user with a range of different information:
- `$roles` - the array of roles already mapped using the built in logic based on data set it the admin panel
- `$username` - the username that was typed in the login screen
- `$userData` - the data that was originally queried for the user

It is expected that you will return an array of roles.

The below example assumes that you want to use the roles derived from the built in logic and add to it.
For this reason new roles are added onto the passed array and that is returned.
If you want to ignore what the logic currently provides you could simply pass a new array.

The below is a relatively complex example that shows how you could write your own query to fetch data and use that to add WordPress roles.
```
function myExlogRolesMapper($roles, $username, $userData) {
    // Uses the data provided to the plugin to create the database object and data required for a query
    $db_data = exlog_get_external_db_instance_and_fields();

    // Start building a query to fetch the user
    // This is the first bit you will want to modify to select data from your additional table where the role is being stored
    $query_string =
        'SELECT *' .
        // This is specifying the table specified in the settings panel, you can hard code these if you rather
        ' FROM ' . esc_sql($db_data["dbstructure_table"]) .
        // This finds the correct user based on the username field set in the settings and the username that they typed in
        ' WHERE (' . esc_sql($db_data["dbstructure_username"]) . '="' . esc_sql($username) . '"';

    if ($db_data["dbstructure_email"]) {
        // Because the username they type in can be an e-mail, if you have set an e-mail field in the settings panel we will also try and find the user by e-mail
        $query_string .= ' OR ' . esc_sql($db_data["dbstructure_email"]) . '="' . esc_sql($username) . '")';
    } else {
        $query_string .= ')';
    }

    // Use the above computed query actually fetch the data
    $rows = $db_data["db_instance"]->get_results($query_string, ARRAY_A);

    // Checking if a user was found
    if ($rows && count($rows) > 0) {
        $foundData = $rows[0];
        // If the custom field in your database called 'myCustomRoleField' has 'editingKing' stored in it
        if ($foundData['myCustomRoleField'] == 'editingKing') {
            // Add the wordpress role 'editor' to the user
            array_push($roles, "editor");
        }
    }

    // return the array of roles as WordPress supports multiple roles in the backend even though their settings pane only shows one
    return $roles;
}
add_filter('exlog_hook_filter_assign_roles', 'myExlogRolesMapper', 10, 3);
```

## Special Thanks
A special thank you to Ben Lobaugh for a [great article](https://ben.lobaugh.net/blog/7175/wordpress-replace-built-in-user-authentication) which I used heavily for this plugin.

## Deploy to WordPress
This is a note to self. This process and code needs integrating into the plugin itself.
A copy of the deploy script is in the repo for reference but needs running from a directory above it (see step 6).

1) Modify the version number in external-login.php
1) Modify the version number in package.json
1) Modify readme.txt "Tested up to" for the most recent WordPress version
1) Modify readme.txt "Stable tag" (Current version)
1) Modify readme.txt == Changelog ==
1) Modify readme.txt == Upgrade Notice ==
1) Tag git commit with respective version number
1) In repo directory execute `./deploy.sh`
