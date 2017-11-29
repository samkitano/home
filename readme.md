# Local projects manager

## Work in progress! **STATUS:** MINIMUM FEATURES NOT IMPLEMENTED YET

## GUI for localhost projects management built with [Laravel](https://laravel.com/), [Twig](https://twig.symfony.com/) and [Vue](https://vuejs.org/)

    - Project Listing
    - Project creation
    - Permissions check

### Creation

    - Laravel
    - Vuejs
    - Node

GUI provides a pseudo-console to output info about project creation progress.
Generated output can be set to a minimum (silent) or maximum (verbose).

Vue-Cli is emulated by simply converting Handlebar mustaches into [Twig](https://twig.symfony.com/) syntax.
Available options for the GUI is provided by meta data on vue templates.


## Requirements

    - PHP >=7.0.0
    - Composer (globally)
    - Node.js >=6.0.0
    - Npm >=5.5.1
    - Vue Cli (to be removed, eventually)
    - [Pusher](https://pusher.com/) account
    - IMPORTANT: /Library/Webserver permissions must be set to ALL users (Mac)

### .env

ENTRY | DESCRIPTION | EXAMPLE
------|-------------|--------------
DEFAULT_AUTHOR | name and email | " John Doe &lt;doe&#64;example.com&gt;"
DEFAULT_LICENSE | default project license | MIT
DEFAULT_VERSION | default version for new projects | 1.0.0
SITES_DIR | path to local projects | "/Users/doe/www/sites"
LOCAL_USER | local username | doe
COMPOSER_HOME | path to composer cache | "/Users/doe/.composer"
COMPOSER_LOCATION | path tocomposer executable | "/usr/local/bin/composer.phar"
NPM_LOCATION | path to npm executable | "/usr/local/bin/npm"
VUE_LOCATION | path to vue-cli executable | "/usr/local/bin/vue"
VUE_CLI_PACKAGE_JSON | full path to vue-cli package file | "/usr/local/lib/node_modules/vue-cli/package.json"
VUE_TEMPLATES | path to downloaded templates. **Must be within Public dir** | "downloads/vuejs-templates"
