# Database

Пакет на основе `illuminate/database` (Laravel Eloquent), доработанный с помощью функционала очереди (EloquentPersistence) и некоторыми другими полезными функциями

Требования при работе с Eloquent, чтобы уменьшить число неприятностей в будущем проекта

- не создавать модели в коде используя `new ModelClass()`, использовать для этого `ModelClass::new()`;
- перед именем связи в коде класса модели ставить символ `_` - от этого будет зависеть проверка и "значение по-умолчанию" для свойства, а также будут пропущены запросы заведомо выбирающие пустую выборку, также будет выдано сообщение, если вы пытаетесь сделать неявный запрос;
- для пагинации и выборки большого числа записей пользуйтесь инструментом ModelClass::chunk() или $query->chunkBuilder(), предоставляемым этим пакетом, где это делается с помощью генераторов, распределяя по времени нагрузку на обмен данных между базой и приложением;
- при записи данных пользоваться Persistence, чтобы все запросы выполнялись после того, как логика действия была выполнена - это уменьшит время транзакции, а значит и количество блокировок;
- указывая связи для загрузки использовать инструмент ModelClass::relationDot() передавая туда callable (в этом случае, если вы переименуете метод связи - оно будет переименовано во всем коде, как callable)

```
PS. Использование `symfony/doctrine` это конечно хорошо. Но человека нужно ему учить, долго учить, потом он сам должен много учить, а потом эта штука будет работать медленнее, чем обычные запросы к PDO, поэтому лучше использовать "ёлку"
PS2. Можно использовать PDO и встроенный в PHP способ работы с базами данных. К сожалению, этот метод недостаточно прост в использовании, а также требует постоянной проверки на SQL-иньекции вручную либо подсчета биндов для запроса... Это можно (и, по-хорошему) нужно делать, но далеко не все специалисты на рынке имеют достаточно опыта, чтобы делать это без подготовки и набитых шишек. И потом, бизнес-задачи имеют свойство "наращиваться", а вот собирать из кусков SQL запрос это тот ещё ад, сильно проще использовать для этого ёлковский билдер
```

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
<?php

require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
});
set_exception_handler(function (\Throwable $e) {
    // require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
    // dd();

    $current = $e;
    do {
        echo PHP_EOL;

        echo \Gzhegow\Lib\Lib::debug_var_dump($current) . PHP_EOL;

        $message = $current->getMessage();
        if (is_a($e, \PDOException::class)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $message = mb_convert_encoding($message, 'utf-8', 'cp1251');
            }
        }
        echo $message . PHP_EOL;

        $file = $current->getFile() ?? '{file}';
        $line = $current->getLine() ?? '{line}';
        echo "{$file} : {$line}" . PHP_EOL;

        foreach ( $e->getTrace() as $traceItem ) {
            $file = $traceItem[ 'file' ] ?? '{file}';
            $line = $traceItem[ 'line' ] ?? '{line}';

            echo "{$file} : {$line}" . PHP_EOL;
        }
    } while ( $current = $current->getPrevious() );

    die();
});


