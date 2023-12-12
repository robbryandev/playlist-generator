# [Playlist Generator](https://playlists.robbryan.dev)

A spotify playlist generator

## Technologies used
- HTML
- CSS
- Tailwind
- Javascript
- Typescript
- Vue
- PHP
- Laravel
- Mysql

## Contributers
- Robert Bryan (lead)
- Lucas Mollerstuen (Helped debug the authentication flow)

## Requirements
- PHP 8.2
- Node.js >= 18

## Getting started
First, configure your environment variables.
### Spotify Auth

[Create a spotify developer application with access to the web api](https://developer.spotify.com/dashboard)
<br>then add 2 redirect urls following the example below

http://{YOUR_HOST}/api/auth/callback
http://{YOUR_HOST}/api/auth/permission/callback
```
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
SPOTIFY_REDIRECT_URI=http://{YOUR_HOST}/api/auth/callback
SPOTIFY_ACCESS_REDIRECT=http://{YOUR_HOST}/api/auth/permission/callback
```

### DB
setup a mysql server then fill in the db environment variables
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=playlist_generator
DB_USERNAME=root
DB_PASSWORD=__CHANGE_ME__
```

## Running
Install the dependencies
```bash
composer install
npm i
```

Initialize database
<br>NOTE: the --force is only needed to run the initial migration in production mode.
```bash
php artisan migrate --force
```

Build the production assets
```bash
npm run build
```

and serve
```bash
php artisan serve --host=0.0.0.0 --port=8000
```