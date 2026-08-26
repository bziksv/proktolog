# Фикс «Заказать звонок» (proktolog.su)

Эталон: kawe.su (`medical-templates`, `nbrains:main.feedback` / `popup-callback`).

## Параметры proktolog

| Параметр | Значение |
|----------|----------|
| Шаблон | `medical-templates` |
| IBLOCK | `37` (`feedback`) |
| Шаблон письма | `53`, событие `CALLBACK` |
| Planfix (как заказы) | `proktolog@almamed.planfix.ru` |
| Поля | `NAME`, `PHONE`, `MAIL`, `QUERY`, `URL` |
| Согласие | `/legal/proktolog-soglasie-pd/`, `/legal/proktolog-politika-pd/` |

## Изменённые файлы

- `bitrix/components/nbrains/main.feedback/component.php`
- `bitrix/php_interface/init.php` — `BX_COMPOSITE_DISABLED` на POST / `?success=`
- `bitrix/templates/medical-templates/footer.php`
- `bitrix/templates/medical-templates/js/functions.js`
- `.../popup-callback/template.php`, `style.css`

## БД (локально применено)

```sql
UPDATE b_event_message
SET EMAIL_TO = 'proktolog@almamed.planfix.ru'
WHERE ID = 53 AND EVENT_NAME = 'CALLBACK';
```

Заказы: `SALE_NEW_ORDER` BCC = `proktolog@almamed.planfix.ru` (уже было).

## На prod после деплоя

1. Rsync/git те же файлы
2. SQL выше на prod БД
3. Кеш: `rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/* bitrix/html_pages/*`

## Проверка (curl)

```bash
HTML=$(curl -sS -c /tmp/c.txt https://proktolog.su/)
SESSID=$(echo "$HTML" | sed -n "s/.*'bitrix_sessid':'\\([^']*\\)'.*/\\1/p" | head -1)
HASH=$(echo "$HTML" | sed -n 's/.*id="callback" data-params-hash="\\([^"]*\\)".*/\\1/p' | head -1)

# без галочки → ошибка
curl -sS -b /tmp/c.txt -X POST https://proktolog.su/ \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "submit=Отправить&PARAMS_HASH=${HASH}&sessid=${SESSID}&NAME=Test&PHONE=%2B79999999999&MAIL=t@test.ru&QUERY=test&URL=/" \
  | grep -o 'errortext'

# с галочкой → успех
curl -sS -b /tmp/c.txt -X POST https://proktolog.su/ \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "submit=Отправить&PARAMS_HASH=${HASH}&sessid=${SESSID}&NAME=Test&PHONE=%2B79999999999&MAIL=t@test.ru&QUERY=test&URL=/&callback-consent=on" \
  | grep -o 'mf-ok-text'
```

## Локальная проверка (2026-08-26)

- Без consent → `errortext` + текст про согласие
- С consent → `mf-ok-text`
- iblock #37 → элемент создан
- `b_event` MESSAGE_ID=53, `EMAIL_TO=proktolog@almamed.planfix.ru`, `SUCCESS_EXEC=Y`

## Типичные ошибки

| Симптом | Причина |
|---------|---------|
| Форма очистилась | AJAX + LocalRedirect или нет `submit` в POST |
| Инкогнито | пустой sessid в composite-кеше |
| Успех, Planfix пуст | `EMAIL_TO` в шаблоне 53 ≠ Planfix |

Полный playbook для других проектов — в промпте агента (kawe commits `8d80f9a` … `5382fc82`).
