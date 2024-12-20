# Database

Пакет на основе `illuminate/database` (Laravel Eloquent), доработанный с помощью функционала очереди (EloquentPersistence) и некоторыми другими полезными функциями

Требования при работе с Eloquent, чтобы уменьшить число неприятностей в будущем проекта

- не создавать модели в коде используя `new ModelClass()`, использовать для этого `ModelClass::from()`
- перед именем связи в коде класса модели ставить символ `_` - от этого будет зависеть проверка и "значение по-умолчанию" для свойства, а также будут пропущены запросы заведомо выбирающие пустую выборку, также будет выдано сообщение, если вы пытаетесь сделать неявный запрос
- для пагинации и выборки большого числа записей пользуйтесь инструментом chunk() / page(), предоставляемым этим пакетом, где это делается с помощью генераторов, распределяя нагрузку на обмен данных между базой и приложением
- при записи данных пользоваться Persistence, чтобы все запросы выполнялись после того, как логика действия была выполнена - это уменьшит время транзакции, а значит и блокировок
- указывая связи для загрузки использовать callable тип (в этом случае, если вы переименуете поле в таблице и имя связи - оно будет переименовано во всем коде, как callable):

PS. Использование `symfony/doctrine` это конечно хорошо. Но человека нужно ему учить, долго учить, потом он сам должен много учить, а потом эта штука будет работать медленнее, чем обычные запросы к PDO, поэтому лучше использовать "ёлку"
PS2. Можно использовать PDO и встроенный в PHP способ работы с базами данных. К сожалению, этот метод недостаточно прост в использовании, а также требует постоянной проверки на SQL-иньекции вручную либо подсчета биндов для запроса... Это можно (и, по-хорошему) нужно делать, но далеко не все специалисты на рынке имеют достаточно опыта, чтобы делать это без подготовки и набитых шишек. И потом, бизнес-задачи имеют свойство "наращиваться", а вот собирать из кусков SQL запрос это тот ещё ад, сильно проще использовать для этого ёлковский билдер

```
// НЕ ВЕРНО:
$query->with([
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
]);
$model->load([
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
]);
$collection->load([
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
    '_relationName._relationNameChild',
]);

// ВЕРНО:
$query->with([
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
]);
$model->load([
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
]);
$collection->load([
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
    Database::relation([ ModelClass::class, '_relationName' ])([ Model2Class::class, '_relationNameChild' ])(),
]);
```

## Установка

```
composer require gzhegow/database;
```

## Пример

```php
```