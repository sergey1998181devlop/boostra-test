<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require_once 'api/Simpla.php';

CONST API_PASS = 'R2DZpNj4bg9YDLJz5wm5PnfizAZE2YX01ffrWzESD5';


$simpla = new Simpla();

$password = $simpla->request->post('api_key');

$result = [];
$errors = array();

if (empty($password) || $password != API_PASS) {
    $result['success'] = false;
    $result['errors'] = ['Invalid API Password'];
} else {
    $user_id = $simpla->request->post('user_id');

    switch ($simpla->request->post('action', 'string')):

        case 'createOrUpdate':

            try {
                $user = new StdClass();

                $user->role = $simpla->request->post('role');
                $user->name = $simpla->request->post('name');
                $user->name_1c = $simpla->request->post('name_1c');
                $user->login = $simpla->request->post('login');
                $user->mango_number = $simpla->request->post('mango_number');

                if ($simpla->request->post('password'))
                    $user->password = $simpla->request->post('password');



                if (empty($user->role))
                    $errors[] = 'Не передана роль';
                if (empty($user->name))
                    $errors[] = 'Не передано имя';
                if (empty($user->login)) {
                    $errors[] = 'Не передан логин';
                } else {
                    $manager = $simpla->managers->getManagerBy('login', $user->login);
                    if (!empty($manager)) {
                        $errors[] = "Менеджер с логином {$user->login} уже существует. ID {$manager->id}";
                    }
                }

                if (empty($user->password))
                    $errors[] = "Не передан пароль";


                if (empty($errors))
                {
                    if (empty($user_id))
                    {
                        $user->id = $simpla->managers->add_manager($user);

                        $result['text'] = "Менеджер $user->id создан";
                        $result['success'] = true;

                        $simpla->changelogs->add_changelog(array(
                            'manager_id' => $user->id,
                            'created' => date('Y-m-d H:i:s'),
                            'type' => 'create_manager',
                            'old_values' => '',
                            'new_values' => '(АПИ) Создан менеджер: '.$user->id,
                            'order_id' => 0,
                            'user_id' => 0,
                        ));

                    } else {
                        $user->id = $simpla->managers->update_manager($user_id, $user);
                        $result['text'] = "Менеджер $user_id обновлён";
                        $result['success'] = true;
                    }
                } else {
                    $result['success'] = false;
                    $result['errors'] = $errors;
                }
            } catch (Throwable $e) {
                $result['success'] = false;
                $result['errors'] = [$e->getMessage()];
            }

            break;
        case 'block':

            try {
                $login = $simpla->request->post('login');

                if (empty($user_id) && empty($login))
                    $errors[] = 'Не передан ID или логин пользователя';

                if (!empty($errors)) {
                    $result['success'] = false;
                    $result['errors'] = $errors;
                } else {

                    if (empty($user_id)) {
                        $manager = $simpla->managers->getManagerBy('login', $login);
                    } else {
                        $manager = $simpla->managers->get_manager($user_id);
                    }

                    if (empty($manager)) {
                        $result['success'] = false;
                        $result['errors'] = ["Менеджер с ID $user_id не найден!"];
                    } else {
                        $user = new StdClass();
                        $user->blocked = 1;
                        $simpla->managers->update_manager($manager->id, $user);

                        $simpla->changelogs->add_changelog(array(
                            'manager_id' => $user_id,
                            'created' => date('Y-m-d H:i:s'),
                            'type' => 'create_manager',
                            'old_values' => '',
                            'new_values' => '(АПИ) Заблокирован менеджер: '.$manager->id,
                            'order_id' => 0,
                            'user_id' => 0,
                        ));

                        $result['success'] = true;
                        $result['message'] = "Менеджер {$manager->login} заблокирован!";
                    }
                }
            } catch (Throwable $e) {
                $result['success'] = false;
                $result['errors'] = [$e->getMessage()];
            }

            break;
        case 'get':
            try {
                $filteredManagers = [];
                $managers = $simpla->managers->get_managers();

                foreach ($managers as $manager) {
                    $filteredManagers[] = [
                        'login' => $manager->login,
                        'name' => $manager->name,
                        'role' => $manager->role,
                        'last_visit' => $manager->last_visit,
                    ];
                }

                $result['success'] = true;
                $result['managers'] = $filteredManagers;
            } catch (Throwable $e) {
                $result['success'] = false;
                $result['errors'] = [$e->getMessage()];
            }
            break;
        default:
            $result['error'] = 'undefined_action';
    endswitch;
}

$simpla->response->json_output($result);
