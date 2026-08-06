<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversacionIA extends Model
{
    use HasFactory;

    protected $table = 'conversaciones_ia';

    protected $fillable = ['usuario_id', 'pregunta', 'respuesta'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
