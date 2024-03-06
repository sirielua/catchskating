
## Bot URL

https://t.me/CatchSkatingMatchmakerBot

## Bot Commands

```sh
# Start the bot in long polling mode. Useful in development mode.
$ php artisan nutgram:run

# List all registered handlers
$ php artisan nutgram:list

# Get current webhook status
$ php artisan nutgram:hook:info

# Remove the bot webhook
$ php artisan nutgram:hook:remove {--d|drop-pending-updates}

# Set the bot webhook
$ php artisan nutgram:hook:set {url}

# Register the bot commands, see automatically register bot commands
$ php artisan nutgram:register-commands

# Start the bot in long polling mode. Useful in development mode.
$ php artisan nutgram:run

# Create a new command class, see Commands
$ php artisan nutgram:make:command {name}

# Create a new conversation class, see Conversations
$ php artisan nutgram:make:conversation {name} {--menu}

# Create a new handler class, see Handlers
$ php artisan nutgram:make:handler {name}

# Create a new middleware class, see Middleware
$ php artisan nutgram:make:middleware {name}

# Create a new ApiException class, see Register API exceptions
$ php artisan nutgram:make:exception {name}

# Generate a file helping IDEs to autocomplete mixins methods.
$ php artisan nutgram:ide:generate

# Log out from the cloud Bot API server
$ php artisan nutgram:logout {--d|drop-pending-updates}
```
