<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'cedicom:admin {email : Adresse e-mail du compte} {--name=Administrateur : Nom affiché}';

    protected $description = 'Crée (ou réinitialise) un compte administrateur du back office avec un mot de passe aléatoire';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        // Mot de passe généré ici et affiché une seule fois : il n'est stocké que haché.
        $password = Str::password(16, symbols: false);

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;
        $user->forceFill([
            'name' => $user->name ?: $this->option('name'),
            'password' => $password,
            'is_admin' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info(($existed ? 'Compte mis à jour' : 'Compte créé').' : '.$email);
        $this->line('Mot de passe (affiché une seule fois, à noter maintenant) : '.$password);
        $this->line('Connexion : '.url('/admin/login'));

        return self::SUCCESS;
    }
}
