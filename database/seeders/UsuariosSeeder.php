<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'nombre_usuario' => 'chef_maria',
                'email' => 'maria@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'María González',
                'bio' => 'Chef profesional con 15 años de experiencia',
                'foto_perfil' => 'https://images.pexels.com/photos/3338497/pexels-photo-3338497.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_usuario' => 'cocina_casera',
                'email' => 'juan@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'Juan Pérez',
                'bio' => 'Amante de la cocina tradicional española',
                'foto_perfil' => 'https://images.pexels.com/photos/8629131/pexels-photo-8629131.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_usuario' => 'veggie_lover',
                'email' => 'ana@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'Ana Martín',
                'bio' => 'Especialista en cocina vegetariana y vegana',
                'foto_perfil' => 'https://images.pexels.com/photos/3771106/pexels-photo-3771106.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_usuario' => 'dulce_tentacion',
                'email' => 'carlos@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'Carlos Ruiz',
                'bio' => 'Repostero creativo e innovador',
                'foto_perfil' => 'https://images.pexels.com/photos/3814446/pexels-photo-3814446.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_usuario' => 'cocinero_novato',
                'email' => 'lucia@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'Lucía Fernández',
                'bio' => 'Aprendiendo a cocinar paso a paso',
                'foto_perfil' => 'https://images.pexels.com/photos/4253302/pexels-photo-4253302.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_usuario' => 'masterchef_home',
                'email' => 'diego@email.com',
                'password' => Hash::make('password123'),
                'nombre_completo' => 'Diego Sánchez',
                'bio' => 'Cocinero amateur con grandes ambiciones',
                'foto_perfil' => 'https://images.pexels.com/photos/4253312/pexels-photo-4253312.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($usuarios as $usuario) {
            $usuarioId = DB::table('usuarios')->insertGetId($usuario);
            
            // Procesar la imagen si es una URL
            if (filter_var($usuario['foto_perfil'], FILTER_VALIDATE_URL)) {
                try {
                    // Incluir las funciones de procesamiento de imágenes
                    require_once __DIR__ . '/../../functions/api.php';
                    
                    $resultadoImagen = procesarImagenPerfil($usuario['foto_perfil'], $usuarioId);
                    
                    if ($resultadoImagen['success']) {
                        // Actualizar la ruta de la imagen en la base de datos
                        DB::table('usuarios')
                            ->where('id', $usuarioId)
                            ->update(['foto_perfil' => $resultadoImagen['filename']]);
                    }
                } catch (Exception $e) {
                    // Si falla el procesamiento, mantener la URL original
                    error_log("Error procesando imagen para usuario {$usuarioId}: " . $e->getMessage());
                }
            }
        }
    }
}
