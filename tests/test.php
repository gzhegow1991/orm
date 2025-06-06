<?php

require_once __DIR__ . '/../vendor/autoload.php';


// > настраиваем PHP
\Gzhegow\Lib\Lib::entrypoint()
    ->setDirRoot(__DIR__ . '/..')
    //
    ->useErrorReporting()
    ->useMemoryLimit()
    ->useUmask()
    ->useErrorHandler()
    ->useExceptionHandler()
;


// > добавляем несколько функция для тестирования
$ffn = new class {
    function root()
    {
        return realpath(__DIR__ . '/..');
    }


    function value_array($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array($value, $maxLevel, $options);
    }

    function value_array_multiline($value, ?int $maxLevel = null, array $options = []) : string
    {
        return \Gzhegow\Lib\Lib::debug()->value_array_multiline($value, $maxLevel, $options);
    }


    function types($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->types([], $separator, ...$values);
    }

    function values($separator = null, ...$values) : string
    {
        return \Gzhegow\Lib\Lib::debug()->values([], $separator, ...$values);
    }


    function print(...$values) : void
    {
        echo $this->values(' | ', ...$values) . PHP_EOL;
    }

    function print_types(...$values) : void
    {
        echo $this->types(' | ', ...$values) . PHP_EOL;
    }


    function print_array($value, ?int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array($value, $maxLevel, $options) . PHP_EOL;
    }

    function print_array_multiline($value, ?int $maxLevel = null, array $options = []) : void
    {
        echo $this->value_array_multiline($value, $maxLevel, $options) . PHP_EOL;
    }


    function test(\Closure $fn, array $args = []) : \Gzhegow\Lib\Modules\Test\Test
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        return \Gzhegow\Lib\Lib::test()->newTest()
            ->fn($fn, $args)
            ->trace($trace)
        ;
    }
};



\Gzhegow\Lib\Lib::require_composer_global();



// >>> ЗАПУСКАЕМ!

// > сначала всегда фабрика
$factory = new \Gzhegow\Orm\Core\OrmFactory();

// > создаем контейнер для Eloquent (не обязательно)
// $illuminateContainer = new \Illuminate\Container\Container();
$illuminateContainer = null;

// > создаем экземпляр Eloquent
$eloquent = new \Gzhegow\Orm\Package\Illuminate\Database\Capsule\Eloquent(
    $illuminateContainer
);

// > добавляем соединение к БД
$pdoCharset = 'utf8mb4';
$pdoCollate = 'utf8mb4_unicode_ci';
$eloquent->addConnection(
    [
        'driver' => 'mysql',

        'host'     => 'localhost',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'test',

        'charset'   => $pdoCharset,
        'collation' => $pdoCollate,

        'options' => [
            // > always throw an exception if any error occured
            \PDO::ATTR_ERRMODE           => \PDO::ERRMODE_EXCEPTION,
            //
            // > calculate $pdo->prepare() on PHP level instead of sending it to MySQL as is
            \PDO::ATTR_EMULATE_PREPARES  => true,
            //
            // > since (PHP_VERSION_ID > 80100) mysql integers return integer
            // > setting ATTR_STRINGIFY_FETCHES flag to TRUE forces returning numeric string
            \PDO::ATTR_STRINGIFY_FETCHES => true,
        ],
    ],
    $connName = 'default'
);

// > устанавливаем длину строки для новых таблиц по-умолчанию
\Illuminate\Database\Schema\Builder::$defaultStringLength = 150;

// > запускаем внутренние загрузочные действия Eloquent
$eloquent->bootEloquent();

// // > включаем логирование Eloquent
// // > создаем диспетчер для Eloquent (необходим для логирования, но не обязателен)
// $illuminateDispatcher = new \Illuminate\Events\Dispatcher(
//     $illuminateContainer
// );
// $eloquent->setEventDispatcher($illuminateDispatcher);
//
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

// > создаем Persistence для Eloquent
// > с помощью него будем откладывать выполнение запросов в очередь, уменьшая время одной транзакции
$eloquentPersistence = new \Gzhegow\Orm\Core\Persistence\EloquentPersistence(
    $eloquent
);

// > создаем фасад
$facade = new \Gzhegow\Orm\Core\OrmFacade(
    $factory,
    //
    $eloquent,
    $eloquentPersistence
);

// > устанавливаем фасад
\Gzhegow\Orm\Core\Orm::setFacade($facade);


$conn = $eloquent->getConnection();
$schema = $eloquent->getSchemaBuilder($conn);

