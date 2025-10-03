<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertamos el registro en tabla datos
        $idDato = DB::table('datos')->insertGetId([
            'nombre' => 'Carlos',
            'apellidoPaterno' => 'Guevara',
            'apellidoMaterno' => 'Sosa',
            'apellidoConyuge' => null,
            'estadoCivil' => 'Soltero',
            'sexo' => 'Masculino',
            'dni' => '67856473',
            'fechaNacimiento'=> '2003-11-08',
            'fechaCaducidadDni' => '2029-09-14',
            'nacionalidad' => 'Peruana',
            'residePeru' => 1,
            'nivelEducativo' => 'Superior',
            'profesion' => 'Ing. Sofware',
            'enfermedadesPreexistentes' => 0,
            'ruc' => null,
            'expuestaPoliticamente' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Insertamos el usuario con idDato relacionado
        DB::table('usuarios')->insert([
            'username' => 'carlosguevara',
            'password' => Hash::make('123456'),
            'id_Datos' => $idDato,
            'id_Rol' => 1, // Rol Admin
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
