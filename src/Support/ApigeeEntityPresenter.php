<?php

namespace NinjaPortal\Shadow\Support;

use DateTimeInterface;
use JsonSerializable;

class ApigeeEntityPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function presentApp(mixed $app): array
    {
        $raw = $this->normalize($app);

        $name = $this->read($app, $raw, ['getName', 'name']) ?? $this->read($app, $raw, ['displayName']);
        $displayName = $this->read($app, $raw, ['getDisplayName', 'displayName']) ?? $name;
        $status = $this->read($app, $raw, ['getStatus', 'status']);
        $callbackUrl = $this->read($app, $raw, ['getCallbackUrl', 'callbackUrl']);
        $appId = $this->read($app, $raw, ['getAppId', 'appId', 'app_id']);
        $credentials = $this->read($app, $raw, ['getCredentials', 'credentials']) ?? [];
        $apiProducts = $this->read($app, $raw, ['getApiProducts', 'apiProducts', 'initialApiProducts']) ?? [];

        return [
            'name' => is_string($name) ? $name : null,
            'display_name' => is_string($displayName) ? $displayName : null,
            'status' => is_string($status) ? $status : null,
            'callback_url' => is_string($callbackUrl) ? $callbackUrl : null,
            'app_id' => is_string($appId) ? $appId : null,
            'api_products' => $this->normalizeProducts($apiProducts),
            'credentials' => collect(is_array($credentials) ? $credentials : [])->map(fn ($credential) => $this->presentCredential($credential))->values()->all(),
            'raw' => is_array($raw) ? $raw : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentCredential(mixed $credential): array
    {
        $raw = $this->normalize($credential);

        $key = $this->read($credential, $raw, ['getConsumerKey', 'consumerKey', 'consumer_key']);
        $secret = $this->read($credential, $raw, ['getConsumerSecret', 'consumerSecret', 'consumer_secret']);
        $status = $this->read($credential, $raw, ['getStatus', 'status']);
        $issuedAt = $this->read($credential, $raw, ['getIssuedAt', 'issuedAt', 'issued_at']);
        $expiresAt = $this->read($credential, $raw, ['getExpiresAt', 'expiresAt', 'expires_at']);
        $apiProducts = $this->read($credential, $raw, ['getApiProducts', 'apiProducts', 'api_products']) ?? [];

        return [
            'key' => is_string($key) ? $key : null,
            'key_short' => is_string($key) ? $this->shorten($key) : null,
            'secret' => is_string($secret) ? $secret : null,
            'secret_short' => is_string($secret) ? $this->shorten($secret) : null,
            'status' => is_string($status) ? $status : null,
            'issued_at' => $this->normalizeDate($issuedAt),
            'expires_at' => $this->normalizeDate($expiresAt),
            'api_products' => $this->normalizeProducts($apiProducts),
            'raw' => is_array($raw) ? $raw : [],
        ];
    }

    public function normalize(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalize($value->jsonSerialize());
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalize($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                /** @var mixed $toArray */
                $toArray = $value->toArray();

                return $this->normalize($toArray);
            }

            return $this->normalize(get_object_vars($value));
        }

        return $value;
    }

    protected function read(mixed $source, mixed $raw, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (is_object($source) && method_exists($source, $key)) {
                try {
                    return $source->{$key}();
                } catch (\Throwable) {
                    // Ignore unsupported getters and continue to raw data.
                }
            }

            if (is_array($raw) && array_key_exists($key, $raw)) {
                return $raw[$key];
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeProducts(mixed $products): array
    {
        $items = is_array($products) ? $products : [];

        return collect($items)->map(function ($item) {
            $raw = $this->normalize($item);
            $name = is_array($raw) ? ($raw['apiproduct'] ?? $raw['apiProduct'] ?? $raw['name'] ?? $raw['product'] ?? null) : null;
            $status = is_array($raw) ? ($raw['status'] ?? null) : null;

            return [
                'name' => is_string($name) ? $name : null,
                'status' => is_string($status) ? $status : null,
                'raw' => is_array($raw) ? $raw : ['value' => $raw],
            ];
        })->values()->all();
    }

    protected function shorten(string $value, int $prefix = 6, int $suffix = 4): string
    {
        if (strlen($value) <= ($prefix + $suffix + 3)) {
            return $value;
        }

        return substr($value, 0, $prefix).'...'.substr($value, -$suffix);
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return null;
    }
}