$modelClassDemoBar = \Gzhegow\Orm\Demo\Model\DemoBarModel::class;
$modelClassDemoBaz = \Gzhegow\Orm\Demo\Model\DemoBazModel::class;
$modelClassDemoFoo = \Gzhegow\Orm\Demo\Model\DemoFooModel::class;
$modelClassDemoImage = \Gzhegow\Orm\Demo\Model\DemoImageModel::class;
$modelClassDemoPost = \Gzhegow\Orm\Demo\Model\DemoPostModel::class;
$modelClassDemoTag = \Gzhegow\Orm\Demo\Model\DemoTagModel::class;
$modelClassDemoUser = \Gzhegow\Orm\Demo\Model\DemoUserModel::class;

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
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoBar,
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
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
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
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
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
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
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoPost
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoUser,
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoUser
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableDemoTag,
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableDemoTag
    ) {
        $blueprint->bigIncrements('id');
        //
        $blueprint->string('name')->nullable();
    }
);

$schema->create(
    $tableTaggable,
    static function (\Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint $blueprint) use (
        $tableTaggable
    ) {
        $blueprint->bigInteger('tag_id')->nullable()->unsigned();
        //
        $blueprint->nullableMorphs('taggable');
    }
);



// >>> ТЕСТЫ

// > TEST
// > используем рекурсивное сохранение для того, чтобы сохранить модели вместе со связями
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 1 ]');
    echo PHP_EOL;


    $modelClassDemoFoo = \Gzhegow\Orm\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Orm\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Orm\Demo\Model\DemoBazModel::class;

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

    $ffn->print($fooCollection);
    $ffn->print($fooCollection[ 0 ]->id, $fooCollection[ 1 ]->id);

    $ffn->print($barCollection);
    $ffn->print($barCollection[ 0 ]->id, $barCollection[ 0 ]->demo_foo_id);
    $ffn->print($barCollection[ 1 ]->id, $barCollection[ 1 ]->demo_foo_id);

    $ffn->print($bazCollection);
    $ffn->print($bazCollection[ 0 ]->id, $bazCollection[ 0 ]->demo_bar_id);
    $ffn->print($bazCollection[ 1 ]->id, $bazCollection[ 1 ]->demo_bar_id);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 1 ]"

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "2"
{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "1"
"2" | "2"
{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "1"
"2" | "2"
');
$test->run();


// > TEST
// > используем Persistence для сохранения ранее созданных моделей
// > это нужно, чтобы уменьшить время транзакции - сохранение делаем в конце бизнес-действия
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 2 ]');
    echo PHP_EOL;


    $modelClassDemoFoo = \Gzhegow\Orm\Demo\Model\DemoFooModel::class;
    $modelClassDemoBar = \Gzhegow\Orm\Demo\Model\DemoBarModel::class;
    $modelClassDemoBaz = \Gzhegow\Orm\Demo\Model\DemoBazModel::class;

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


    \Gzhegow\Orm\Core\Orm::persistence()->flush();


    $fooCollection = $modelClassDemoFoo::query()->get([ '*' ]);
    $barCollection = $modelClassDemoBar::query()->get([ '*' ]);
    $bazCollection = $modelClassDemoBaz::query()->get([ '*' ]);

    $ffn->print($fooCollection);
    $ffn->print($fooCollection[ 0 ]->id, $fooCollection[ 1 ]->id);

    $ffn->print($barCollection);
    $ffn->print($barCollection[ 0 ]->id, $barCollection[ 0 ]->demo_foo_id);
    $ffn->print($barCollection[ 1 ]->id, $barCollection[ 1 ]->demo_foo_id);

    $ffn->print($bazCollection);
    $ffn->print($bazCollection[ 0 ]->id, $bazCollection[ 0 ]->demo_bar_id);
    $ffn->print($bazCollection[ 1 ]->id, $bazCollection[ 1 ]->demo_bar_id);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 2 ]"

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "2"
{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "1"
"2" | "2"
{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
"1" | "1"
"2" | "2"
');
$test->run();


// > TEST
// > тестирование связей (для примера взят Morph), у которых в этом пакете изменился интерфейс создания
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 3 ]');
    echo PHP_EOL;


    $modelClassDemoPost = \Gzhegow\Orm\Demo\Model\DemoPostModel::class;
    $modelClassDemoUser = \Gzhegow\Orm\Demo\Model\DemoUserModel::class;
    $modelClassDemoImage = \Gzhegow\Orm\Demo\Model\DemoImageModel::class;
    $modelClassDemoTag = \Gzhegow\Orm\Demo\Model\DemoTagModel::class;

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

    \Gzhegow\Orm\Core\Orm::persistence()->flush();


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

    $ffn->print($imageCollection);
    $ffn->print($imageCollection[ 0 ], $imageCollection[ 0 ]->_imageable);
    echo PHP_EOL;

    $ffn->print($postCollection);
    $ffn->print($postCollection[ 0 ], $postCollection[ 0 ]->_demoImages[ 0 ]);
    echo PHP_EOL;

    $ffn->print($userCollection);
    $ffn->print($userCollection[ 0 ], $userCollection[ 0 ]->_demoImages[ 0 ]);
    echo PHP_EOL;


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

    \Gzhegow\Orm\Core\Orm::persistence()->flush();


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

    $ffn->print($tagCollection);
    $ffn->print($tagCollection[ 0 ], $tagCollection[ 0 ]->_demoPosts[ 0 ], $tagCollection[ 0 ]->_demoUsers[ 0 ]);
    $ffn->print($tagCollection[ 1 ], $tagCollection[ 1 ]->_demoPosts[ 0 ], $tagCollection[ 1 ]->_demoUsers[ 0 ]);
    echo PHP_EOL;

    $ffn->print($postCollection);
    $ffn->print($postCollection[ 1 ], $postCollection[ 1 ]->_demoTags[ 0 ], $postCollection[ 1 ]->_demoTags[ 1 ]);
    echo PHP_EOL;

    $ffn->print($userCollection);
    $ffn->print($userCollection[ 1 ], $userCollection[ 1 ]->_demoTags[ 0 ], $userCollection[ 1 ]->_demoTags[ 1 ]);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 3 ]"

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoImageModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoPostModel }

