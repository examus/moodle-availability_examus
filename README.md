# EXAMUS MOODLE PLUGIN

## Requirements
1. Examus plugin was tested with Moodle 3.3.1 and 3.3.2 version.
2. Examus proctoring needs an OAuth2 server plugin installed.
    * You can download the plugin from https://github.com/examus/moodle-local_oauth/releases (ZIP archive). To install it, login to your Moodle site as an admin, go to `Site administration → Plugins → Install plugins`, upload the ZIP file and install the plugin as prompted.
    * If you have an OAuth2 server plugin installed from another source, **please make sure it allows HTTPS url as Redirect URL**.

## Installation and integration

### Plugin installation
Download ZIP file from https://github.com/examus/moodle-availability_examus/releases/, login to your Moodle site as an admin, open `Site administration → Plugins → Install plugins`, upload the ZIP file and install it.

### Setting up a user for the integration
Due to security reasons, it's recommended to have a separate user with special permissions for the integration.
1. Create a new user, for example, `examus`. To do that, go to `Site administration → Users → Accounts → Add a new user`. Fill the required fields. Create a password for the user.
2. Create a new role for the integration, for example, `webservice`. Go to `Site administration → Users → Permissions → Define roles`. Click the `Add a new role` button. Choose a role archetype `Authenticated user` as a basis. Click `Continue`.
3. Select the context `System` where this role can be assigned.
In the bottom part of the page, allow the following capabilities:
    - local/rcommon:authenticate ???
    - Use REST protocol  
Fill in all other required fields: `Short name`, `Custom full name`.
Click the `Create this role` button.
4. Go to `Site administration → Users → Permissions → Assign system roles`. Choose the role created in the previous step (`webservice`). Add the previously created `examus` user to the `Existing users` field.

### Enabling web services
1. Go to `Site administration → Advanced features`. Enable web services. Save changes.
2. Go to `Site administration → Plugins → Web services → Manage protocols`. Enable the REST protocol. Save changes.

### Creating a web service token
1. Go to `Site administration → Plugins → Web services → Manage tokens`, click `Add`.
2. Add a token for the service `Examus` for the previously created user `examus` (or Admin User). Leave the IP restriction field empty.
3. Send the token to Examus. We will use this token for integration.

### Creating a new OAuth client
The new client will be used by Examus proctoring service to authenticate your users.
1. Please contact us to get your 'Redirect URL'.
2. Go to `Site administration → Server → OAuth provider settings`, click `Add new client`. You can use any `Client identifier`, for example, `examus`. Paste the received 'Redirect URL'. Save changes.
3. Sent the `Client identifier` and `Client secret` values to Examus. We will use them for integration.

## Usage

### Setting a restriction for a module
1. In course editing mode, choose `Edit settings` for the module (quiz) you want to use with Examus proctoring. Scroll down to `Restrict access`.
2. Choose `Add restrictions... → Examus` to enable proctoring for this module.
3. Specify the duration of the proctoring session. If you already have a time restriction for the module (quiz), the proctoring session duration must be equal to the time restriction setting.
4. Choose the proctoring mode.
5. Choose the rules for the proctoring session.

### Adding a new entry
If the student attempted the module once, for every following attempt a new Examus entry must be created in the following way.
1. Login as an admin. Go to `Site administration → Reports → Examus settings`.
2. Find the exam you want to allow a new attempt for. Click the button `New entry`.
