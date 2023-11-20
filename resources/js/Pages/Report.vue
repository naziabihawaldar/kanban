<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
const items = [
    {
      name: 'African Elephant',
      species: 'Loxodonta africana',
      diet: 'Herbivore',
      habitat: 'Savanna, Forests',
    },
    // ... more items
  ]
const headers = [
    { "title": "Vulnerability Name", "key": "vulnerabilityName", "align": "left" },
    { "title": "Vulnerability ID", "key": "vulnerabilityID", "align": "left" },
    { "title": "Vulnerability Description", "key": "vulnerabilityDescription", "align": "left" },
    { "title": "Asset IP", "key": "assetIP", "align": "left" },
    { "title": "IP & Vuln Id", "key": "ipAndVulnId", "align": "left" },
    { "title": "Port", "key": "port", "align": "left" },
    { "title": "Protocol", "key": "protocol", "align": "left" },
    { "title": "OS Type/Version", "key": "osTypeVersion", "align": "left" },
    { "title": "OS Version", "key": "osVersion", "align": "left" },
    { "title": "Business Unit", "key": "businessUnit", "align": "left" },
    { "title": "Class", "key": "class", "align": "left" },
    { "title": "CVE ID", "key": "cveID", "align": "left" },
    { "title": "CVSS Score", "key": "cvssScore", "align": "left" },
    { "title": "Severity", "key": "severity", "align": "left" },
    { "title": "Solution", "key": "solution", "align": "left" },
    { "title": "Impact Of Vulnerability", "key": "impactOfVulnerability", "align": "left" },
    { "title": "Scan Date Time", "key": "scanDateTime", "align": "left" },
    { "title": "Background", "key": "background", "align": "left" },
    { "title": "Service", "key": "service", "align": "left" },
    { "title": "Remediation", "key": "remediation", "align": "left" },
    { "title": "References", "key": "references", "align": "left" },
    { "title": "Exception", "key": "exception", "align": "left" },
    { "title": "Tags", "key": "tags", "align": "left" },
    { "title": "Asset Version", "key": "assetVersion", "align": "left" },
    { "title": "Model", "key": "model", "align": "left" },
    { "title": "Make", "key": "make", "align": "left" },
    { "title": "Asset Type", "key": "assetType", "align": "left" },
    { "title": "Host Name", "key": "hostName", "align": "left" },
    { "title": "PLK_VLAN10 (POS-SICOM) Subnet", "key": "plkVLAN10Subnet", "align": "left" },
    { "title": "PLK_VLAN70 (Kiosk)", "key": "plkVLAN70", "align": "left" },
    { "title": "PLK_VLAN254 (Meraki-Management)", "key": "plkVLAN254", "align": "left" },
    { "title": "PLK_VLAN4 Subnet", "key": "plkVLAN4Subnet", "align": "left" },
    { "title": "BK_VLAN10 (POS-SICOM) Subnet", "key": "bkVLAN10Subnet", "align": "left" },
    { "title": "BK_VLAN70 (Kiosk)", "key": "bkVLAN70", "align": "left" },
    { "title": "BK_VLAN254 (Meraki-Management)", "key": "bkVLAN254", "align": "left" },
    { "title": "BK_VLAN4 Subnet", "key": "bkVLAN4Subnet", "align": "left" },
    { "title": "THS_VLAN10 (POS-SICOM) Subnet", "key": "thsVLAN10Subnet", "align": "left" },
    { "title": "THS_VLAN70 (Kiosk)", "key": "thsVLAN70", "align": "left" },
    { "title": "THS_VLAN254 (Meraki-Management)", "key": "thsVLAN254", "align": "left" },
    { "title": "THS_VLAN4 Subnet", "key": "thsVLAN4Subnet", "align": "left" }
];
const itemsPerPage = ref(5);

</script>
<template>
    <Head>
        <title>Reports</title>
    </Head>
    <AuthenticatedLayout>
        <v-container>
            <v-row no-gutters>
                <v-col cols="12">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Reports</h2>
                </v-col>
                <v-col cols="12">
                    <!-- <v-data-table :items="items"></v-data-table> -->
                </v-col>
            </v-row>
        </v-container>

    </AuthenticatedLayout>
</template>