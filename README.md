# Sura
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

![Sura](https://raw.githubusercontent.com/Sura/sura/master/images/app.gif)

<a name="installation"></a>

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Sura.

```bash
$ composer create-project suralabs/sura:^0.0.1 MYPROJECT --prefer-dist
```

Copy config files

```bash
$ cp system/data/config.php.example system/data/config.php
```

```bash
$ cp system/data/db_config.php.example system/data/db_config.php
```

The database configuration is located in system/data/db_config.php

To create a migration, use the -migrate

```bash
$ php sura -migrate
```

To create a user admin, use the -make:add-user <name> <lastname> <mail> <pass>

```bash
$ php sura -make:add-user Ivan Petrov petrov@example.com password
```


### Server Requirements

Sura has a few system requirements.

However, if you are not using Homestead, you will need to make sure your server meets the following requirements:

- PHP ^8.2
- ICONV PHP Extension
- GD PHP extension
- MySQLI PHP Extension
- Zlib PHP Extension
- Curl PHP Extension
- PDO PHP Extension

## License

This software is distributed as open source with the [MIT](https://github.com/suralabs/sura/blob/master/LICENSE)
license.
## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center"><a href="https://github.com/phakof"><img src="https://avatars.githubusercontent.com/u/62615948?v=4?s=100" width="100px;" alt="phakof"/><br /><sub><b>phakof</b></sub></a><br /><a href="#infra-phakof" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="https://github.com/Sura-framework/sura/commits?author=phakof" title="Tests">⚠️</a> <a href="https://github.com/Sura-framework/sura/commits?author=phakof" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!