<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; 

class CreateLibrarian extends Command
{
    /**
     * Название и сигнатура команды.
     *
     * @var string
     */
    protected $signature = 'librarian:create';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Создать нового библиотекаря';

    /**
     * Выполнение команды.
     */
    public function handle()
    {
        $name = $this->ask('Введите имя библиотекаря:');
        $email = $this->ask('Введите email библиотекаря:');
        $password = $this->secret('Введите пароль библиотекаря:');
        $passwordConfirmation = $this->secret('Повторите пароль библиотекаря:');

        if ($password !== $passwordConfirmation) {
            $this->error('Пароли не совпадают.');
            return 1;
        }

        // Проверка, существует ли пользователь с таким email
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->error('Пользователь с таким email уже существует.');

            // Предложение сменить роль или отменить регистрацию
            if ($this->confirm('Хотите сменить роль этого пользователя на библиотекаря?')) {
                $user->role = UserRole::LIBRARIAN;
                $user->save();
                $this->info("Роль пользователя {$user->email} изменена на библиотекаря.");

                // Генерация токена для существующего пользователя
                $token = JWTAuth::fromUser($user);
                $this->info("Токен для пользователя: " . $token);

                return 0;
            }

            $this->info('Регистрация отменена.');
            return 1;
        }

        // Создание нового библиотекаря
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => UserRole::LIBRARIAN,
            ]);

            $this->info('Библиотекарь успешно создан!');

            // Генерация токена для нового пользователя
            $token = JWTAuth::fromUser($user);
            $this->info("Токен для пользователя: " . $token);

            return 0;
        } catch (\Exception $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return 1;
        }
    }
}