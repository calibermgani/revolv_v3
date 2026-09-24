<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompletedUserEditablesReworkLookupIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('completed_user_editables')) {
            return;
        }
        DB::statement('ALTER TABLE completed_user_editables ADD INDEX cue_rework_window_idx (project_id(64), sub_project_id(64), record_status(32), rework_status(8), start_time(19))');
        DB::statement('ALTER TABLE completed_user_editables ADD INDEX cue_record_lookup_idx (project_id(64), sub_project_id(64), record_id(64), record_status(32))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('completed_user_editables')) {
            return;
        }
        DB::statement('ALTER TABLE completed_user_editables DROP INDEX cue_rework_window_idx');
        DB::statement('ALTER TABLE completed_user_editables DROP INDEX cue_record_lookup_idx');
    }
}
