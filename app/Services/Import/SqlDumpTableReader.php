<?php

namespace App\Services\Import;

use RuntimeException;

class SqlDumpTableReader
{
    public function readTable(string $path, string $table): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("SQL dump not found at [{$path}].");
        }

        $rows = [];
        $statement = '';
        $collecting = false;
        $needle = sprintf('INSERT INTO `%s`', $table);

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open SQL dump at [{$path}].");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                if (! $collecting && ! str_contains($line, $needle)) {
                    continue;
                }

                $collecting = true;
                $statement .= trim($line);

                if (! str_contains($line, ';')) {
                    continue;
                }

                $rows = [...$rows, ...$this->parseInsertStatement($statement)];
                $statement = '';
                $collecting = false;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    private function parseInsertStatement(string $statement): array
    {
        if (! preg_match('/^INSERT INTO `[^`]+` \((.*?)\) VALUES (.+);$/s', $statement, $matches)) {
            throw new RuntimeException('Unable to parse SQL insert statement.');
        }

        preg_match_all('/`([^`]*)`/', $matches[1], $columnMatches);
        $columns = $columnMatches[1];

        return array_map(
            fn (array $values) => array_combine($columns, $values),
            array_map(fn (string $tuple) => $this->parseTuple($tuple), $this->extractTuples($matches[2])),
        );
    }

    private function extractTuples(string $valueList): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $escaped = false;

        $length = strlen($valueList);

        for ($index = 0; $index < $length; $index++) {
            $character = $valueList[$index];

            if ($inString) {
                $buffer .= $character;

                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($character === '\\') {
                    $escaped = true;

                    continue;
                }

                if ($character === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($character === "'") {
                $inString = true;
                $buffer .= $character;

                continue;
            }

            if ($character === '(') {
                if ($depth > 0) {
                    $buffer .= $character;
                }

                $depth++;

                continue;
            }

            if ($character === ')') {
                $depth--;

                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';

                    continue;
                }

                $buffer .= $character;

                continue;
            }

            if ($depth === 0) {
                continue;
            }

            $buffer .= $character;
        }

        return $tuples;
    }

    private function parseTuple(string $tuple): array
    {
        $tokens = [];
        $buffer = '';
        $inString = false;
        $escaped = false;

        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];

            if ($inString) {
                $buffer .= $character;

                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($character === '\\') {
                    $escaped = true;

                    continue;
                }

                if ($character === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($character === "'") {
                $inString = true;
                $buffer .= $character;

                continue;
            }

            if ($character === ',') {
                $tokens[] = $this->decodeToken($buffer);
                $buffer = '';

                continue;
            }

            $buffer .= $character;
        }

        $tokens[] = $this->decodeToken($buffer);

        return $tokens;
    }

    private function decodeToken(string $token): mixed
    {
        $token = trim($token);

        if ($token === 'NULL') {
            return null;
        }

        if ($token !== '' && $token[0] === "'" && str_ends_with($token, "'")) {
            $value = substr($token, 1, -1);

            return strtr($value, [
                '\\0' => "\0",
                '\\n' => "\n",
                '\\r' => "\r",
                '\\t' => "\t",
                '\\Z' => "\x1A",
                "\\'" => "'",
                '\\"' => '"',
                '\\\\' => '\\',
            ]);
        }

        if (preg_match('/^-?\d+$/', $token) === 1) {
            return (int) $token;
        }

        if (preg_match('/^-?(?:\d+\.\d+|\d+)(?:e[+-]?\d+)?$/i', $token) === 1) {
            return (float) $token;
        }

        return $token;
    }
}
