<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nombre', 'slug'];

    /** 1:N — Una categoría agrupa muchas vacantes. */
    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class);
    }
}
