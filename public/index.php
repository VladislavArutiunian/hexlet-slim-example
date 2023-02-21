<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\PhpRenderer;
use Symfony\Component\Validator\Constraints as Assert;

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');
    return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
});

$app->get('/users', function ($request, $response) use ($users) {
    $userNeedle = $request->getQueryParam('user');
    $filteredUsers = array_filter($users, fn ($user) => str_contains($user, $userNeedle));
    $params = ['users' => $filteredUsers];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->get('/courses/{courseId}', function ($request, $response, $args) {
    $courseId = $args['courseId'];
    return $response->write("Course id: {$courseId}");
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => '', 'password' => '', 'passwordConfirmation' => '', 'city' => ''],
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});

$app->post('/users/new', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $params = [
        'user' => $user,
    ];
    $id = \uniqid();
    $user['id'] = $id;
    $file = new File\File(dirname(__DIR__, 1) . '/logs/users.json');
    $file->save($user);
    return $response->withRedirect('/users');
});



//$app->post('/users/new', function ($request, $response) {
//    $user = $request->getParsedBodyParam('user');
//    $params = [
//        'user' => $user,
//    ];
//    $id = \uniqid();
////    $user['id'] = $id;
//    $rules = new Assert\Collection([
//        'nickname' => [
//            new Assert\NotBlank(),
//            new Assert\Length(min:5, max: 32, minMessage: 'Никнейм должен содержать минимум 5 символов')
//        ],
//        'email' => new Assert\Email()
//    ]);
//    $validator = \Symfony\Component\Validator\Validation::createValidator();
//    $errorsList = $validator->validate($user, $rules);
//    foreach ($errorsList as $error) {
//        throw new \Exception("{$error->getPropertyPath()}: {$error->getMessage()}");
//    }
//    $file = new File\File(dirname(__DIR__, 1) . '/logs/users.json');
//    $file->save($user);
//    return $this->get('renderer')->render($response, "users/new.phtml", $params);
//});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/courses', function ($request, $response) {
    $courses = [
        'ogo' => ['id' => 505, 'name' => 'Igor']
    ];
    $params = [
        'courses' => $courses
    ];
    return $this->get('renderer')->render($response, 'courses/show.phtml', $params);
});

$app->run();
