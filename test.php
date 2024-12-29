<?php

require_once getenv('COMPOSER_HOME') . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');


// > настраиваем обработку ошибок
(new \Gzhegow\Lib\Exception\ErrorHandler())
    ->useErrorReporting()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
function _debug(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->type_id($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump(...$values) : void
{
    $lines = [];
    foreach ( $values as $value ) {
        $lines[] = \Gzhegow\Lib\Lib::debug()->value($value);
    }

    echo implode(' | ', $lines) . PHP_EOL;
}

function _dump_array($value, int $maxLevel = null, bool $multiline = false) : void
{
    $content = $multiline
        ? \Gzhegow\Lib\Lib::debug()->array_multiline($value, $maxLevel)
        : \Gzhegow\Lib\Lib::debug()->array($value, $maxLevel);

    echo $content . PHP_EOL;
}

function _assert_output(
    \Closure $fn, string $expect = null
) : void
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

    \Gzhegow\Lib\Lib::assert()->resource_static(STDOUT);
    \Gzhegow\Lib\Lib::assert()->output($trace, $fn, $expect);
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
// > используем рекурсивное сохранение для того, чтобы сохранить модели вместе со связями
$fn = function () use (
    $eloquent,
    $schema
) {
    _dump('[ TEST 1 ]');


    $modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Database\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Database\Demo\Model\DemoBazModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoFoo::query()->truncate();
    $modelClassDemoBar::query()->truncate();
    $modelClassDemoBaz::query()->truncate();
    $schema->enableForeignKeyConstraints();


    $foo1 = $modelClassDemoFoo::new();
    $foo1->name = 'foo1';
    $bar1 = $modelClassDemoBar::new();
    $bar1->name = 'bar1';
    $baz1 = $modelClassDemoBaz::new();
    $baz1->name = 'baz1';

    $foo2 = $modelClassDemoFoo::new();
    $foo2->name = 'foo2';
    $bar2 = $modelClassDemoBar::new();
    $bar2->name = 'bar2';
    $baz2 = $modelClassDemoBaz::new();
    $baz2->name = 'baz2';


    $bar1->_demoFoo = $foo1;
    $baz1->_demoBar = $bar1;

    $baz1->saveRecursive();


    $bar2->_demoFoo = $foo2;
    $baz2->_demoBar = $bar2;
    $bar2->_demoBazs[] = $baz2;
    $foo2->_demoBars[] = $bar2;

    $foo2->saveRecursive();


    $fooCollection = $modelClassDemoFoo::query()->get([ '*' ]);
    $barCollection = $modelClassDemoBar::query()->get([ '*' ]);
    $bazCollection = $modelClassDemoBaz::query()->get([ '*' ]);

    _dump($fooCollection);
    _dump($fooCollection[ 0 ]->id, $fooCollection[ 1 ]->id);

    _dump($barCollection);
    _dump($barCollection[ 0 ]->id, $barCollection[ 0 ]->demo_foo_id);
    _dump($barCollection[ 1 ]->id, $barCollection[ 1 ]->demo_foo_id);

    _dump($bazCollection);
    _dump($bazCollection[ 0 ]->id, $bazCollection[ 0 ]->demo_bar_id);
    _dump($bazCollection[ 1 ]->id, $bazCollection[ 1 ]->demo_bar_id);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 1 ]"
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
    $schema
) {
    _dump('[ TEST 2 ]');


    $modelClassDemoFoo = \Gzhegow\Database\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Database\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Database\Demo\Model\DemoBazModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoFoo::query()->truncate();
    $modelClassDemoBar::query()->truncate();
    $modelClassDemoBaz::query()->truncate();
    $schema->enableForeignKeyConstraints();


    $foo3 = $modelClassDemoFoo::new();
    $foo3->name = 'foo3';
    $bar3 = $modelClassDemoBar::new();
    $bar3->name = 'bar3';
    $baz3 = $modelClassDemoBaz::new();
    $baz3->name = 'baz3';

    $foo4 = $modelClassDemoFoo::new();
    $foo4->name = 'foo4';
    $bar4 = $modelClassDemoBar::new();
    $bar4->name = 'bar4';
    $baz4 = $modelClassDemoBaz::new();
    $baz4->name = 'baz4';


    $bar3->_demoFoo = $foo3;
    $baz3->_demoBar = $bar3;

    $baz3->persistForSaveRecursive();


    $bar4->_demoFoo = $foo4;
    $baz4->_demoBar = $bar4;
    $bar4->_demoBazs[] = $baz4;
    $foo4->_demoBars[] = $bar4;

    $foo4->persistForSaveRecursive();


    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $fooCollection = $modelClassDemoFoo::query()->get([ '*' ]);
    $barCollection = $modelClassDemoBar::query()->get([ '*' ]);
    $bazCollection = $modelClassDemoBaz::query()->get([ '*' ]);

    _dump($fooCollection);
    _dump($fooCollection[ 0 ]->id, $fooCollection[ 1 ]->id);

    _dump($barCollection);
    _dump($barCollection[ 0 ]->id, $barCollection[ 0 ]->demo_foo_id);
    _dump($barCollection[ 1 ]->id, $barCollection[ 1 ]->demo_foo_id);

    _dump($bazCollection);
    _dump($bazCollection[ 0 ]->id, $bazCollection[ 0 ]->demo_bar_id);
    _dump($bazCollection[ 1 ]->id, $bazCollection[ 1 ]->demo_bar_id);


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
// > тестирование связей (для примера взят Morph), у которых в этом пакете изменился интерфейс создания
$fn = function () use (
    $eloquent,
    $schema
) {
    _dump('[ TEST 3 ]');


    $modelClassDemoPost = \Gzhegow\Database\Demo\Model\DemoPostModel::class;
    $modelClassDemoUser = \Gzhegow\Database\Demo\Model\DemoUserModel::class;
    $modelClassDemoImage = \Gzhegow\Database\Demo\Model\DemoImageModel::class;
    $modelClassDemoTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoPost::query()->truncate();
    $modelClassDemoUser::query()->truncate();
    $modelClassDemoImage::query()->truncate();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();


    $post1 = $modelClassDemoPost::new();
    $post1->name = 'post1';

    $user1 = $modelClassDemoUser::new();
    $user1->name = 'user1';

    $image1 = $modelClassDemoImage::new();
    $image1->name = 'image1';

    $image2 = $modelClassDemoImage::new();
    $image2->name = 'image2';

    $image1->_imageable = $post1;
    $image2->_imageable = $user1;

    $post1->_demoImages[] = $image1;

    $user1->_demoImages[] = $image2;


    $image1->persistForSaveRecursive();
    $image2->persistForSaveRecursive();

    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $imageQuery = $image1::query()
        ->addColumns($image1->getMorphKeys('imageable'))
        ->with(
            $post1::relationDot()([ $image1, '_imageable' ])()
        )
    ;
    $postQuery = $post1::query()
        ->with(
            $post1::relationDot()([ $post1, '_demoImages' ])()
        )
    ;
    $userQuery = $user1::query()
        ->with(
            $user1::relationDot()([ $user1, '_demoImages' ])()
        )
    ;

    $imageCollection = $modelClassDemoImage::get($imageQuery);
    $postCollection = $modelClassDemoPost::get($postQuery);
    $userCollection = $modelClassDemoUser::get($userQuery);

    _dump($imageCollection);
    _dump($imageCollection[ 0 ], $imageCollection[ 0 ]->_imageable);
    _dump('');

    _dump($postCollection);
    _dump($postCollection[ 0 ], $postCollection[ 0 ]->_demoImages[ 0 ]);
    _dump('');

    _dump($userCollection);
    _dump($userCollection[ 0 ], $userCollection[ 0 ]->_demoImages[ 0 ]);
    _dump('');


    $post2 = $modelClassDemoPost::new();
    $post2->name = 'post2';

    $user2 = $modelClassDemoUser::new();
    $user2->name = 'user2';

    $tag1 = $modelClassDemoTag::new();
    $tag1->name = 'tag1';

    $tag2 = $modelClassDemoTag::new();
    $tag2->name = 'tag2';


    $post2->persistForSave();
    $post2->_demoTags()->persistForSaveMany([
        $tag1,
        $tag2,
    ]);

    $user2->persistForSave();
    $user2->_demoTags()->persistForSaveMany([
        $tag1,
        $tag2,
    ]);

    \Gzhegow\Database\Core\Orm::getEloquentPersistence()->flush();


    $tagQuery = $modelClassDemoTag::query()
        ->with([
            $modelClassDemoTag::relationDot()([ $modelClassDemoTag, '_demoPosts' ])(),
            $modelClassDemoTag::relationDot()([ $modelClassDemoTag, '_demoUsers' ])(),
        ])
    ;
    $postQuery = $post2::query()
        ->with(
            $post2::relationDot()([ $post2, '_demoTags' ])()
        )
    ;
    $userQuery = $user2::query()
        ->with(
            $user2::relationDot()([ $user2, '_demoTags' ])()
        )
    ;

    $tagCollection = $modelClassDemoTag::get($tagQuery);
    $postCollection = $modelClassDemoPost::get($postQuery);
    $userCollection = $modelClassDemoUser::get($userQuery);

    _dump($tagCollection);
    _dump($tagCollection[ 0 ], $tagCollection[ 0 ]->_demoPosts[ 0 ], $tagCollection[ 0 ]->_demoUsers[ 0 ]);
    _dump($tagCollection[ 1 ], $tagCollection[ 1 ]->_demoPosts[ 0 ], $tagCollection[ 1 ]->_demoUsers[ 0 ]);
    _dump('');

    _dump($postCollection);
    _dump($postCollection[ 1 ], $postCollection[ 1 ]->_demoTags[ 0 ], $postCollection[ 1 ]->_demoTags[ 1 ]);
    _dump('');

    _dump($userCollection);
    _dump($userCollection[ 1 ], $userCollection[ 1 ]->_demoTags[ 0 ], $userCollection[ 1 ]->_demoTags[ 1 ]);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 3 ]"
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoImageModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel }
""
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoImageModel }
""
{ object(iterable countable(1)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoUserModel } | { object # Gzhegow\Database\Demo\Model\DemoImageModel }
""
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoUserModel }
{ object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoUserModel }
""
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoPostModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel }
""
{ object(iterable countable(2)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object # Gzhegow\Database\Demo\Model\DemoUserModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel } | { object # Gzhegow\Database\Demo\Model\DemoTagModel }
""
HEREDOC
);


// >>> TEST
// > можно подсчитать количество записей в таблице используя EXPLAIN, к сожалению, будет показано число строк, которое придется обработать, а не число строк по результатам запроса
// > но иногда этого достаточно, особенно если запрос покрыт должным числом индексов, чтобы отобразить "Всего: ~100 страниц"
$fn = function () use (
    $eloquent,
    $schema
) {
    _dump('[ TEST 4 ]');


    $modelClassDemoTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    $query = $modelClassDemoTag::query()->where('name', 'tag77');
    _dump($cnt = $query->count(), $cnt === 1);

    $cnt = $query->countExplain();
    _dump($cnt > 1, $cnt <= 100);


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 4 ]"
1 | TRUE
TRUE | TRUE
""
HEREDOC
);


