<?php namespace Waka\Mailer\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Mailer\Models\Bloc;

class BlocsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $bloc = new Bloc();
            $bloc->id = $row['id'] ?? null;
            $bloc->is_mjml = $row['is_mjml'] ?? null;
            $bloc->name = $row['name'] ?? null;
            $bloc->slug = $row['slug'] ?? null;
            $bloc->contenu = $row['contenu'] ?? null;
            $bloc->copy = $row['copy'] ?? null;
            $bloc->save();
        }
    }
}
