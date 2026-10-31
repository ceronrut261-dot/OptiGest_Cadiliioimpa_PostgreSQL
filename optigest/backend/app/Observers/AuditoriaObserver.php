<?php

namespace App\Observers;

use App\Models\AuditoriaLog;
use App\Models\Cotizacion;
use App\Models\Material;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditoriaObserver
{
    /**
     * Conserva los valores anteriores por cada instancia del modelo.
     */
    private static ?\WeakMap $valoresAnteriores = null;

    private static function obtenerMapa(): \WeakMap
    {
        if (self::$valoresAnteriores === null) {
            self::$valoresAnteriores = new \WeakMap();
        }

        return self::$valoresAnteriores;
    }

    /**
     * Guarda los valores anteriores antes de actualizar.
     */
    public function updating(Model $model): void
    {
        $anteriores = [];

        foreach (array_keys($model->getDirty()) as $campo) {
            $anteriores[$campo] = $model->getRawOriginal($campo);
        }

        self::obtenerMapa()[$model] = $anteriores;
    }

    /**
     * Registra la creación de un registro.
     */
    public function created(Model $model): void
    {
        $this->registrar(
            'crear',
            $model,
            null,
            $this->obtenerValores($model)
        );
    }

    /**
     * Registra actualizaciones, activaciones y desactivaciones.
     */
    public function updated(Model $model): void
    {
        $mapa = self::obtenerMapa();

        $anteriores = $mapa[$model] ?? [];

        unset($mapa[$model]);

        $nuevos = $model->getChanges();

        unset($nuevos['updated_at']);

        if (empty($nuevos)) {
            return;
        }

        $anterioresFiltrados = [];

        foreach (array_keys($nuevos) as $campo) {
            $anterioresFiltrados[$campo] =
                $anteriores[$campo] ?? null;
        }

        unset($anterioresFiltrados['updated_at']);

        $accion = 'actualizar';

        if (
            $model instanceof Material
            && array_key_exists('activo', $nuevos)
            && array_key_exists('activo', $anterioresFiltrados)
        ) {
            $valorAnterior = filter_var(
                $anterioresFiltrados['activo'],
                FILTER_VALIDATE_BOOLEAN
            );

            $valorNuevo = filter_var(
                $nuevos['activo'],
                FILTER_VALIDATE_BOOLEAN
            );

            if ($valorAnterior && !$valorNuevo) {
                $accion = 'desactivar';
            } elseif (!$valorAnterior && $valorNuevo) {
                $accion = 'activar';
            }
        }

        $this->registrar(
            $accion,
            $model,
            $anterioresFiltrados,
            $nuevos
        );
    }

    /**
     * Registra la eliminación de un registro.
     */
    public function deleted(Model $model): void
    {
        $this->registrar(
            'eliminar',
            $model,
            $this->obtenerValores($model),
            null
        );
    }

    /**
     * Guarda la información en la bitácora.
     */
    private function registrar(
        string $accion,
        Model $model,
        ?array $anteriores,
        ?array $nuevos
    ): void {
        $camposSensibles = [
            'password',
            'remember_token',
            'api_token',
        ];

        if ($anteriores !== null) {
            $anteriores = array_diff_key(
                $anteriores,
                array_flip($camposSensibles)
            );
        }

        if ($nuevos !== null) {
            $nuevos = array_diff_key(
                $nuevos,
                array_flip($camposSensibles)
            );
        }

        $request = app()->bound('request')
            ? request()
            : null;

        $nombreModelo = class_basename($model);

        $identificador = $nombreModelo
            . ' #' . $model->getKey();

        if ($model instanceof Material) {
            $nombreMaterial = $model->nombre ?? 'Material';
            $codigoMaterial = $model->codigo ?? $model->getKey();

            $identificador = $nombreMaterial
                . ' (' . $codigoMaterial . ')';
        } elseif ($model instanceof Cotizacion) {
            $identificador = 'Cotización #'
                . $model->getKey();
        }

        $descripcion = match ($accion) {
            'crear' => match (true) {
                $model instanceof Material =>
                    'Material creado: ' . $identificador,

                $model instanceof Cotizacion =>
                    'Cotización creada: ' . $identificador,

                default =>
                    $nombreModelo . ' creado: ' . $identificador,
            },

            'actualizar' => match (true) {
                $model instanceof Material =>
                    'Material actualizado: ' . $identificador,

                $model instanceof Cotizacion =>
                    'Cotización actualizada: ' . $identificador,

                default =>
                    $nombreModelo . ' actualizado: ' . $identificador,
            },

            'activar' =>
                'Material activado: ' . $identificador,

            'desactivar' =>
                'Material desactivado: ' . $identificador,

            'eliminar' => match (true) {
                $model instanceof Material =>
                    'Material eliminado: ' . $identificador,

                $model instanceof Cotizacion =>
                    'Cotización eliminada: ' . $identificador,

                default =>
                    $nombreModelo . ' eliminado: ' . $identificador,
            },

            default =>
                $nombreModelo . ' - acción: ' . $accion,
        };

        AuditoriaLog::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'modulo' => $nombreModelo,
            'registro_tipo' => $nombreModelo,
            'registro_id' => $model->getKey(),
            'descripcion' => $descripcion,
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $nuevos,
            'ip_address' => $request
                ? $request->ip()
                : null,
            'user_agent' => $request
                ? substr(
                    (string) $request->userAgent(),
                    0,
                    2000
                )
                : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Obtiene los atributos actuales sin incluir credenciales sensibles.
     */
    private function obtenerValores(Model $model): array
    {
        $valores = $model->getAttributes();

        unset(
            $valores['password'],
            $valores['remember_token'],
            $valores['api_token']
        );

        return $valores;
    }
}