// >>> TEST
// > используем механизм Chunk, чтобы считать данные из таблиц
// > на базе механизма работает и пагинация, предлагается два варианта - нативный SQL LIMIT/OFFSET и COLUMN(>|>=|<|<=)VALUE
$fn = function () use (
    $eloquent,
    $schema
) {
    _dump('[ TEST 5 ]');


    $modelClassDemoTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    _dump('chunkModelNativeForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder->chunksModelNativeForeach(
        $limitChunk = 25, $limit = null, $offset = null
    );
    foreach ( $builder->chunksForeach() as $chunk ) {
        _dump($chunk);
    }

    _dump('chunkModelAfterForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder = $builder->chunksModelAfterForeach(
        $limitChunk = 25, $limit = null,
        $offsetColumn = 'id', $offsetOperator = '>', $offsetValue = 1, $includeOffsetValue = true
    );
    foreach ( $builder->chunksForeach() as $chunk ) {
        _dump($chunk);
    }


    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 5 ]"
"chunkModelNativeForeach"
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"chunkModelAfterForeach"
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(iterable countable(25)) # Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
""
HEREDOC
);


// >>> TEST
// > используем механизм Chunk, чтобы считать данные из таблиц
// > на базе механизма работает и пагинация, предлагается два варианта - нативный SQL LIMIT/OFFSET и COLUMN(>|>=|<|<=)VALUE
$fn = function () use (
    $eloquent,
    $schema
) {
    _dump('[ TEST 6 ]');


    $modelClassDemoTag = \Gzhegow\Database\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    _dump('paginateModelNativeForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder
        // ->setTotalItems(100)
        // ->setTotalPages(8)
        // ->withSelectCountNative()
        // ->withSelectCountExplain()
        ->paginatePdoNativeForeach(
            $perPage = 13, $page = 7, $pagesDelta = 2,
            $offset = null
        )
    ;

    $result = $builder->paginateResult();
    _dump_array((array) $result, 1, true);
    _dump_array($result->pagesAbsolute, 1, true);
    _dump_array($result->pagesRelative, 1, true);

    _dump('paginateModelAfterForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder
        // ->setTotalItems(100)
        // ->setTotalPages(8)
        // ->withSelectCountNative()
        // ->withSelectCountExplain()
        ->paginatePdoAfterForeach(
            $perPage = 13, $page = 7, $pagesDelta = 2,
            $offsetColumn = 'id', $offsetOperator = '>', $offsetValue = 1, $includeOffsetValue = true
        )
    ;

    $result = $builder->paginateResult();
    _dump_array((array) $result, 1, true);
    _dump_array($result->pagesAbsolute, 1, true);
    _dump_array($result->pagesRelative, 1, true);

    echo '';
};
_assert_output($fn, <<<HEREDOC
"[ TEST 6 ]"
"paginateModelNativeForeach"
[
  "totalItems" => 100,
  "totalPages" => 8,
  "page" => 7,
  "perPage" => 13,
  "pagesDelta" => 2,
  "from" => 78,
  "to" => 91,
  "pagesAbsolute" => "{ array(5) }",
  "pagesRelative" => "{ array(5) }",
  "items" => "{ object(iterable countable(13)) # Illuminate\Support\Collection }"
]
[
  1 => 13,
  5 => 13,
  6 => 13,
  7 => 13,
  8 => 9
]
[
  "first" => 13,
  "previous" => 13,
  "current" => 13,
  "next" => NULL,
  "last" => 9
]
"paginateModelAfterForeach"
[
  "totalItems" => 100,
  "totalPages" => 8,
  "page" => 7,
  "perPage" => 13,
  "pagesDelta" => 2,
  "from" => 78,
  "to" => 91,
  "pagesAbsolute" => "{ array(5) }",
  "pagesRelative" => "{ array(5) }",
  "items" => "{ object(iterable countable(13)) # Illuminate\Support\Collection }"
]
[
  1 => 13,
  5 => 13,
  6 => 13,
  7 => 13,
  8 => 9
]
[
  "first" => 13,
  "previous" => 13,
  "current" => 13,
  "next" => NULL,
  "last" => 9
]
""
HEREDOC
);


// >>> TEST
// > рекомендуется в проекте указывать связи в виде callable, чтобы они менялись, когда применяешь `Refactor` в PHPStorm
$fn = function () use ($eloquent) {
    _dump('[ TEST 7 ]');

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
"[ TEST 7 ]"
"_demoBars._demoBazs"
"_demoFoo"
"_demoBazs"
"_demoFoo:id"
"_demoBazs:id"
""
HEREDOC
);


// > удаляем таблицы после тестов
$schema->disableForeignKeyConstraints();
$schema->dropAllTables();
$schema->enableForeignKeyConstraints();
