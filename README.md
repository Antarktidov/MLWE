
# MLWE
> [!WARNING]  
> This README is outdated. Some instructions given in this document will not work. Some features are not documented.

A PHP Laravel 11 Wiki Engine with comments.

![Article Screenshot](https://github.com/Antarktidov/MyLaravelWikiEngine/blob/master/images/article%20screenshot%20with%20comments.png?raw=true)

## Requirements
* PHP 8.2
* database
* git
* composer

## Installation
clone repository via git and install laravel via composer:
```bash
  git clone https://github.com/Antarktidov/MyLaravelWikiEngine.git
  cd MyLaravelWikiEngine
  composer install
```
Once the project is installed configure it as [any other Laravel app](https://laravel.com/docs/11.x/installation#initial-configuration).
```bash
  $EDITOR .env
  $EDITOR config/app.php
  php artisan migrate --seed
```
Then run http-server via:
```bash
  php artisan serve
```
go to url `<path to your wiki>/home`
and create your account.
Than you should grant you steward (global superadmin) rights via database editor.
Add the following row in user_user_group_wiki:
| id | user_id |user_group_id|wiki_id|
| ------ | ----------- | ----------- |----------- |
| <Your_row_id. If it's first row, it should be 1>|<Your account id. If you are first user, it's should be 1>|1|0|

Now you can open your wiki in browser and start explore it.

## Useful urls
* `<path to your wiki>/home` — Registration and Login
* `<path to your wiki>` — list of all wikis
* `<path to your wiki>/closed-wikis` — list of all closed (deleted) wikis
* `<path to your wiki>/create-wiki` — create wiki page
* `<path to your wiki>/global-user-rights/<user id>` — global user rights manage page
* `<path to your wiki>/wiki/<wiki url>/all-articles` — list of all articles on your wiki
* `<path to your wiki>/wiki/<wiki url>/create-article` — create article page
* `<path to your wiki>/wiki/<wiki url>/trash` — list of all deleted articles on your wiki (need steward or admin rights)
* `<path to your wiki>/wiki/<wiki url>/user-rights/<user id>` — local user rights manage page
