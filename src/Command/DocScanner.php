<?php

namespace App\Command;

use Scoop\Command\Writer;

class DocScanner implements Command
{
    private $writer;

    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    public static function getName()
    {
        return 'scan:docs';
    }

    public function execute()
    {
        $files = glob("app/views/documentation/*.php");
        $index = array();
        foreach($files as $file) {
            $filename = basename($file, '.sdt.php');
            $content = file_get_contents($file);
            preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches, PREG_OFFSET_CAPTURE);
            $sections = $matches[0];
            for ($i = 0; $i < count($sections); $i++) {
                $fullTag = $sections[$i][0];
                $innerHtml = $matches[1][$i][0];
                preg_match('/id="([^"]+)"/i', $innerHtml, $idMatch);
                $id = isset($idMatch[1]) ? $idMatch[1] : '';
                $title = trim(strip_tags($innerHtml));
                $start = $sections[$i][1] + strlen($fullTag);
                $end = isset($sections[$i+1]) ? $sections[$i+1][1] : strlen($content);
                $sectionContent = substr($content, $start, $end - $start);
                if (!empty($title)) {
                    $index[] = [
                        'title'   => $title,
                        'file'    => $filename,
                        'anchor'  => $id,
                        'content' => preg_replace('/\s+/', ' ', trim(strip_tags($sectionContent)))
                    ];
                }
            }
        }
        if (file_put_contents('index.json', json_encode($index, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            $this->writer->write('<success:Indice generado con exito. ' . count($index) . ' secciones encontradas.!>');
        } else {
            $this->writer->write('<error:Error al escribir el archivo JSON. Revisa permisos de carpeta.!>');
        }
    }

    public function help()
    {
        $this->writer->write('Escanea el directorio <info:app/views/documentation!> y genera index.json para realizar busquedas dinamicas.');
    }
}
