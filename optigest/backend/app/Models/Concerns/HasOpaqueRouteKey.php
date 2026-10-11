<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * URLs "encapsuladas": en lugar de /tickets/5 la direccion pasa a ser algo
 * como /tickets/Xk3v9QpL0aZr7mWc2uYt1g. El id real nunca se muestra.
 *
 * - Cifra el id con AES-256-CBC usando una llave derivada de APP_KEY.
 * - No requiere paquetes nuevos ni cambios en la base de datos.
 * - Es una capa EXTRA: la proteccion real contra IDOR sigue siendo
 *   $this->authorize(...) en cada controlador.
 *
 * Uso: agregar `use HasOpaqueRouteKey;` dentro del modelo (Ticket,
 * Cotizacion, SalidaMaterial, Cliente, TicketGasto...). Los enlaces creados
 * con route('tickets.show', $ticket) se codifican solos; si algun enlace se
 * arma a mano con $ticket->id, hay que cambiarlo por route(...).
 */
trait HasOpaqueRouteKey
{
    public function getRouteKey(): string
    {
        return self::opacoCodificar((int) $this->getKey());
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        // Si se pide un campo especifico (ej. {modelo:slug}) se respeta.
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $id = self::opacoDecodificar((string) $value);

        return $id === null ? null : $this->where($this->getKeyName(), $id)->first();
    }

    private static function opacoLlave(): string
    {
        return hash_hmac('sha256', static::class, config('app.key'), true); // 32 bytes = AES-256
    }

    private static function opacoIv(): string
    {
        return substr(hash_hmac('sha256', 'iv:'.static::class, config('app.key'), true), 0, 16);
    }

    public static function opacoCodificar(int $id): string
    {
        $cifrado = openssl_encrypt((string) $id, 'aes-256-cbc', self::opacoLlave(), OPENSSL_RAW_DATA, self::opacoIv());

        return rtrim(strtr(base64_encode($cifrado), '+/', '-_'), '=');
    }

    public static function opacoDecodificar(string $token): ?int
    {
        if ($token === '' || strlen($token) > 64 || ! preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
            return null;
        }

        $binario = base64_decode(strtr($token, '-_', '+/'), true);

        if ($binario === false) {
            return null;
        }

        $claro = openssl_decrypt($binario, 'aes-256-cbc', self::opacoLlave(), OPENSSL_RAW_DATA, self::opacoIv());

        return ($claro !== false && ctype_digit($claro)) ? (int) $claro : null;
    }
}