{ object(countable(1) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoPostModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoImageModel }

{ object(countable(1) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoUserModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoImageModel }

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoPostModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoUserModel }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoPostModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoUserModel }

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoPostModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel }

{ object(countable(2) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(stringable) # Gzhegow\Orm\Demo\Model\DemoUserModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel } | { object(stringable) # Gzhegow\Orm\Demo\Model\DemoTagModel }
');
$test->run();


// > TEST
// > можно подсчитать количество записей в таблице используя EXPLAIN, к сожалению, будет показано число строк, которое придется обработать, а не число строк по результатам запроса
// > но иногда этого достаточно, особенно если запрос покрыт должным числом индексов, чтобы отобразить "Всего: ~100 страниц"
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 4 ]');
    echo PHP_EOL;


    $modelClassDemoTag = \Gzhegow\Orm\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    $query = $modelClassDemoTag::query()->where('name', 'tag77');
    $ffn->print($cnt = $query->count(), $cnt === 1);

    $cnt = $query->countExplain();
    $ffn->print($cnt > 1, $cnt <= 100);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 4 ]"

1 | TRUE
TRUE | TRUE
');
$test->run();


// > TEST
// > используем механизм Chunk, чтобы считать данные из таблиц
// > на базе механизма работает и пагинация, предлагается два варианта - нативный SQL LIMIT/OFFSET и COLUMN(>|>=|<|<=)VALUE
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 5 ]');
    echo PHP_EOL;


    $modelClassDemoTag = \Gzhegow\Orm\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    $ffn->print('chunkModelNativeForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder->chunksModelNativeForeach(
        $limitChunk = 25, $limit = null, $offset = null
    );
    foreach ( $builder->chunksForeach() as $chunk ) {
        $ffn->print($chunk);
    }
    echo PHP_EOL;


    $ffn->print('chunkModelAfterForeach');
    $builder = $modelClassDemoTag::chunks();
    $builder = $builder->chunksModelAfterForeach(
        $limitChunk = 25, $limit = null,
        $offsetColumn = 'id', $offsetOperator = '>', $offsetValue = 1, $includeOffsetValue = true
    );
    foreach ( $builder->chunksForeach() as $chunk ) {
        $ffn->print($chunk);
    }
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 5 ]"

"chunkModelNativeForeach"
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }

