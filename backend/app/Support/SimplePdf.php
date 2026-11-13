<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

/**
 * Minimal dependency-free PDF writer for receipts, payslips and invoices.
 */
class SimplePdf
{
    private const PAGE_WIDTH = 595;

    private const PAGE_HEIGHT = 842;

    private const MARGIN_LEFT = 46;

    private const MARGIN_TOP = 800;

    private const MARGIN_BOTTOM = 60;

    /** @var array<int, string> */
    private array $pages = [];

    private string $stream = '';

    private float $y = self::MARGIN_TOP;

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function render(array $blocks): string
    {
        $this->pages = [];
        $this->stream = '';
        $this->y = self::MARGIN_TOP;

        foreach ($blocks as $block) {
            $this->renderBlock($block);
        }

        $this->newPage(false);

        return $this->build();
    }

    public function respond(string $pdf, string $filename): Response
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderBlock(array $block): void
    {
        $type = $block['type'] ?? 'text';

        match ($type) {
            'title' => $this->writeText((string) $block['text'], 20, true, 0, [0.06, 0.29, 0.73]),
            'subtitle' => $this->writeText((string) $block['text'], 11, false, 4, [0.35, 0.35, 0.35]),
            'heading' => $this->writeText((string) $block['text'], 13, true, 14, [0.1, 0.1, 0.1]),
            'text' => $this->writeText((string) $block['text'], 10, false, 4),
            'hr' => $this->separator(),
            'spacer' => $this->y -= (float) ($block['height'] ?? 10),
            'kv' => $this->writeKeyValues($block['rows'] ?? [], (int) ($block['columns'] ?? 2)),
            'table' => $this->writeTable($block),
            'total' => $this->writeTotal((string) $block['label'], (string) $block['value']),
            default => null,
        };
    }

