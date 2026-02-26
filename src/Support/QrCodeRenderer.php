<?php

namespace NinjaPortal\Shadow\Support;

use Throwable;

class QrCodeRenderer
{
    public function isAvailable(): bool
    {
        return class_exists(\chillerlan\QRCode\QRCode::class)
            && class_exists(\chillerlan\QRCode\QROptions::class)
            && interface_exists(\chillerlan\QRCode\Output\QROutputInterface::class);
    }

    public function renderSvgDataUri(?string $value, int $scale = 6): ?string
    {
        $payload = trim((string) $value);
        if ($payload === '' || ! $this->isAvailable()) {
            return null;
        }

        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\Output\QROutputInterface::MARKUP_SVG,
                'outputBase64' => true,
                'scale' => max(3, min(10, $scale)),
                'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
            ]);

            $result = (new \chillerlan\QRCode\QRCode($options))->render($payload);

            return is_string($result) && $result !== '' ? $result : null;
        } catch (Throwable) {
            return null;
        }
    }
}

