<?php
namespace Vgsite\HTTP;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Vgsite\API\Exceptions\APIException;

/**
 * Trait implementing functionality common to requests and responses.
 */
trait MessageTrait
{
    /** @var array Map of all registered headers: [name => key] */
    private $headers = [];

    /** @var string */
    private $protocol = '1.1';

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function setProtocolVersion($version): self
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $this->protocol = $version;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $key): bool
    {
        return array_key_exists($key, $this->headers);
    }

    public function getHeader(string $key): ?string
    {
        if (! $this->hasHeader($key)) {
            return null;
        }

        return $this->headers[$key];
    }

    public function setHeader(string $key, string $value): self
    {
        $this->assertHeader($key);
        $value = $this->normalizeHeaderValue($value);

        $this->headers[$key] = $value;

        return $this;
    }

    private function setHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            if (is_int($key)) {
                // Numeric array keys are converted to int by PHP but having a header name '123' is not forbidden by the spec
                // and also allowed in withHeader(). So we need to cast it to string again for the following assertion to pass.
                $key = (string) $key;
            }
            $this->setHeader($key, $value);
        }

        return $this;
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string $value Header value
     *
     * @return string Trimmed header value
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function normalizeHeaderValue(string $value): string
    {
        if (!is_scalar($value) && null !== $value) {
            throw new \InvalidArgumentException(sprintf(
                'Header value must be scalar or null but %s provided.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return trim((string) $value, " \t");
    }

    private function assertHeader($header)
    {
        if (!is_string($header)) {
            throw new \InvalidArgumentException(sprintf(
                'Header name must be a string but %s provided.',
                is_object($header) ? get_class($header) : gettype($header)
            ));
        }

        if ($header === '') {
            throw new \InvalidArgumentException('Header name can not be empty.');
        }
    }

    public function renderHeader($key): ?string
    {
        if (! $value = $this->getHeader($key)) {
            return null;
        }

        return "{$key}: {$value}";
    }

    public function parseRange(string $range): array
    {
        if (!preg_match('/^items=(\d*)-(\d*)$/', $range, $matches)) {
            throw new APIException('Range expected to be in the following format: items=min-max', null, 'INVALID_RANGE_FORMAT', 416);
        }

        $start = $matches[1];
        $end = $matches[2];

        if ($start > $end) {
            throw new APIException('Range start is higher than end', null, 'INVALID_RANGE_FORMAT', 416);
        }

        return [$start, $end];
    }
}
