<?php namespace Waka\Mailer\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Mailer\Models\WakaMail;

class WakaMailsImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $wakaMail = null;
            $id = $row['id'] ?? null;
            if($id) {
                $wakaMail = WakaMail::find($id);
            }
            if(!$wakaMail) {
                $wakaMail = new WakaMail();
            }
            $wakaMail->id = $row['id'] ?? null;
            $wakaMail->name = $row['name'] ?? null;
            $wakaMail->slug = $row['slug'] ?? null;
            $wakaMail->subject = $row['subject'] ?? null;
            $wakaMail->no_ds = $row['no_ds'] ?? null;
            $wakaMail->data_source = $row['data_source'] ?? null;
            $wakaMail->layout_id = $row['layout_id'] ?? null;
            $wakaMail->is_mjml = $row['is_mjml'] ?? null;
            $wakaMail->mjml = $row['mjml'] ?? null;
            $wakaMail->mjml_html = $row['mjml_html'] ?? null;
            $wakaMail->html = $row['html'] ?? null;
            $wakaMail->model_functions = json_decode($row['model_functions'] ?? null);
            $wakaMail->images = json_decode($row['images'] ?? null);
            $wakaMail->pjs = json_decode($row['pjs'] ?? null);
            $wakaMail->is_scope = $row['is_scope'] ?? null;
            $wakaMail->scopes = json_decode($row['scopes'] ?? null);
            $wakaMail->test_id = $row['test_id'] ?? null;
            $wakaMail->save();
        }
    }
}