// > добавляем несколько функция для тестирования
function _dump(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_value($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _debug(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug_type_id($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert_resource(STDOUT);
    \Gzhegow\Lib\Lib::assert_output($trace, $fn, $expect);
}


// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\Database\Core\OrmFactory();

// > создаем контейнер для Eloquent (не обязательно)
// $illuminateContainer = new \Illuminate\Container\Container();
$illuminateContainer = null;

// > создаем экземпляр Eloquent
$eloquent = new \Gzhegow\Database\Package\Illuminate\Database\Capsule\Eloquent(
    $illuminateContainer
);

// > добавляем соединение к БД
$eloquent->addConnection(
    [
        'driver' => 'mysql',

        'host'     => 'localhost',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'test',

        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',

        'options' => [
            \PDO::ATTR_EMULATE_PREPARES => true,
            \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
        ],
    ],
    $connName = 'default'
);

// > устанавливаем длину строки для новых таблиц по-умолчанию
\Illuminate\Database\Schema\Builder::$defaultStringLength = 150;

// > запускаем внутренние загрузочные действия Eloquent
$eloquent->bootEloquent();

// // > создаем диспетчер для Eloquent (необходим для логирования, но не обязателен)
// $illuminateDispatcher = new \Illuminate\Events\Dispatcher(
//     $illuminateContainer
// );
// $eloquent->setEventDispatcher($illuminateDispatcher);

// // > включаем логирование Eloquent
// $connection = $eloquent->getConnection();
// $connection->enableQueryLog();
// $connection->listen(static function ($query) {
//     $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
//     $trace = array_slice($trace, 6);
//
//     $files = [];
//     foreach ( $trace as $item ) {
//         $traceFile = $item[ 'file' ] ?? '';
//         $traceLine = $item[ 'line' ] ?? '';
//
//         if (! $traceFile) continue;
//
//         // > таким образом можно фильтровать список файлов при дебаге, в каком запросе ошибка
//         // if (false !== strpos($traceFile, '/vendor/')) continue;
//
//         $files[] = "{$traceFile}: $traceLine";
//     }
//
//     $sql = preg_replace('~\s+~', ' ', trim($query->sql));
//     $bindings = $query->bindings;
//
//     $context = [
//         'sql'      => $sql,
//         'bindings' => $bindings,
//         'files'    => $files,
//     ];
//
//     echo '[ SQL ] ' . \Gzhegow\Lib\Lib::debug_array_multiline($context) . PHP_EOL;
// });

// > создаем Persistence для Eloquent (с помощью него будем откладывать выполнение запросов в очередь, уменьшая время транзакции)
$eloquentPersistence = new \Gzhegow\Database\Core\Persistence\EloquentPersistence(
    $eloquent
);

// > создаем фасад
$facade = new \Gzhegow\Database\Core\OrmFacade(
    $factory,
    //
    $eloquent,
    $eloquentPersistence
);

// > устанавливаем фасад
\Gzhegow\Database\Core\Orm::setFacade($facade);


$conn = $eloquent->getConnection();
$schema = $eloquent->getSchemaBuilder($conn);

$modelClassDemoBar = \Gzhegow\Database\Demo\Model\DemoBarModel::class;
$modelClassDemoBaz = \Gzhegow\Database\Demo\Model\DemoBazModel::class;
$modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;
$modelClassDemoImage = \Gzhegow\Database\Demo\Model\DemoImageModel::class;
$modelClassDemoPost = \Gzhegow\Database\Demo\Model\DemoPostModel::class;
$modelClassDemoTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;
$modelClassDemoUser = \Gzhegow\Database\Demo\Model\DemoUserModel::class;

$tableDemoBar = $modelClassDemoBar::table();
$tableDemoBaz = $modelClassDemoBaz::table();
$tableDemoFoo = $modelClassDemoFoo::table();
$tableDemoImage = $modelClassDemoImage::table();
$tableDemoPost = $modelClassDemoUser::table();
$tableDemoTag = $modelClassDemoTag::table();
$tableDemoUser = $modelClassDemoPost::table();
$tableTaggable = $modelClassDemoTag::tableMorphedByMany('taggable');


// > удаляем таблицы с прошлого раза
$schema->disableForeignKeyConstraints();
$schema->dropAllTables();
$schema->enableForeignKeyConstraints();


// > создаем таблицы поновой
$schema->create(
    $tableDemoFoo,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoBar,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoFoo
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->unsignedBigInteger($tableDemoFoo . '_id')->nullable();
        //
        $blueprint->string('name')->nullable();

        $blueprint
            ->foreign($tableDemoFoo . '_id')
            ->references('id')
            ->on($tableDemoFoo)
            ->onUpdate('CASCADE')
            ->onDelete('CASCADE')
        ;
    });

$schema->create(
    $tableDemoBaz,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoBar
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->unsignedBigInteger($tableDemoBar . '_id')->nullable();
        //
        $blueprint->string('name')->nullable();

        $blueprint
            ->foreign($tableDemoBar . '_id')
            ->references('id')
            ->on($tableDemoBar)
            ->onUpdate('CASCADE')
            ->onDelete('CASCADE')
        ;
    }
);

$schema->create(
    $tableDemoImage,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoImage
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->nullableMorphs('imageable');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoPost,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoPost
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoUser,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoUser
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoTag,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoTag
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableTaggable,
    static function (\Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableTaggable
    ) {
        $blueprint->bigInteger('tag_id')->nullable()->unsigned();
        //
        $blueprint->nullableMorphs('taggable');
    }
);


// >>> TEST
// > рекомендуется в проекте указывать связи в виде callable, чтобы они менялись, когда применяешь `Refactor` в PHPStorm
$fn = function () {
    _dump('[ TEST 1 ]');

    $foo_hasMany_bars_hasMany_bazs = \Gzhegow\Database\Core\Orm::eloquentRelationDot()
    ([ \Gzhegow\Database\Demo\Model\DemoFooModel::class, '_demoBars' ])
    ([ \Gzhegow\Database\Demo\Model\DemoBarModel::class, '_demoBazs' ])
    ();
    _dump($foo_hasMany_bars_hasMany_bazs);

    $bar_belongsTo_foo = \Gzhegow\Database\Demo\Model\DemoBarModel::relationDot()
    ([ \Gzhegow\Database\Demo\Model\DemoBarModel::class, '_demoFoo' ])
    ();
    _dump($bar_belongsTo_foo);

    $bar_hasMany_bazs = \Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel::relationDot()
    ([ \Gzhegow\Database\Demo\Model\DemoBarModel::class, '_demoBazs' ])
    ();
    _dump($bar_hasMany_bazs);

    $bar_belongsTo_foo_only_id = \Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel::relationDot()
    ([ \Gzhegow\Database\Demo\Model\DemoBarModel::class, '_demoFoo' ], 'id')
    ();
    _dump($bar_belongsTo_foo_only_id);

    $bar_hasMany_bazs_only_id = \Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel::relationDot()
    ([ \Gzhegow\Database\Demo\Model\DemoBarModel::class, '_demoBazs' ], 'id')
    ();
    _dump($bar_hasMany_bazs_only_id);

    // > Делаем запрос со связями
    // $query = \Gzhegow\Database\Demo\Model\DemoFooModel::query();
    // $query->with($foo_hasMany_bars_hasMany_bazs);
    // $query->with([
    //     $foo_hasMany_bars_hasMany_bazs,
    // ]);
    // $query->with([
    //     $foo_hasMany_bars_hasMany_bazs => static function ($query) { },
    // ]);
    //
    // $query = \Gzhegow\Database\Demo\Model\DemoBarModel::query();
    // $query->with($bar_belongsTo_foo);
    // $query->with([
    //     $bar_belongsTo_foo,
    //     $bar_hasMany_bazs,
    // ]);
    // $query->with([
    //     $bar_belongsTo_foo => static function ($query) { },
    //     $bar_hasMany_bazs  => static function ($query) { },
    // ]);

    // > Подгружаем связи к уже полученным из базы моделям
    // $query = \Gzhegow\Database\Demo\Model\DemoFooModel::query();
    // $model = $query->firstOrFail();
    // $model->load($foo_hasMany_bars_hasMany_bazs);
    // $model->load([
    //     $foo_hasMany_bars_hasMany_bazs,
    // ]);
    // $model->load([
    //     $foo_hasMany_bars_hasMany_bazs => static function ($query) { },
    // ]);
    //
    // $query = \Gzhegow\Database\Demo\Model\DemoBarModel::query();
    // $model = $query->firstOrFail();
    // $model->load($bar_belongsTo_foo);
    // $model->load([
    //     $bar_belongsTo_foo,
    //     $bar_hasMany_bazs,
    // ]);
    // $model->load([
    //     $bar_belongsTo_foo => static function ($query) { },
    //     $bar_hasMany_bazs  => static function ($query) { },
    // ]);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 1 ]"
"_demoBars._demoBazs"
"_demoFoo"
"_demoBazs"
"_demoFoo:id"
"_demoBazs:id"
""
HEREDOC
);



// >>> TEST
// > используем рекурсивное сохранение для того, чтобы сохранить модели вместе со связями
$fn = function () use (
    $eloquent,
    //
    $tableDemoBar,
    $tableDemoBaz,
    $tableDemoFoo
) {
    _dump('[ TEST 2 ]');


    $modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Database\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Database\Demo\Model\DemoBazModel::class;


    $modelDemoFoo1 = $modelClassDemoFoo::new();
    $modelDemoFoo1->name = 'modelDemoFoo1';
    $modelDemoBar1 = $modelClassDemoBar::new();
    $modelDemoBar1->name = 'modelDemoBar1';
    $modelDemoBaz1 = $modelClassDemoBaz::new();
    $modelDemoBaz1->name = 'modelDemoBaz1';

    $modelDemoFoo2 = $modelClassDemoFoo::new();
    $modelDemoFoo2->name = 'modelDemoFoo2';
    $modelDemoBar2 = $modelClassDemoBar::new();
    $modelDemoBar2->name = 'modelDemoBar2';
    $modelDemoBaz2 = $modelClassDemoBaz::new();
    $modelDemoBaz2->name = 'modelDemoBaz2';


    $modelDemoBar1->_demoFoo = $modelDemoFoo1;
    $modelDemoBaz1->_demoBar = $modelDemoBar1;

    $modelDemoBaz1->saveRecursive();


    $modelDemoBar2->_demoFoo = $modelDemoFoo2;
    $modelDemoBaz2->_demoBar = $modelDemoBar2;
    $modelDemoBar2->_demoBazs[] = $modelDemoBaz2;
    $modelDemoFoo2->_demoBars[] = $modelDemoBar2;

    $modelDemoFoo2->saveRecursive();


    $modelDemoFooResult = $modelClassDemoFoo::query()->get();
    $modelDemoBarResult = $modelClassDemoBar::query()->get();
    $modelDemoBazResult = $modelClassDemoBaz::query()->get();

    _dump($modelDemoFooResult);
    _dump($modelDemoFooResult[ 0 ]->id, $modelDemoFooResult[ 1 ]->id);

    _dump($modelDemoBarResult);
    _dump($modelDemoBarResult[ 0 ]->id, $modelDemoBarResult[ 0 ]->demo_foo_id);
    _dump($modelDemoBarResult[ 1 ]->id, $modelDemoBarResult[ 1 ]->demo_foo_id);

    _dump($modelDemoBazResult);
    _dump($modelDemoBazResult[ 0 ]->id, $modelDemoBazResult[ 0 ]->demo_bar_id);
    _dump($modelDemoBazResult[ 1 ]->id, $modelDemoBazResult[ 1 ]->demo_bar_id);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 2 ]"
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
1 | 2
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
1 | 1
2 | 2
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
1 | 1
2 | 2
""
HEREDOC
);


