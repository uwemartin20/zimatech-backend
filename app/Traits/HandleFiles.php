<?php

namespace App\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;

trait HandleFiles
{
    public function createNewFile($filename, $type, $data=null) {
        $fullname = $filename .'.'. $type;
        switch ($type) {
            case "txt":
                file_put_contents($fullname, "");
                break;
            case "xlsx":
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $writer = new Xlsx($spreadsheet);
                $writer->save($fullname);
                break;
            case "ppt":
                $ppt = new PhpPresentation();
                $slide = $ppt->getActiveSlide();
                $shape = $slide->createRichTextShape()
                    ->setHeight(50)
                    ->setWidth(600)
                    ->setOffsetX(170)
                    ->setOffsetY(180);
                $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $textRun = $shape->createTextRun('New Presentation');
                $textRun->getFont()->setBold(true)->setSize(24);

                $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');
                $writer->save($fullname);
                break;
            default:
                throw new \Exception("Unsupported file type: $type");
        }
    }
}
