<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    // Libera todos os campos para serem salvos em massa
    protected $guarded = [];
}
