<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Containers;

use DateInterval;
use DateTime;

class EsiResponse implements \Seat\Services\Contracts\EsiResponse
{
    /**
     * @var array
     */
    private array $headers;

    /**
     * @var int
     */
    private int $status_code;

    /**
     * @var object|array
     */
    private object|array $body;

    /**
     * @var bool
     */
    private bool $cached;

    /**
     * @param  string  $data
     * @param  array  $headers
     * @param  int  $response_code
     * @param  bool  $cached
     */
    public function __construct(string $data, array $headers, int $response_code, bool $cached)
    {
        $this->headers = $headers;
        $this->status_code = $response_code;
        $this->cached = $cached;

        $json = json_decode($data);

        if (! is_object($json) && ! is_array($json))
            $json = (object) $data;

        $this->body = $json;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param  string  $name
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : [];
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function expired(): bool
    {
        $now = new DateTime();
        $now->sub(new DateInterval('PT1M'));

        $expires = new DateTime($this->getHeaderLine('Expires'));

        return $now->diff($expires)->invert === 1;
    }

    /**
     * @return int|null
     */
    public function getPagesCount(): ?int
    {
        return (int) $this->getHeader('X-Pages') ?: null;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status_code >= 400;
    }

    /**
     * @return object|array
     */
    public function getBody(): object|array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function error(): string
    {
        $error = property_exists($this->body, 'error') ? $this->body->error : '';
        $error .= property_exists($this->body, 'error_description') ? ": {$this->body->error_description}" : '';

        return $error;
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return $this->cached;
    }
}