// >>> TEST
// > используем Persistence для сохранения ранее созданных моделей
// > это нужно, чтобы уменьшить время транзакции - сохранение делаем в конце бизнес-действия
$fn = function () use (
    $eloquent,
    //
    $tableDemoBar,
    $tableDemoBaz,
    $tableDemoFoo
) {
    _dump('[ TEST 3 ]');


    $modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Database\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Database\Demo\Model\DemoBazModel::class;


    $modelDemoFoo3 = $modelClassDemoFoo::new();
    $modelDemoFoo3->name = 'modelDemoFoo3';
    $modelDemoBar3 = $modelClassDemoBar::new();
    $modelDemoBar3->name = 'modelDemoBar3';
    $modelDemoBaz3 = $modelClassDemoBaz::new();
    $modelDemoBaz3->name = 'modelDemoBaz3';

    $modelDemoFoo4 = $modelClassDemoFoo::new();
    $modelDemoFoo4->name = 'modelDemoFoo4';
    $modelDemoBar4 = $modelClassDemoBar::new();
    $modelDemoBar4->name = 'modelDemoBar4';
    $modelDemoBaz4 = $modelClassDemoBaz::new();
    $modelDemoBaz4->name = 'modelDemoBaz4';


    $modelDemoBar3->_demoFoo = $modelDemoFoo3;
    $modelDemoBaz3->_demoBar = $modelDemoBar3;

    $modelDemoBaz3->persistForSaveRecursive();


    $modelDemoBar4->_demoFoo = $modelDemoFoo4;
    $modelDemoBaz4->_demoBar = $modelDemoBar4;
    $modelDemoBar4->_demoBazs[] = $modelDemoBaz4;
    $modelDemoFoo4->_demoBars[] = $modelDemoBar4;

    $modelDemoFoo4->persistForSaveRecursive();


    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $modelDemoFooResult = $modelClassDemoFoo::query()->get();
    $modelDemoBarResult = $modelClassDemoBar::query()->get();
    $modelDemoBazResult = $modelClassDemoBaz::query()->get();

    _dump($modelDemoFooResult);
    _dump($modelDemoFooResult[ 2 ]->id, $modelDemoFooResult[ 3 ]->id);

    _dump($modelDemoBarResult);
    _dump($modelDemoBarResult[ 2 ]->id, $modelDemoBarResult[ 2 ]->demo_foo_id);
    _dump($modelDemoBarResult[ 3 ]->id, $modelDemoBarResult[ 3 ]->demo_foo_id);

    _dump($modelDemoBazResult);
    _dump($modelDemoBazResult[ 2 ]->id, $modelDemoBazResult[ 2 ]->demo_bar_id);
    _dump($modelDemoBazResult[ 3 ]->id, $modelDemoBazResult[ 3 ]->demo_bar_id);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 3 ]"
{ object(iterable countable(4)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
3 | 4
{ object(iterable countable(4)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
3 | 3
4 | 4
{ object(iterable countable(4)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
3 | 3
4 | 4
""
HEREDOC
);


// >>> TEST
// > используем механизм Chunk, чтобы считать данные из таблиц
// > на базе механизма работает и пагинация, предлагается два варианта - нативный SQL LIMIT/OFFSET и COLUMN(>|>=|<|<=)VALUE
$fn = function () use (
    $eloquent,
    //
    $conn,
    $schema,
    $tableDemoBar,
    $tableDemoBaz,
    $tableDemoFoo
) {
    _dump('[ TEST 4 ]');


    $modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;

    _dump('chunkModelNativeForeach');
    foreach ( $modelClassDemoFoo::chunkModelNativeForeach(
        $limitChunk = 1, $limit = 2
    ) as $chunk ) {
        _dump($chunk);
    }

    _dump('chunkModelAfterForeach');
    foreach ( $modelClassDemoFoo::chunkModelAfterForeach(
        $limitChunk = 1, $limit = null,
        $offsetColumn = 'id', $offsetOperator = '>', $offsetValue = 1, $includeOffsetValue = false
    ) as $chunk ) {
        _dump($chunk);
    }


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 4 ]"
"chunkModelNativeForeach"
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"chunkModelAfterForeach"
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
""
HEREDOC
);


// >>> TEST
// > тестирование связей (для примера взят Morph), у которых в этом пакете изменился интерфейс создания
$fn = function () use (
    $eloquent,
    //
    $tableDemoImage,
    $tableDemoPost,
    $tableDemoUser
) {
    _dump('[ TEST 5 ]');


    $modelPost1 = \Gzhegow\Database\Demo\Model\DemoPostModel::new();
    $modelPost1->name = 'modelPost1';

    $modelUser1 = \Gzhegow\Database\Demo\Model\DemoUserModel::new();
    $modelUser1->name = 'modelUser1';

    $modelImage1 = \Gzhegow\Database\Demo\Model\DemoImageModel::new();
    $modelImage1->name = 'modelImage1';

    $modelImage2 = \Gzhegow\Database\Demo\Model\DemoImageModel::new();
    $modelImage2->name = 'modelImage2';

    $modelImage1->_imageable = $modelPost1;
    $modelImage2->_imageable = $modelUser1;

    $modelPost1->_demoImages[] = $modelImage1;

    $modelUser1->_demoImages[] = $modelImage2;


    $modelImage1->persistForSaveRecursive();
    $modelImage2->persistForSaveRecursive();

    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $modelImageQuery = $modelImage1::query()
        ->with(
            $modelPost1::relationDot()([ $modelImage1, '_imageable' ])()
        )
    ;
    $modelPostQuery = $modelPost1::query()
        ->with(
            $modelPost1::relationDot()([ $modelPost1, '_demoImages' ])()
        )
    ;
    $modelUserQuery = $modelUser1::query()
        ->with(
            $modelUser1::relationDot()([ $modelUser1, '_demoImages' ])()
        )
    ;

    $modelImageResult = \Gzhegow\Database\Demo\Model\DemoImageModel::get($modelImageQuery);
    $modelPostResult = \Gzhegow\Database\Demo\Model\DemoPostModel::get($modelPostQuery);
    $modelUserResult = \Gzhegow\Database\Demo\Model\DemoUserModel::get($modelUserQuery);

    _dump($modelImageResult);
    _dump($modelImageResult[ 0 ], $modelImageResult[ 0 ]->_imageable);
    _dump($modelPostResult);
    _dump($modelPostResult[ 0 ], $modelPostResult[ 0 ]->_demoImages[ 0 ]);
    _dump($modelUserResult);
    _dump($modelUserResult[ 0 ], $modelUserResult[ 0 ]->_demoImages[ 0 ]);


    $modelPost2 = \Gzhegow\Database\Demo\Model\DemoPostModel::new();
    $modelPost2->name = 'modelPost2';

    $modelUser2 = \Gzhegow\Database\Demo\Model\DemoUserModel::new();
    $modelUser2->name = 'modelUser2';

    $modelTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;

    $modelTag1 = $modelTag::new();
    $modelTag1->name = 'modelTag1';

    $modelTag2 = $modelTag::new();
    $modelTag2->name = 'modelTag2';


    $modelPost2->persistForSave();
    $modelPost2->_demoTags()->persistForSaveMany([
        $modelTag1,
        $modelTag2,
    ]);

    $modelUser2->persistForSave();
    $modelUser2->_demoTags()->persistForSaveMany([
        $modelTag1,
        $modelTag2,
    ]);

    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $modelTagQuery = $modelTag::query()
        ->with([
            $modelTag::relationDot()([ $modelTag, '_demoPosts' ])(),
            $modelTag::relationDot()([ $modelTag, '_demoUsers' ])(),
        ])
    ;
    $modelPostQuery = $modelPost2::query()
        ->with(
            $modelPost2::relationDot()([ $modelPost2, '_demoTags' ])()
        )
    ;
    $modelUserQuery = $modelUser2::query()
        ->with(
            $modelUser2::relationDot()([ $modelUser2, '_demoTags' ])()
        )
    ;

    $modelTagResult = \Gzhegow\Database\Demo\Model\DemoTagModel::get($modelTagQuery);
    $modelPostResult = \Gzhegow\Database\Demo\Model\DemoPostModel::get($modelPostQuery);
    $modelUserResult = \Gzhegow\Database\Demo\Model\DemoUserModel::get($modelUserQuery);

    _dump($modelTagResult);
    _dump($modelTagResult[ 0 ], $modelTagResult[ 0 ]->_demoPosts[ 0 ], $modelTagResult[ 0 ]->_demoUsers[ 0 ]);
    _dump($modelTagResult[ 1 ], $modelTagResult[ 1 ]->_demoPosts[ 0 ], $modelTagResult[ 1 ]->_demoUsers[ 0 ]);
    _dump($modelPostResult);
    _dump($modelPostResult[ 1 ], $modelPostResult[ 1 ]->_demoTags[ 0 ], $modelPostResult[ 1 ]->_demoTags[ 1 ]);
    _dump($modelUserResult);
    _dump($modelUserResult[ 1 ], $modelUserResult[ 1 ]->_demoTags[ 0 ], $modelUserResult[ 1 ]->_demoTags[ 1 ]);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 5 ]"
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoImageModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel }
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoImageModel }
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoUserModel } | { object # Gzhegow\Database\Demo\Model\DemoImageModel }
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoUserModel }
{ object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoUserModel }
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel }
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoUserModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel }
""
HEREDOC
);


// > удаляем таблицы после тестов
$schema->disableForeignKeyConstraints();
$schema->dropAllTables();
$schema->enableForeignKeyConstraints();
```