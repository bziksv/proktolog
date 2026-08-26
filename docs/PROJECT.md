# proktolog.su — документация проекта

Bitrix-интернет-магазин проктологического оборудования и медтехники (поставки по РФ).

Шаблон: **medical-templates**. Компоненты: **nbrains**, **niges**. Модули: **prime.alerts** (`local/`), **prime.cleaner**, **prime.roistatbitrixcms**, **prime.updateprice**, **artrix.imageoptimizer**, **arturgolubev.***, **isaev.seotemplate**, **kda.exportexcel**, **delight.webpconverter**, **imyie.orderminprice**, **asd.iblock**, **niges.cookiesaccept**, **ismagin.filecleaner**, **sng.secure**.

## Репозиторий и окружения

| | |
|---|---|
| GitHub | https://github.com/bziksv/proktolog |
| **Git root** | `proktolog.su/` (= корень сайта на prod) |
| Prod IP | `45.90.35.63` |
| Prod path | `/var/www/proktolog.su/data/www/proktolog.su` |
| Домен | https://proktolog.su |
| Локально | http://127.0.0.1:8102/ |

Родительская папка `proktolog/` — обёртка Mac: дамп БД (`proktolog_su_db.sql`), `.cursorignore`, корневой `README.md`.

## Структура

```
proktolog/                       # workspace (Mac)
├── proktolog_su_db.sql          # дамп БД (не в git)
├── .cursorignore
├── README.md
└── proktolog.su/                # корень сайта (= git root)
    ├── .local/                  # nginx/php-fpm (Mac, soft)
    ├── docs/                    # документация
    ├── scripts/                 # dev
    ├── bitrix/
    │   ├── modules/             # ядро + сторонние/кастомные
    │   ├── components/{nbrains,niges}/
    │   ├── templates/medical-templates/
    │   └── php_interface/       # dbconn, init.php
    ├── local/modules/prime.alerts/
    ├── catalog/ personal/ about/ …
    └── upload/                  # медиа (не в git)
```

## База данных

| Параметр | Prod (из дампа/конфига) | Локально |
|----------|-------------------------|----------|
| Host | `localhost` | `127.0.0.1` |
| Database | `proktolog_su_db` | `proktolog_su_db` |
| User | `proktolog_su_usr` | `proktolog_local` |
| Password | (prod) | `proktolog_local` |

Дамп: MariaDB 10.3 → импорт в Homebrew MySQL 8.0 (`/tmp/mysql.sock`).

Инфоблок каталога: `IBLOCK_CATALOG = 33` (`bitrix/php_interface/init.php`).

Сайт в БД: `LID=s1`, `SERVER_NAME=proktolog.su`.

## Локальная разработка (Mac, soft)

Порты: **8102** (nginx), **9102** (php-fpm). MySQL 3306 (Homebrew).

```bash
cd proktolog.su
cp .local/db.env.example .local/db.env   # один раз
./scripts/setup-local-db.sh --background # один раз, щадящий импорт
./scripts/start-dev.sh
./scripts/stop-dev.sh
```

Soft-режим:

- php-fpm `ondemand`, max **2** workers
- `memory_limit` 512M, opcache 64M
- импорт дампа в фоне (`--background`), без параллельной нагрузки

`apply-local-db-config.sh` пишет `dbconn.local.php` и правит `.settings.php` под локальные креды; prod-копии кладёт в `.local/backup/`.

### Занятые порты (соседние проекты)

| Порт | Проект |
|------|--------|
| 8098 | lorshop |
| 8099 | (занят) |
| 8100 | medplakaty |
| 8101 | oftal-med |
| **8102** | **proktolog** |
| **9102** | **proktolog php-fpm** |

## Разделы сайта

| Путь | Назначение |
|------|------------|
| `/` | Главная (слайдер, популярные категории, новости) |
| `/catalog/` | Каталог |
| `/personal/` | ЛК, корзина `/personal/cart/`, заказ `/personal/order/` |
| `/about/` `/dostavka/` `/oplata/` `/kontakty/` | Инфо |
| `/news/` `/articles/` | Контент |
| `/klientam/` | Клиентам |
| `/auth/` `/login/` | Авторизация |
| `/bitrix/admin/` | Админка Bitrix |

## Кастом / заметки

- `hand1CtoSite.php` — обмен с 1С
- `prime.alerts` — политика email/алертов (`local/modules/`)
- В `dbconn.php` на prod жёстко `SERVER_PORT=443` — локально перекрывается `dbconn.local.php`
- Композит/html_pages при старте soft-стенда отключается (`.enabled` → `.enabled.local-off`)

## Деплой на prod

**Git после правок — всегда.** **Prod — только по явной просьбе.**

| | |
|---|---|
| SSH / host | `45.90.35.63` |
| Path | `/var/www/proktolog.su/data/www/proktolog.su` |
| Remote | https://github.com/bziksv/proktolog |

```bash
cd proktolog.su
git add … && git commit -m "…" && git push origin main

# только когда пользователь просит выкатить на сервер
```

**Запрещено:** автодеплой на prod, правки на prod без commit, `scp` файлов кода.

## Проверка

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://proktolog.su/
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8102/
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8102/catalog/
```
