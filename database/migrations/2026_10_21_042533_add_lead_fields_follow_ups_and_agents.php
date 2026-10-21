<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lead form fields the admin can add and relabel, a follow-up history per lead, and the
     * "My Leads" permission with an Agent role for salespeople who add their own leads.
     */
    public function up(): void
    {
        Schema::create('lead_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 150);
            $table->string('type', 20)->default('text');
            $table->string('placeholder', 150)->nullable();
            $table->string('help')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('show_in_list')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->json('extra')->nullable()->after('message');
            $table->foreignId('created_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
        });

        Schema::create('lead_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('done_at')->nullable();
            $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('outcome')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'done_at']);
            $table->index('due_date');
        });

        $now = now();

        // Built-in fields (columns on the leads table) plus a "Location" field to start with.
        $fields = [
            ['name', 'Full name', 'text', true, true, false],
            ['phone', 'Phone / WhatsApp', 'phone', true, true, false],
            ['location', 'Location (city / area)', 'text', false, false, true],
            ['email', 'Email', 'email', true, false, false],
            ['plot_id', 'Plot size', 'select', true, false, false],
            ['budget', 'Budget', 'text', true, false, false],
            ['payment_preference', 'Payment preference', 'select', true, false, false],
            ['preferred_contact_method', 'Contact by', 'select', true, false, false],
            ['preferred_time', 'Preferred time', 'text', true, false, false],
            ['message', 'Message / requirement', 'textarea', true, false, false],
        ];

        foreach ($fields as $order => [$key, $label, $type, $system, $required, $inList]) {
            DB::table('lead_fields')->insertOrIgnore([
                'key' => $key, 'label' => $label, 'type' => $type, 'is_system' => $system, 'is_required' => $required,
                'show_in_list' => $inList, 'is_active' => true, 'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // The single follow-up date each lead had becomes its first scheduled follow-up.
        DB::table('leads')->whereNotNull('follow_up_date')->whereNull('deleted_at')->orderBy('id')
            ->each(function (object $lead) use ($now) {
                DB::table('lead_follow_ups')->insert([
                    'lead_id' => $lead->id, 'user_id' => $lead->assigned_to, 'due_date' => $lead->follow_up_date,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            });

        DB::table('permissions')->insertOrIgnore([
            'slug' => 'my_leads', 'name' => 'My Leads',
            'description' => 'Add leads and work only on the leads they added or that are assigned to them',
            'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now,
        ]);

        if (! DB::table('roles')->where('slug', 'agent')->exists()) {
            $role = DB::table('roles')->insertGetId([
                'name' => 'Agent', 'slug' => 'agent', 'description' => 'Adds leads (e.g. from Facebook ads) and follows up on their own leads only.',
                'is_super' => false, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('permission_role')->insert(['role_id' => $role, 'permission_id' => DB::table('permissions')->where('slug', 'my_leads')->value('id')]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'agent')->delete();
        DB::table('permissions')->where('slug', 'my_leads')->delete();

        Schema::dropIfExists('lead_follow_ups');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('extra');
        });

        Schema::dropIfExists('lead_fields');
    }
};
