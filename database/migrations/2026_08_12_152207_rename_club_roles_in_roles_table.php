<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameRoles([
            'president' => 'President',
            'vice_president' => 'Vice President',
            'secretary' => 'General Secretary',
            'treasurer' => 'Treasurer',
            'head_projects' => 'Head of Projects',
            'head_ctf' => 'CTF Lead',
            'head_media' => 'Public Relations',
            'head_innovations' => 'Lead Developer',
            'head_discipline' => 'Technical Lead',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameRoles([
            'President' => 'president',
            'Vice President' => 'vice_president',
            'General Secretary' => 'secretary',
            'Treasurer' => 'treasurer',
            'Head of Projects' => 'head_projects',
            'CTF Lead' => 'head_ctf',
            'Public Relations' => 'head_media',
            'Lead Developer' => 'head_innovations',
            'Technical Lead' => 'head_discipline',
        ]);
    }

    /**
     * @param  array<string, string>  $roleRenames
     */
    private function renameRoles(array $roleRenames): void
    {
        foreach ($roleRenames as $from => $to) {
            $role = Role::where('name', $from)->first();

            if ($role) {
                $role->update(['name' => $to]);
            }
        }
    }
};
