<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The app now runs on Pakistan time (config app.timezone = Asia/Karachi). Dates and times
 * saved before were UTC clock times, 5 hours behind, so every date-time column of every
 * table is moved forward 5 hours once. Date-only and time-only columns are not touched.
 *
 * It is dated before the other migrations of this day, so on a server that runs them in
 * one go it only moves the old UTC values, not the rows those migrations add.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->shift(5);
    }

    public function down(): void
    {
        $this->shift(-5);
    }

    protected function shift(int $hours): void
    {
        $connection = DB::connection();
        $sqlite = $connection->getDriverName() === 'sqlite';
        $schema = $sqlite ? null : $connection->getDatabaseName();

        foreach (Schema::getTableListing(schema: $schema, schemaQualified: false) as $table) {
            if ($table === 'migrations') {
                continue;
            }

            $columns = collect(Schema::getColumns($table))
                ->filter(fn (array $column) => in_array(strtolower($column['type_name']), ['timestamp', 'datetime'], true))
                ->pluck('name');

            if ($columns->isEmpty()) {
                continue;
            }

            // All columns of a table in one statement; NULL stays NULL.
            DB::table($table)->update($columns->mapWithKeys(fn (string $column) => [
                $column => DB::raw($sqlite
                    ? sprintf("datetime(\"%s\", '%+d hours')", $column, $hours)
                    : sprintf('DATE_ADD(`%s`, INTERVAL %d HOUR)', $column, $hours)),
            ])->all());
        }
    }
};
