<?php namespace Waka\Mailer\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Mailer\Models\WakaMail;

class WakaMailsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $wakaMail = new WakaMail();
            $wakaMail->id = $row['id'] ?? null;
            $wakaMail->name = $row['name'] ?? null;
            $wakaMail->slug = $row['slug'] ?? null;
            $wakaMail->subject = $row['subject'] ?? null;
            $wakaMail->data_source_id = $row['data_source_id'] ?? null;
            $wakaMail->layout_id = $row['layout_id'] ?? null;
            $wakaMail->is_mjml = $row['is_mjml'] ?? null;
            $wakaMail->mjml = $row['mjml'] ?? null;
            $wakaMail->mjml_html = $row['mjml_html'] ?? null;
            $wakaMail->html = $row['html'] ?? null;
            $wakaMail->model_functions = json_decode($row['model_functions'] ?? null);
            $wakaMail->images = json_decode($row['images'] ?? null);
            $wakaMail->save();
        }
    }
}
