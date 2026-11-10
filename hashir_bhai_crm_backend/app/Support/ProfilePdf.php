<?php

namespace App\Support;

class ProfilePdf
{
    private string $objects = '';

    private int $objectCount = 0;

    /** @var array<int, string> */
    private array $objectsList = [];

    public function build(array $fields, ?string $imagePath): string
    {
        $this->objectsList = [];
        $this->objectCount = 0;

        $catalog = $this->addObject('');
        $pages = $this->addObject('');
        $page = $this->addObject('');
        $content = $this->addObject('');
        $font = $this->addObject(
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'
        );
        $fontBold = $this->addObject(
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>'
        );

        $streamParts = [];
        $y = 780;

        $streamParts[] = 'BT /F2 20 Tf 50 '.$y.' Td (Profile) Tj ET';
        $y -= 28;
        $streamParts[] = '0.2 0.4 0.8 rg BT /F1 12 Tf 50 '.$y.' Td (Profile data export) Tj ET';
        $y -= 10;
        $streamParts[] = '0.85 0.85 0.85 RG 1 w 50 '.$y.' m 545 '.$y.' l S';
        $y -= 30;

        $imageObject = null;
        if ($imagePath !== null && is_file($imagePath)) {
            $imageData = @file_get_contents($imagePath);
            if ($imageData !== false) {
                $info = @getimagesize($imagePath);
                $width = $info[0] ?? 96;
                $height = $info[1] ?? 96;
                $drawW = 90;
                $drawH = max(40, min(90, $drawH = $drawW * ($height / max($width, 1))));
                $imgY = $y - $drawH;

                $xObject = $this->addObject('');
                $this->objectsList[$xObject - 1] =
                    '<< /Type /XObject /Subtype /Image /Width '.(int) $width.
                    ' /Height '.(int) $height.
                    ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($imageData).
                    " >>\nstream\n".$imageData."\nendstream";
                $imageObject = $xObject;

                $streamParts[] = 'q '.$drawW.' 0 0 '.$drawH.' 50 '.$imgY.' cm /Im0 Do Q';
                $textX = 160;
            }
        }

        $textX = $imageObject !== null ? 160 : 50;
        if ($imageObject === null) {
            $textYStart = $y;
        } else {
            $textYStart = $y;
        }

        $rowY = $textYStart;
        foreach ($fields as $label => $value) {
            $value = $this->sanitize((string) $value);
            $streamParts[] = 'BT /F2 11 Tf 50 '.$rowY.' Td ('.$this->escape($label).') Tj ET';
            $streamParts[] = 'BT /F1 11 Tf '.$textX.' '.$rowY.' Td ('.$value.') Tj ET';
            $rowY -= 18;
            if ($rowY < 60) {
                break;
            }
        }

        $stream = implode("\n", $streamParts);
        $this->objectsList[$content - 1] =
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";

        $xObjects = $imageObject !== null ? ' /XObject << /Im0 '.$imageObject.' 0 R >>' : '';
        $this->objectsList[$page - 1] =
            '<< /Type /Page /Parent '.$pages.' 0 R /MediaBox [0 0 595 842] '.
            '/Contents '.$content.' 0 R /Resources << /Font << /F1 '.$font.' 0 R /F2 '.$fontBold.' 0 R >>'.$xObjects.' >> >>';
        $this->objectsList[$pages - 1] = '<< /Type /Pages /Kids ['.$page.' 0 R] /Count 1 >>';
        $this->objectsList[$catalog - 1] = '<< /Type /Catalog /Pages '.$pages.' 0 R >>';

        return $this->render();
    }

    private function addObject(string $body): int
    {
        $this->objectsList[] = $body;

        return count($this->objectsList);
    }

    private function render(): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($this->objectsList as $index => $body) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$body."\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($this->objectsList) + 1;
        $pdf .= "xref\n0 ".$count."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size ".$count." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefPos."\n%%EOF";

        return $pdf;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private function sanitize(string $value): string
    {
        $value = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $value);
        if ($value === false || $value === null) {
            $value = preg_replace('/[^\x20-\x7E]/', '', (string) func_get_args()[0]) ?? '';
        }

        return $value;
    }
}
