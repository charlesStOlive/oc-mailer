<?php namespace Waka\Mailer\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Mailer\Models\Template;

class TemplatesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $template = new Template();
            $template->id = $row['id'] ?? null;
            $template->name = $row['name'] ?? null;
            $template->contenu = $row['contenu'] ?? null;
            $template->css = $row['css'] ?? null;
            $template->save();
        }
    }
}
