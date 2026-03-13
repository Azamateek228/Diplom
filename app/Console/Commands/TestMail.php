<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Тест отправки почты';

    public function handle()
    {
        // Принудительно устанавливаем конфиг
        config(['mail.mailers.smtp.host' => 'localhost']);
        config(['mail.mailers.smtp.port' => 1025]);
        
        $this->info("📧 MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("📧 MAIL_PORT: " . config('mail.mailers.smtp.port'));
        
        Mail::raw('Тестовое письмо от Кино на колёсах', function($message) {
            $message->to('test@kinokolesa.ru')
                    ->subject('Тест почты')
                    ->from('test@kinokolesa.ru', 'Кино на колёсах');
        });
        
        $this->info('✅ Письмо отправлено!');
        $this->info('📧 Откройте http://localhost:8025 для просмотра');
        
        return 0;
    }
}
