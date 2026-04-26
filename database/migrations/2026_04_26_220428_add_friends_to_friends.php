<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = <<<SQL
        ALTER TABLE friends ADD COLUMN friends BIGINT[] NOT NULL,
        ADD CONSTRAINT unique_friends CHECK (array_length(friends, 1) = 2);
        SQL;
        DB::statement($sql);

        $sql2 = <<<SQL
        -- Уникальность через триггер или функцию
        CREATE OR REPLACE FUNCTION normalize_friends(friends BIGINT[]) 
        RETURNS INT[] AS $$
        BEGIN
            RETURN ARRAY[LEAST(friends[1], friends[2]), GREATEST(friends[1], friends[2])];
        END;
        $$ LANGUAGE plpgsql IMMUTABLE;
        SQL;
        DB::statement($sql2);

        $sql3 = <<<SQL
        -- Уникальный индекс
        CREATE UNIQUE INDEX idx_unique_friends ON friends ((normalize_friends(friends)));
        SQL;
        DB::statement($sql3);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_unique_friends;');
        DB::statement('DROP FUNCTION IF EXISTS normalize_friends(BIGINT[]);');
        DB::statement('ALTER TABLE friends DROP CONSTRAINT IF EXISTS unique_friends, DROP COLUMN IF EXISTS friends;');
    }
};