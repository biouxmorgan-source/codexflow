<?php

namespace App\Support\Import;

use InvalidArgumentException;

/**
 * Lit un fichier CSV (séparateur ; , ou tabulation, UTF-8 ou Windows-1252) ou JSON (liste d'objets)
 * et le ramène à des en-têtes et des lignes de texte.
 */
final class TabularFile
{
    public const MAX_ROWS = 2000;

    /**
     * @param  list<string>  $headers
     * @param  list<array{line: int, cells: list<string>}>  $rows
     */
    public function __construct(public readonly array $headers, public readonly array $rows) {}

    public static function read(string $path, string $extension): self
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $table = strtolower($extension) === 'json' ? self::fromJson($content) : self::fromCsv($content);

        if ($table->headers === []) {
            throw new InvalidArgumentException('Le fichier ne contient aucune colonne.');
        }

        if ($table->rows === []) {
            throw new InvalidArgumentException('Le fichier ne contient aucune ligne de données.');
        }

        if (count($table->rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException('Le fichier dépasse '.self::MAX_ROWS.' lignes : découpez-le en plusieurs imports.');
        }

        return $table;
    }

    /**
     * @return list<string>
     */
    public function column(int $index): array
    {
        return array_map(fn (array $row) => $row['cells'][$index] ?? '', $this->rows);
    }

    private static function fromCsv(string $content): self
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = collect([';', ',', "\t"])->sortByDesc(fn ($candidate) => substr_count($firstLine, $candidate))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $headers = null;
        $rows = [];
        $line = 0;

        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $line++;
            $cells = array_map(fn ($cell) => trim((string) $cell), $cells);

            if (implode('', $cells) === '') {
                continue;
            }

            if ($headers === null) {
                $headers = $cells;

                continue;
            }

            $rows[] = ['line' => $line, 'cells' => array_slice(array_pad($cells, count($headers), ''), 0, count($headers))];
        }

        fclose($stream);

        return new self(self::cleanHeaders($headers ?? []), $rows);
    }

    private static function fromJson(string $content): self
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new InvalidArgumentException('Le fichier JSON est illisible : '.json_last_error_msg().'.');
        }

        // Accepte aussi { "fiches": [ … ] } : un objet qui contient une seule liste.
        if (! array_is_list($data) && count($data) === 1 && is_array(reset($data))) {
            $data = reset($data);
        }

        if (! array_is_list($data) || collect($data)->contains(fn ($item) => ! is_array($item) || array_is_list($item))) {
            throw new InvalidArgumentException('Le fichier JSON doit contenir une liste d\'objets, par exemple [{"Nom": "…"}].');
        }

        $headers = collect($data)->flatMap(fn (array $item) => array_keys($item))->map(fn ($key) => (string) $key)->unique()->values()->all();

        $rows = [];
        foreach ($data as $index => $item) {
            $rows[] = [
                'line' => $index + 1,
                'cells' => array_map(fn (string $header) => self::stringify($item[$header] ?? ''), $headers),
            ];
        }

        return new self(self::cleanHeaders($headers), $rows);
    }

    private static function stringify(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'oui' : 'non',
            is_array($value) => implode('|', array_map(fn ($item) => is_scalar($item) ? (string) $item : '', $value)),
            default => trim((string) $value),
        };
    }

    /**
     * @param  list<string>  $headers
     * @return list<string>
     */
    private static function cleanHeaders(array $headers): array
    {
        return array_map(fn ($header, $index) => mb_substr(trim($header), 0, 100) ?: 'Colonne '.($index + 1), $headers, array_keys($headers));
    }
}