    private function writeText(string $text, int $size, bool $bold, float $gapBefore, array $rgb = [0, 0, 0]): void
    {
        $this->y -= $gapBefore;
        [$r, $g, $b] = $rgb;

        $lines = explode("\n", wordwrap($this->ascii($text), (int) floor((self::PAGE_WIDTH - self::MARGIN_LEFT * 2) / ($size * 0.5)), "\n", true));

        foreach ($lines as $line) {
            $this->ensureSpace($size + 6);
            $this->stream .= sprintf(
                "BT %.3F %.3F %.3F rg /F%d %d Tf %.1F %.1F Td (%s) Tj ET\n",
                $r,
                $g,
                $b,
                $bold ? 2 : 1,
                $size,
                (float) self::MARGIN_LEFT,
                $this->y,
                $this->escape($line)
            );
            $this->y -= $size + 4;
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $rows
     */
    private function writeKeyValues(array $rows, int $columns): void
    {
        $columnWidth = (self::PAGE_WIDTH - self::MARGIN_LEFT * 2) / max(1, $columns);
        $index = 0;
        $lineHeight = 16;

        foreach ($rows as $row) {
            $column = $index % $columns;

            if ($column === 0) {
                $this->ensureSpace($lineHeight);
            }

            $x = self::MARGIN_LEFT + ($column * $columnWidth);
            $label = $this->ascii((string) ($row[0] ?? ''));
            $value = $this->ascii((string) ($row[1] ?? ''));

            $this->stream .= sprintf(
                "BT 0.45 0.45 0.45 rg /F1 9 Tf %.1F %.1F Td (%s) Tj ET\n",
                $x,
                $this->y,
                $this->escape($label)
            );
            $this->stream .= sprintf(
                "BT 0.1 0.1 0.1 rg /F2 10 Tf %.1F %.1F Td (%s) Tj ET\n",
                $x,
                $this->y - 10,
                $this->escape($value)
            );

            $index++;

            if ($column === $columns - 1 || $index === count($rows)) {
                $this->y -= $lineHeight + 6;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function writeTable(array $block): void
    {
        $headers = $block['headers'] ?? [];
        $rows = $block['rows'] ?? [];
        $widths = $block['widths'] ?? [];
        $totalWidth = array_sum($widths) ?: 1;
        $available = self::PAGE_WIDTH - self::MARGIN_LEFT * 2;

        $this->y -= 8;
        $this->ensureSpace(30);

        $x = (float) self::MARGIN_LEFT;
        foreach ($headers as $index => $header) {
            $size = $available * (($widths[$index] ?? 1) / $totalWidth);
            $this->stream .= sprintf(
                "BT 0.15 0.15 0.15 rg /F2 9 Tf %.1F %.1F Td (%s) Tj ET\n",
                $x + 2,
                $this->y,
                $this->escape($this->ascii((string) $header))
            );
            $x += $size;
        }

        $this->y -= 6;
        $this->separator();
        $this->y -= 10;

        foreach ($rows as $row) {
            $this->ensureSpace(20);
            $x = (float) self::MARGIN_LEFT;

            foreach (array_values($row) as $index => $cell) {
                $size = $available * (($widths[$index] ?? 1) / $totalWidth);
                $this->stream .= sprintf(
                    "BT 0.15 0.15 0.15 rg /F1 9 Tf %.1F %.1F Td (%s) Tj ET\n",
                    $x + 2,
                    $this->y,
                    $this->escape($this->ascii((string) $cell))
                );
                $x += $size;
            }

            $this->y -= 16;
            $this->stream .= sprintf("0.9 0.9 0.9 RG 0.4 w %.1F %.1F m %.1F %.1F l S\n", (float) self::MARGIN_LEFT, $this->y + 6, (float) (self::PAGE_WIDTH - self::MARGIN_LEFT), $this->y + 6);
        }
    }

    private function writeTotal(string $label, string $value): void
    {
        $this->ensureSpace(28);
        $this->y -= 8;
        $this->stream .= sprintf(
            "BT 0.1 0.1 0.1 rg /F2 12 Tf %.1F %.1F Td (%s) Tj ET\n",
            (float) (self::PAGE_WIDTH - self::MARGIN_LEFT - 220),
            $this->y,
            $this->escape($this->ascii($label))
        );
        $this->stream .= sprintf(
            "BT 0.06 0.29 0.73 rg /F2 12 Tf %.1F %.1F Td (%s) Tj ET\n",
            (float) (self::PAGE_WIDTH - self::MARGIN_LEFT - 90),
            $this->y,
            $this->escape($this->ascii($value))
        );
        $this->y -= 18;
    }

    private function separator(): void
    {
        $this->stream .= sprintf("0.85 0.85 0.85 RG 1 w %.1F %.1F m %.1F %.1F l S\n", (float) self::MARGIN_LEFT, $this->y, (float) (self::PAGE_WIDTH - self::MARGIN_LEFT), $this->y);
    }

    private function ensureSpace(float $needed): void
    {
        if ($this->y - $needed < self::MARGIN_BOTTOM) {
            $this->newPage();
        }
    }

    private function newPage(bool $onlyIfNeeded = true): void
    {
        if ($onlyIfNeeded && $this->stream === '') {
            return;
        }

        if ($this->stream === '' && ! $onlyIfNeeded) {
            $this->stream = '';
        }

        if (trim($this->stream) !== '') {
            $this->pages[] = $this->stream;
        }

        $this->stream = '';
        $this->y = self::MARGIN_TOP;
    }

    private function build(): string
    {
        $objects = [];
        $pageCount = max(1, count($this->pages));
        $kids = [];

        $fontRegular = 3 + ($pageCount * 2);
        $fontBold = $fontRegular + 1;

        foreach (range(1, $pageCount) as $index) {
            $kids[] = (3 + (($index - 1) * 2)).' 0 R';
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.$pageCount.' >>';

        foreach ($this->pages as $index => $stream) {
            $pageId = 3 + ($index * 2);
            $contentId = $pageId + 1;

            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::PAGE_WIDTH.' '.self::PAGE_HEIGHT.'] /Resources << /Font << /F1 '.$fontRegular.' 0 R /F2 '.$fontBold.' 0 R >> >> /Contents '.$contentId.' 0 R >>';
            $objects[$contentId] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';
        }

        $objects[$fontRegular] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[$fontBold] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $id => $content) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$content."\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= 'xref'."\n".'0 '.($maxId + 1)."\n"."0000000000 65535 f \n";

        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= isset($offsets[$id])
                ? str_pad((string) $offsets[$id], 10, '0', STR_PAD_LEFT)." 00000 n \n"
                : "0000000000 65535 f \n";
        }

        $pdf .= 'trailer'."\n".'<< /Size '.($maxId + 1).' /Root 1 0 R >>'."\n".'startxref'."\n".$xrefPosition."\n".'%%EOF';

        return $pdf;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $text);
    }

    private function ascii(string $text): string
    {
        $replaced = str_replace(
            ['₨', '₹', '€', '£', '¥', '₺', '₪', '₦', '₴', '₱', '฿', '₫', '₡', '₭'],
            ['Rs ', 'Rs ', 'EUR ', 'GBP ', 'JPY ', 'TRY ', 'ILS ', 'NGN ', 'UAH ', 'PHP ', 'THB ', 'VND ', 'CRC ', 'LAK '],
            $text
        );

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $replaced);

        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $replaced) : $converted;
    }
}
