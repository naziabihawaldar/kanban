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
        Schema::table('cards', function (Blueprint $table) {
            $table->string('vulnerability_id')->nullable();
            $table->string('vulnerability_title')->nullable();
            $table->string('vulnerability_desc')->nullable();
            $table->string('vulnerability_details')->nullable();
            $table->string('asset_ip')->nullable();
            $table->string('ip_and_vuln_id')->nullable();
            $table->string('port')->nullable();
            $table->string('protocol')->nullable();
            $table->string('os_type')->nullable();
            $table->string('os_version')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('class')->nullable();
            $table->string('cve_id')->nullable();
            $table->string('cvss_score')->nullable();
            $table->string('severity',800)->nullable();
            $table->string('solution')->nullable();
            $table->string('impact_of_vulnerability')->nullable();
            $table->string('scan_date_time')->nullable();
            $table->string('background')->nullable();
            $table->string('service')->nullable();
            $table->string('remediation')->nullable();
            $table->string('references')->nullable();
            $table->string('exception')->nullable();
            $table->string('tags')->nullable();
            $table->string('asset_version')->nullable();
            $table->string('model')->nullable();
            $table->string('make')->nullable();
            $table->string('asset_type')->nullable();
            $table->string('host_name')->nullable();
            

            $table->string('PLK_VLAN10_POS_SICOM_Subnet')->nullable();
            $table->string('PLK_VLAN70_Kiosk_Subnet')->nullable();
            $table->string('PLK_VLAN254_Meraki_Management_Subnet')->nullable();
            $table->string('PLK_VLAN4_Subnet')->nullable();
            $table->string('BK_VLAN10_POS_SICOM_Subnet')->nullable();
            $table->string('BK_VLAN70_Kiosk_Subnet')->nullable();
            $table->string('BK_VLAN254_Meraki_Management_Subnet')->nullable();
            $table->string('BK_VLAN4_Subnet')->nullable();
            $table->string('THS_VLAN10_POS_SICOM_Subnet')->nullable();
            $table->string('THS_VLAN70_Kiosk_Subnet')->nullable();
            $table->string('THS_VLAN254_Meraki_Management_Subnet')->nullable();
            $table->string('THS_VLAN4_Subnet')->nullable();

            
            $table->integer('board_id')->nullable()->after('column_id');
            $table->dateTime('start_date')->nullable()->after('board_id');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->dateTime('due_date')->nullable()->after('end_date');
            $table->string('created_by')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            //
        });
    }
};