"chunkModelAfterForeach"
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
{ object(countable(25) iterable stringable) # Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection }
');
$test->run();


// > TEST
// > используем механизм Chunk, чтобы считать данные из таблиц
// > на базе механизма работает и пагинация, предлагается два варианта - нативный SQL LIMIT/OFFSET и COLUMN(>|>=|<|<=)VALUE
$fn = function () use (
    $eloquent,
    $schema,
    $ffn
) {
    $ffn->print('[ TEST 6 ]');
    echo PHP_EOL;


    $modelClassDemoTag = \Gzhegow\Orm\Demo\Model\DemoTagModel::class;

    $schema->disableForeignKeyConstraints();
    $modelClassDemoTag::query()->truncate();
    $schema->enableForeignKeyConstraints();

    for ( $i = 0; $i < 100; $i++ ) {
        $tag = $modelClassDemoTag::new();
        $tag->name = 'tag' . $i;
        $tag->save();
    }


    $ffn->print('paginateModelNativeForeach');
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
    $ffn->print_array_multiline((array) $result);
    $ffn->print_array_multiline($result->pagesAbsolute);
    $ffn->print_array_multiline($result->pagesRelative);
    echo PHP_EOL;


    $ffn->print('paginateModelAfterForeach');
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
    $ffn->print_array_multiline((array) $result);
    $ffn->print_array_multiline($result->pagesAbsolute);
    $ffn->print_array_multiline($result->pagesRelative);
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 6 ]"

"paginateModelNativeForeach"
###
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
  "items" => "{ object(countable(13) iterable stringable) # Illuminate\Support\Collection }"
]
###
###
[
  1 => 13,
  5 => 13,
  6 => 13,
  7 => 13,
  8 => 9
]
###
###
[
  "first" => 13,
  "previous" => 13,
  "current" => 13,
  "next" => NULL,
  "last" => 9
]
###

"paginateModelAfterForeach"
###
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
  "items" => "{ object(countable(13) iterable stringable) # Illuminate\Support\Collection }"
]
###
###
[
  1 => 13,
  5 => 13,
  6 => 13,
  7 => 13,
  8 => 9
]
###
###
[
  "first" => 13,
  "previous" => 13,
  "current" => 13,
  "next" => NULL,
  "last" => 9
]
###
');
$test->run();


// > TEST
// > рекомендуется в проекте указывать связи в виде callable, чтобы они менялись, когда применяешь `Refactor` в PHPStorm
$fn = function () use (
    $eloquent,
    $ffn
) {
    $ffn->print('[ TEST 7 ]');
    echo PHP_EOL;


    $foo_hasMany_bars_hasMany_bazs = \Gzhegow\Orm\Core\Orm::relationDot()
    ([ \Gzhegow\Orm\Demo\Model\DemoFooModel::class, '_demoBars' ])
    ([ \Gzhegow\Orm\Demo\Model\DemoBarModel::class, '_demoBazs' ])
    ();
    $ffn->print($foo_hasMany_bars_hasMany_bazs);

    $bar_belongsTo_foo = \Gzhegow\Orm\Demo\Model\DemoBarModel::relationDot()
    ([ \Gzhegow\Orm\Demo\Model\DemoBarModel::class, '_demoFoo' ])
    ();
    $ffn->print($bar_belongsTo_foo);

    $bar_hasMany_bazs = \Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel::relationDot()
    ([ \Gzhegow\Orm\Demo\Model\DemoBarModel::class, '_demoBazs' ])
    ();
    $ffn->print($bar_hasMany_bazs);

    $bar_belongsTo_foo_only_id = \Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel::relationDot()
    ([ \Gzhegow\Orm\Demo\Model\DemoBarModel::class, '_demoFoo' ], 'id')
    ();
    $ffn->print($bar_belongsTo_foo_only_id);

    $bar_hasMany_bazs_only_id = \Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel::relationDot()
    ([ \Gzhegow\Orm\Demo\Model\DemoBarModel::class, '_demoBazs' ], 'id')
    ();
    $ffn->print($bar_hasMany_bazs_only_id);

    // > ПРИМЕР
    // > Делаем запрос со связями
    // $query = \Gzhegow\Orm\Demo\Model\DemoFooModel::query();
    // $query->with($foo_hasMany_bars_hasMany_bazs);
    // $query->with([
    //     $foo_hasMany_bars_hasMany_bazs,
    // ]);
    // $query->with([
    //     $foo_hasMany_bars_hasMany_bazs => static function ($query) { },
    // ]);
    //
    // $query = \Gzhegow\Orm\Demo\Model\DemoBarModel::query();
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
    // $query = \Gzhegow\Orm\Demo\Model\DemoFooModel::query();
    // $model = $query->firstOrFail();
    // $model->load($foo_hasMany_bars_hasMany_bazs);
    // $model->load([
    //     $foo_hasMany_bars_hasMany_bazs,
    // ]);
    // $model->load([
    //     $foo_hasMany_bars_hasMany_bazs => static function ($query) { },
    // ]);
    //
    // $query = \Gzhegow\Orm\Demo\Model\DemoBarModel::query();
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
};
$test = $ffn->test($fn);
$test->expectStdout('
"[ TEST 7 ]"

"_demoBars._demoBazs"
"_demoFoo"
"_demoBazs"
"_demoFoo:id"
"_demoBazs:id"
');
$test->run();


// > удаляем таблицы после тестов
$schema->disableForeignKeyConstraints();
$schema->dropAllTables();
$schema->enableForeignKeyConstraints();
